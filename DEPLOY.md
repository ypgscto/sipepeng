# Deploy SiPepeng ke Production

> **Windows Server + Laragon** (`C:\webserver\www\sipepeng`): gunakan panduan khusus  
> **[DEPLOY-WINDOWS-LARAGON.md](DEPLOY-WINDOWS-LARAGON.md)**.

Panduan di bawah ini untuk server **Linux** (Ubuntu/Debian) dengan **Nginx atau Apache**, **PHP 8.2+**, **MySQL 8+**, dan **Node.js 20+** (hanya saat build aset). Siakad-API harus sudah berjalan dan dapat diakses dari server production.

**Repository:** `https://github.com/ypgscto/sipepeng.git` (branch `main`)

---

## Ringkasan tahap

| # | Tahap | Ringkasan |
|---|--------|-----------|
| 1 | Persiapan server | PHP, Composer, MySQL, web server, SSL, Git |
| 2 | Database production | Buat DB + user MySQL |
| 3 | Deploy kode | `git clone` + `composer` + `npm build` |
| 4 | Environment | File `.env` production |
| 5 | Migrasi & seed | Schema + peran LPPM + master data |
| 6 | Storage & permission | `storage:link`, ownership |
| 7 | Optimasi Laravel | Config/route/view cache |
| 8 | Web server | Document root → `public/` |
| 9 | Siakad & pengguna | Konfigurasi API + aktivasi login |
| 10 | Verifikasi | Smoke test + backup |

---

## Tahap 1 — Persiapan server

### Paket yang diperlukan

- PHP 8.2+ dengan ekstensi: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `gd`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `zip`
- Composer 2.x
- MySQL 8+ (atau MariaDB setara)
- Nginx **atau** Apache + `mod_rewrite`
- Node.js 20+ & npm (untuk `npm run build`)
- Git

### Contoh (Ubuntu)

```bash
sudo apt update
sudo apt install -y nginx mysql-server php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring \
  php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath composer git nodejs npm
```

---

## Tahap 2 — Database production

```bash
sudo mysql -u root -p
```

```sql
CREATE DATABASE sipepeng CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'sipepeng_app'@'localhost' IDENTIFIED BY 'GANTI_PASSWORD_KUAT';
GRANT ALL PRIVILEGES ON sipepeng.* TO 'sipepeng_app'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## Tahap 3 — Deploy kode aplikasi

### Git clone (disarankan)

```bash
sudo mkdir -p /var/www/sipepeng
sudo chown $USER:www-data /var/www/sipepeng
cd /var/www/sipepeng
git clone https://github.com/ypgscto/sipepeng.git .
git checkout main
```

### Install dependensi PHP & build frontend

```bash
cd /var/www/sipepeng
composer install --no-dev --optimize-autoloader
cp .env.example .env
# Edit .env (Tahap 4) sebelum perintah di bawah yang butuh APP_KEY

php artisan key:generate

npm ci
npm run build
```

---

## Tahap 4 — File `.env` production

Salin dari `.env.example` lalu sesuaikan minimal:

```env
APP_NAME=SiPepeng
APP_ENV=production
APP_DEBUG=false
APP_URL=https://sipepeng.stikesgunungsari.ac.id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sipepeng
DB_USERNAME=sipepeng_app
DB_PASSWORD=GANTI_PASSWORD_KUAT

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
QUEUE_CONNECTION=database
CACHE_STORE=database
FILESYSTEM_DISK=local

SIAKAD_API_BASE_URL=https://siakad-api.stikesgunungsari.ac.id
SIAKAD_API_TOKEN=TOKEN_DARI_SIAKAD_API
SIAKAD_API_TIMEOUT=120

SIPEPENG_ALLOW_LOCAL_LOGIN_FALLBACK=false
SIPEPENG_AUTO_ALLOW_LOGIN_ON_FIRST_LOGIN=false
SIPEPENG_BOOTSTRAP_SUPER_ADMIN=true
SIPEPENG_PURGE_DEMO_ACCOUNTS=true
```

**Penting**

- `APP_DEBUG=false` di production.
- `APP_KEY` generate sekali; jangan rotate tanpa re-enter token Siakad di Pengaturan.
- Jangan commit file `.env` ke Git.

---

## Tahap 5 — Migrasi database & data awal

```bash
cd /var/www/sipepeng
php artisan migrate --force
php artisan db:seed --force
php artisan sipepeng:sync-siakad-super-admin
```

Login awal super admin IT: akun **SIAKAD-GS** (mis. `bashar.ypgs@gmail.com`).

---

## Tahap 6 — Storage & permission

```bash
php artisan storage:link
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache
```

---

## Tahap 7 — Optimasi Laravel (production)

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Setelah ubah `.env` atau config:

```bash
php artisan config:clear
php artisan config:cache
```

Uji Siakad:

```bash
php scripts/test-siakad-connection.php
```

---

## Tahap 8 — Konfigurasi web server

**Document root harus** `.../sipepeng/public`, bukan folder root proyek.

### Nginx (cuplikan)

```nginx
server {
    listen 443 ssl http2;
    server_name sipepeng.stikesgunungsari.ac.id;
    root /var/www/sipepeng/public;

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### HTTPS

Gunakan Let's Encrypt (`certbot`) sebelum go-live.

---

## Tahap 9 — Integrasi Siakad & pengguna

1. **Pengaturan → SIAKAD-API** — URL & token production.
2. **Data Referensi Siakad** — refresh prodi, dosen, mahasiswa.
3. Aktifkan user yang boleh login SiPepeng (`is_allowed_login`).
4. Uji login dengan akun dosen/ketua prodi yang sudah diaktifkan.

Queue worker (opsional):

```bash
php artisan queue:work --daemon
```

Scheduler (cron):

```cron
* * * * * cd /var/www/sipepeng && php artisan schedule:run >> /dev/null 2>&1
```

---

## Tahap 10 — Verifikasi & maintenance

### Smoke test

- [ ] `GET /up` → 200
- [ ] Halaman login (HTTPS)
- [ ] Login super admin via SIAKAD-GS
- [ ] Dashboard + sidebar modul LPPM
- [ ] Refresh referensi Siakad
- [ ] Upload PDF proposal kecil

### Backup database

```bash
mysqldump -u sipepeng_app -p sipepeng > backup_sipepeng_$(date +%F).sql
```

### Update rilis berikutnya

```bash
cd /var/www/sipepeng
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Detail: [docs/DEPLOY-UPDATE.md](docs/DEPLOY-UPDATE.md).

---

## Checklist keamanan production

- [ ] `APP_DEBUG=false`
- [ ] `.env` tidak terbaca publik (di luar `public/`)
- [ ] Token `SIAKAD_API_TOKEN` rahasia
- [ ] Password DB kuat
- [ ] Hanya user dengan `is_allowed_login=1` yang bisa masuk
- [ ] MySQL tidak expose ke internet

---

## Repositori Git (development)

Push dari PC development:

```bash
cd C:/laragon/www/sipepeng
git init   # jika belum
git remote add origin https://github.com/ypgscto/sipepeng.git
git add .
git commit -m "Initial commit SiPepeng"
git push -u origin main
```

Deploy production mengikuti **Tahap 3** dengan URL repositori yang sama.

---

Lihat juga: [DEPLOY-WINDOWS-LARAGON.md](DEPLOY-WINDOWS-LARAGON.md), [docs/configuration-env.md](docs/configuration-env.md).
