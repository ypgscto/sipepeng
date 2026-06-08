@echo off
REM Cek modul Panduan / SOP sudah terpasang di server.
REM   cd C:\webserver\www\sipepeng
REM   scripts\verify-panduan.bat

cd /d "%~dp0.."

echo === Verifikasi Panduan SiPepeng ===
echo.

set OK=1

if not exist "app\Http\Controllers\ManualController.php" (
    echo [GAGAL] ManualController.php tidak ada - git pull belum dapat kode panduan
    set OK=0
)

if not exist "config\sipepeng_manual.php" (
    echo [GAGAL] config\sipepeng_manual.php tidak ada
    set OK=0
)

if not exist "resources\views\manual\index.blade.php" (
    echo [GAGAL] views\manual\ tidak ada
    set OK=0
)

php artisan route:list --name=manual.index 2>nul | findstr /C:"manual.index" >nul
if errorlevel 1 (
    echo [GAGAL] Route manual.index tidak terdaftar - jalankan: php artisan route:clear ^&^& php artisan route:cache
    set OK=0
) else (
    echo [OK] Route manual.index terdaftar
)

findstr /C:"Panduan / SOP" config\sipeng_sidebar.php >nul 2>&1
if errorlevel 1 (
    echo [GAGAL] Menu sidebar Panduan belum ada di config\sipeng_sidebar.php
    set OK=0
) else (
    echo [OK] Entri sidebar Panduan / SOP ada
)

echo.
if "%OK%"=="1" (
    echo Semua cek lulus. Buka: /panduan atau menu Panduan / SOP di sidebar.
    echo Jika menu tidak tampil: logout-login, php artisan optimize:clear, config:cache, route:cache
) else (
    echo Perbaiki item [GAGAL] di atas, lalu: php artisan optimize:clear ^&^& php artisan config:cache ^&^& php artisan route:cache
    exit /b 1
)

exit /b 0
