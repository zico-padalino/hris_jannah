@echo off
setlocal EnableExtensions
chcp 65001 >nul
cd /d "%~dp0"

title HRIS Absensi RS

echo ============================================
echo   HRIS Absensi RS - Menjalankan Server
echo ============================================
echo.

where php >nul 2>&1
if errorlevel 1 (
    echo [ERROR] PHP tidak ditemukan di PATH.
    echo         Instal PHP 8.3+ dan coba lagi.
    goto :end
)

if not exist "vendor\autoload.php" (
    echo [ERROR] Dependensi belum terinstall.
    echo         Jalankan: composer install
    goto :end
)

if not exist ".env" (
    echo [ERROR] File .env tidak ditemukan.
    echo         Salin .env.example ke .env lalu: php artisan key:generate
    goto :end
)

set "MODE="
if /i "%~1"=="local" set "MODE=1"
if /i "%~1"=="tunnel" set "MODE=2"
if /i "%~1"=="cloudflare" set "MODE=2"
if "%~1"=="1" set "MODE=1"
if "%~1"=="2" set "MODE=2"

if not defined MODE (
    echo Pilih mode:
    echo   [1] Lokal        - http://localhost:8000 + fingerprint
    echo   [2] TryCloudflare - URL publik gratis + fingerprint
    echo.
    set /p "MODE=Pilihan (1/2) [1]: "
    if not defined MODE set "MODE=1"
)

if "%MODE%"=="2" goto :tunnel
if not "%MODE%"=="1" (
    echo [ERROR] Pilihan tidak valid. Gunakan 1 atau 2.
    goto :end
)

:local
echo.
echo Mode: Lokal
echo Server     : http://localhost:8000
echo Fingerprint: watcher aktif
echo.
echo Tekan Ctrl+C untuk menghentikan semua layanan.
echo.

where composer >nul 2>&1
if not errorlevel 1 (
    where npm >nul 2>&1
    if not errorlevel 1 (
        composer run serve:tcp
        goto :end
    )
)

echo [INFO] npm tidak ditemukan, menjalankan di jendela terpisah...
echo.
start "HRIS - Laravel Server" /D "%~dp0" cmd /k "php artisan serve --host=0.0.0.0 --port=8000"
timeout /t 2 /nobreak >nul
start "HRIS - Fingerprint Watch" /D "%~dp0" cmd /k "php artisan fingerprint:watch"
echo Server sudah dijalankan.
goto :end

:tunnel
where cloudflared >nul 2>&1
if errorlevel 1 (
    echo [ERROR] cloudflared tidak ditemukan di PATH.
    echo.
    echo         Download: https://developers.cloudflare.com/cloudflare-one/connections/connect-networks/downloads/
    echo         Setelah install, jalankan ulang start.bat
    goto :end
)

echo.
echo Mode: TryCloudflare
echo Fingerprint watcher akan dibuka di jendela terpisah.
echo URL publik akan muncul setelah tunnel siap.
echo Tekan Ctrl+C di jendela ini untuk menghentikan server + tunnel.
echo.

start "HRIS - Fingerprint Watch" /D "%~dp0" cmd /k "php artisan fingerprint:watch"
timeout /t 1 /nobreak >nul

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\start-cloudflare-tunnel.ps1"

:end
pause
endlocal
