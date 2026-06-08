@echo off
REM Jalankan setelah git pull / update kode di server production.
REM   cd C:\webserver\www\sipepeng
REM   scripts\post-deploy.bat

cd /d "%~dp0.."

echo [SiPepeng] composer install...
call composer install --no-dev --optimize-autoloader
if errorlevel 1 exit /b 1

echo [SiPepeng] npm ci + build...
call npm ci
if errorlevel 1 exit /b 1
call npm run build
if errorlevel 1 exit /b 1

echo [SiPepeng] migrate...
php artisan migrate --force
if errorlevel 1 exit /b 1

echo [SiPepeng] cache...
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo.
echo Selesai. Uji: %APP_URL%/login (sesuaikan APP_URL di .env)
exit /b 0
