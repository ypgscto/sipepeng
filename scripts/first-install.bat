@echo off
REM Instalasi pertama SiPepeng di server (setelah clone + .env).
REM   cd C:\webserver\www\sipepeng
REM   copy .env.example .env
REM   notepad .env
REM   scripts\first-install.bat

cd /d "%~dp0.."

if not exist .env (
    echo ERROR: Buat .env dulu dari .env.example
    echo   Produksi: copy .env.production.example .env
    exit /b 1
)

findstr /C:"DB_CONNECTION=sqlite" .env >nul 2>&1
if not errorlevel 1 (
    echo.
    echo ERROR: .env masih DB_CONNECTION=sqlite ^(untuk development saja^).
    echo   Server production wajib MySQL:
    echo     copy .env.production.example .env
    echo     notepad .env
    echo   Atau ubah manual: DB_CONNECTION=mysql + DB_HOST/DB_DATABASE/...
    echo.
    exit /b 1
)

echo [SiPepeng] Cek database...
php artisan db:show >nul 2>&1
if errorlevel 1 (
    echo.
    echo ERROR: Database belum ada atau koneksi DB gagal.
    echo   1. Buat database: mysql -u root -e "CREATE DATABASE sipepeng CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    echo   2. Atau jalankan: scripts\create-database.sql di HeidiSQL
    echo   3. Cek DB_HOST, DB_PORT, DB_DATABASE di .env
    echo.
    exit /b 1
)

echo [SiPepeng] composer install...
call composer install --no-dev --optimize-autoloader
if errorlevel 1 exit /b 1

if not exist .env (
    echo ERROR: .env hilang
    exit /b 1
)

findstr /C:"APP_KEY=" .env | findstr /V "APP_KEY=$" >nul
if errorlevel 1 (
    echo [SiPepeng] generate APP_KEY...
    php artisan key:generate --force
)

echo [SiPepeng] npm ci + build...
call npm ci
if errorlevel 1 exit /b 1
call npm run build
if errorlevel 1 exit /b 1

echo [SiPepeng] migrate + seed...
php artisan config:clear
php artisan migrate --force
if errorlevel 1 (
    echo.
    echo ERROR migrate gagal. Cek:
    echo   - DB_CONNECTION=mysql di .env ^(bukan sqlite^)
    echo   - Database sipepeng sudah dibuat di MySQL
    echo   - Ekstensi PHP pdo_mysql aktif di Laragon
    echo   - php artisan config:clear lalu coba lagi
    echo.
    exit /b 1
)
php artisan db:seed --force
if errorlevel 1 exit /b 1

echo [SiPepeng] super admin Siakad...
php artisan sipepeng:sync-siakad-super-admin
if errorlevel 1 exit /b 1

echo [SiPepeng] storage link...
php artisan storage:link

echo [SiPepeng] cache production...
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo.
echo Instalasi selesai. Langkah berikutnya:
echo   1. Virtual host ke folder public\
echo   2. php scripts\test-siakad-connection.php
echo   3. Login super admin via SIAKAD-GS (bashar.ypgs@gmail.com)
echo   4. Pengaturan - aktifkan user LPPM yang boleh login
exit /b 0
