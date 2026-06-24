# Jalankan Laravel + Cloudflare Tunnel (demo publik gratis)
# Usage: powershell -ExecutionPolicy Bypass -File scripts/start-cloudflare-tunnel.ps1
#        powershell -ExecutionPolicy Bypass -File scripts/start-cloudflare-tunnel.ps1 -Port 8000 -Restart

param(
    [int]$Port = 8000,
    [string]$ListenAddress = "127.0.0.1",
    [switch]$Restart
)

$ErrorActionPreference = "Stop"
$root = Split-Path -Parent $PSScriptRoot
Set-Location $root

$script:serveProcess = $null
$script:LastTunnelUrl = $null

function Test-CliCommand {
    param([string]$Name)
    return $null -ne (Get-Command $Name -ErrorAction SilentlyContinue)
}

function Stop-CloudflaredProcesses {
    Get-Process cloudflared -ErrorAction SilentlyContinue |
        ForEach-Object {
            Stop-Process -Id $_.Id -Force -ErrorAction SilentlyContinue
        }
}

function Stop-PortListeners {
    param([int]$Port)
    Get-NetTCPConnection -LocalPort $Port -State Listen -ErrorAction SilentlyContinue |
        ForEach-Object {
            Stop-Process -Id $_.OwningProcess -Force -ErrorAction SilentlyContinue
        }
}

function Test-LaravelServer {
    param([string]$Url)
    try {
        $response = Invoke-WebRequest -Uri $Url -UseBasicParsing -TimeoutSec 5
        return $response.StatusCode -ge 200 -and $response.StatusCode -lt 500
    } catch {
        return $false
    }
}

function Stop-ServeProcess {
    if ($null -eq $script:serveProcess) {
        return
    }
    if (-not $script:serveProcess.HasExited) {
        $script:serveProcess.Kill()
        $script:serveProcess.WaitForExit(5000)
    }
    $script:serveProcess = $null
}

function Show-TunnelUrlBanner {
    param(
        [string]$Url,
        [switch]$Ready
    )

    Write-Host ""
    Write-Host "================================================================" -ForegroundColor Cyan
    if ($Ready) {
        Write-Host "  TUNNEL SIAP - BUKA DI BROWSER SEKARANG:" -ForegroundColor Green
    } else {
        Write-Host "  URL DIBUAT - TUNGGU KONEKSI..." -ForegroundColor DarkYellow
    }
    Write-Host ""
    Write-Host "  $Url" -ForegroundColor Yellow
    Write-Host ""
    if ($Ready) {
        Write-Host "  Login demo: admin@rs.local / password" -ForegroundColor DarkGray
    } else {
        Write-Host "  Jangan buka dulu sampai muncul 'TUNNEL SIAP'" -ForegroundColor DarkGray
    }
    Write-Host "================================================================" -ForegroundColor Cyan
    Write-Host ""
}

function Write-CloudflaredLine {
    param([string]$Line)

    if ([string]::IsNullOrWhiteSpace($Line)) {
        return
    }

    $urlPattern = 'https://[a-z0-9-]+\.trycloudflare\.com'

    if ($Line -match $urlPattern) {
        if ($script:LastTunnelUrl -ne $Matches[0]) {
            $script:LastTunnelUrl = $Matches[0]
            Show-TunnelUrlBanner $Matches[0]
        }
    }

    if ($Line -match 'Registered tunnel connection' -and $null -ne $script:LastTunnelUrl) {
        Show-TunnelUrlBanner $script:LastTunnelUrl -Ready
        return
    }

    if ($Line -notmatch 'precheck component=') {
        Write-Host $Line
    }
}

function Start-CloudflaredTunnel {
    param([string]$TargetUrl)

    $previousPreference = $ErrorActionPreference
    $ErrorActionPreference = 'Continue'

    try {
        cloudflared tunnel --url $TargetUrl 2>&1 | ForEach-Object {
            Write-CloudflaredLine $_.ToString()
        }
    } finally {
        $ErrorActionPreference = $previousPreference
    }

    if ($null -ne $LASTEXITCODE) {
        return $LASTEXITCODE
    }

    return 0
}

if (-not (Test-CliCommand "php")) {
    throw "PHP tidak ditemukan. Pastikan PHP ada di PATH."
}

if (-not (Test-CliCommand "cloudflared")) {
    throw @"
cloudflared belum terinstall.
Download: https://developers.cloudflare.com/cloudflare-one/connections/connect-networks/downloads/
"@
}

$localUrl = "http://${ListenAddress}:${Port}"

Write-Host "==> Menghentikan tunnel cloudflared lama (jika ada)"
Stop-CloudflaredProcesses
Start-Sleep -Seconds 1

Write-Host "==> Memulai ulang Laravel di port $Port"
Stop-PortListeners -Port $Port
Start-Sleep -Seconds 1

Write-Host "==> Menjalankan Laravel: $localUrl"
$psi = New-Object System.Diagnostics.ProcessStartInfo
$psi.FileName = "php"
$psi.Arguments = "artisan serve --host=$ListenAddress --port=$Port"
$psi.WorkingDirectory = $root
$psi.UseShellExecute = $false
$psi.CreateNoWindow = $true
$script:serveProcess = [System.Diagnostics.Process]::Start($psi)

$deadline = (Get-Date).AddSeconds(15)
$serverReady = $false
do {
    Start-Sleep -Milliseconds 500
    $serverReady = Test-LaravelServer -Url $localUrl
} while (-not $serverReady -and (Get-Date) -lt $deadline)

if (-not $serverReady) {
    Stop-ServeProcess
    throw "Laravel tidak merespons di $localUrl. Cek database/MySQL lalu coba lagi."
}

Write-Host "    Laravel siap."

Write-Host ""
Write-Host "==> Menjalankan Cloudflare Tunnel..."
Write-Host "    Buka URL hanya setelah muncul 'TUNNEL SIAP'."
Write-Host "    Tekan Ctrl+C untuk menghentikan tunnel dan server Laravel."
Write-Host ""

try {
    $exitCode = Start-CloudflaredTunnel -TargetUrl $localUrl
    exit $exitCode
} finally {
    Stop-ServeProcess
}
