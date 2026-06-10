-- Buat database SiPepeng (jalankan sekali di server sebelum php artisan migrate)
-- HeidiSQL / phpMyAdmin: buka tab SQL, paste, Execute
-- Atau CLI: mysql -u root -p < scripts\create-database.sql

CREATE DATABASE IF NOT EXISTS sipepeng
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

-- Opsional: user khusus (ganti PASSWORD_KUAT)
-- CREATE USER IF NOT EXISTS 'sipepeng_app'@'localhost' IDENTIFIED BY 'PASSWORD_KUAT';
-- GRANT ALL PRIVILEGES ON sipepeng.* TO 'sipepeng_app'@'localhost';
-- FLUSH PRIVILEGES;
