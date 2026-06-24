# Siapkan paket upload untuk InfinityFree
# Jalankan dari root proyek: powershell -ExecutionPolicy Bypass -File scripts/prepare-infinityfree.ps1

$ErrorActionPreference = "Stop"
$root = Split-Path -Parent $PSScriptRoot
$outDir = Join-Path $root "deploy\infinityfree-package"
$htdocs = Join-Path $outDir "htdocs"
$laravel = Join-Path $htdocs "laravel"

Write-Host "==> Build production assets"
Set-Location $root
composer install --no-dev --optimize-autoloader --no-interaction
npm ci 2>$null
if ($LASTEXITCODE -ne 0) { npm install }
npm run build

Write-Host "==> Bersihkan folder paket lama"
if (Test-Path $outDir) {
    Remove-Item $outDir -Recurse -Force
}

New-Item -ItemType Directory -Path $laravel -Force | Out-Null

Write-Host "==> Salin source Laravel"
$exclude = @(
    "node_modules", ".git", "tests", "mobile", "deploy\infinityfree-package",
    "storage\logs\*", "storage\framework\cache\data\*",
    "storage\framework\sessions\*", "storage\framework\views\*"
)

robocopy $root $laravel /E /XD node_modules .git tests mobile deploy\infinityfree-package storage\logs /XF .env .env.backup /NFL /NDL /NJH /NJS /nc /ns /np | Out-Null

Write-Host "==> Siapkan storage writable"
$storageDirs = @(
    "app\public\branding",
    "framework\cache\data",
    "framework\sessions",
    "framework\views",
    "logs"
)
foreach ($dir in $storageDirs) {
    $path = Join-Path $laravel "storage\$dir"
    New-Item -ItemType Directory -Path $path -Force | Out-Null
}
Copy-Item (Join-Path $laravel "storage\app\.gitignore") (Join-Path $laravel "storage\app\public\.gitignore") -ErrorAction SilentlyContinue

Write-Host "==> Salin file public ke htdocs"
Copy-Item (Join-Path $root "public\*") $htdocs -Recurse -Force
Copy-Item (Join-Path $root "deploy\infinityfree\htdocs-index.php") (Join-Path $htdocs "index.php") -Force
Copy-Item (Join-Path $root "deploy\infinityfree\setup-once.php") (Join-Path $htdocs "setup-once.php") -Force

Write-Host "==> Buat .env dari template"
Copy-Item (Join-Path $root "deploy\infinityfree\env.template") (Join-Path $laravel ".env") -Force

$zipPath = Join-Path $root "deploy\infinityfree-upload.zip"
if (Test-Path $zipPath) { Remove-Item $zipPath -Force }

$coreZip = Join-Path $root "deploy\infinityfree-part1-core.zip"
$vendorZip = Join-Path $root "deploy\infinityfree-part2-vendor.zip"
if (Test-Path $coreZip) { Remove-Item $coreZip -Force }
if (Test-Path $vendorZip) { Remove-Item $vendorZip -Force }

# ZIP opsional (kecil, terpisah). Untuk file besar gunakan FTP — lihat DEPLOY_INFINITYFREE.md
$vendorPath = Join-Path $laravel "vendor"
$coreStaging = Join-Path $outDir "_core_staging"
$coreHtdocs = Join-Path $coreStaging "htdocs"
$coreLaravel = Join-Path $coreHtdocs "laravel"
New-Item -ItemType Directory -Path $coreLaravel -Force | Out-Null
robocopy $htdocs $coreHtdocs /E /XD vendor /NFL /NDL /NJH /NJS /nc /ns /np | Out-Null
robocopy $laravel $coreLaravel /E /XD vendor /NFL /NDL /NJH /NJS /nc /ns /np | Out-Null
Compress-Archive -Path (Join-Path $coreStaging "*") -DestinationPath $coreZip -Force
if (Test-Path $vendorPath) {
    Compress-Archive -Path $vendorPath -DestinationPath $vendorZip -Force
}
Remove-Item $coreStaging -Recurse -Force -ErrorAction SilentlyContinue

Write-Host ""
Write-Host "Selesai!"
Write-Host "Folder upload (DISARANKAN via FTP): $htdocs"
Write-Host "ZIP bagian 1 (tanpa vendor) : $coreZip"
Write-Host "ZIP bagian 2 (vendor saja)   : $vendorZip"
Write-Host ""
Write-Host "Upload TANPA ZIP: gunakan FileZilla, drag folder htdocs ke server."
Write-Host "Panduan lengkap: DEPLOY_INFINITYFREE.md"
