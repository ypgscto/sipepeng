# Deploy sementara via IP (tanpa domain)

Panduan cepat jika aplikasi diakses lewat **IP + subfolder**, misalnya:

```
http://98.142.245.18/sipepeng/
```

Path server: `C:\webserver\www\sipepeng`

---

## Error: `vendor/autoload.php` tidak ditemukan

Kode sudah di-clone/copy, tetapi **dependensi belum di-install**. Jalankan di **Terminal Laragon server**:

```bat
cd C:\webserver\www\sipepeng

composer install --no-dev --optimize-autoloader
```

Lalu lanjut langkah `.env` di bawah.

---

## Instalasi lengkap (server baru, IP sementara)

```bat
cd C:\webserver\www\sipepeng

copy .env.production.example .env
notepad .env
```

> **Jangan** pakai `copy .env.example .env` di server — default-nya **SQLite** (development).  
> Production wajib **MySQL** (`DB_CONNECTION=mysql`).

Isi `.env` minimal:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=http://98.142.245.18/sipepeng

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sipepeng
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=false

SIAKAD_API_BASE_URL=http://127.0.0.1:8000
SIAKAD_API_TOKEN=TOKEN_DARI_SIAKAD_API

SIPEPENG_ALLOW_LOCAL_LOGIN_FALLBACK=false
SIPEPENG_BOOTSTRAP_SUPER_ADMIN=true
```

> `APP_URL` **harus persis** dengan URL di browser (tanpa slash di akhir).  
> `SESSION_SECURE_COOKIE=false` karena sementara pakai **HTTP** (bukan HTTPS).  
> Ganti IP jika berbeda.

---

## Buat database MySQL (wajib sebelum migrate)

Laravel **tidak** membuat database otomatis — buat manual dulu.

### Opsi A — HeidiSQL / phpMyAdmin (Laragon)

1. Laragon → **Start All**
2. Buka **HeidiSQL** (atau phpMyAdmin)
3. Tab **Query**, jalankan:

```sql
CREATE DATABASE sipepeng CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

4. Pastikan database `sipepeng` muncul di panel kiri.

### Opsi B — Terminal Laragon

```bat
cd C:\webserver\www\sipepeng
mysql -u root -e "CREATE DATABASE sipepeng CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Atau pakai file SQL di repo:

```bat
mysql -u root < scripts\create-database.sql
```

*(Jika MySQL pakai password root: tambahkan `-p` lalu masukkan password.)*

### Cek koneksi `.env`

| Variabel | Nilai umum Laragon |
|----------|-------------------|
| `DB_HOST` | `127.0.0.1` |
| `DB_PORT` | `3306` (kadang `3307` — cek Laragon → MySQL) |
| `DB_DATABASE` | `sipepeng` |
| `DB_USERNAME` | `root` |
| `DB_PASSWORD` | kosong atau password root Laragon |

Uji koneksi:

```bat
php artisan db:show
```

Jika error "Unknown database" → database belum dibuat.  
Jika error "Access denied" → cek `DB_USERNAME` / `DB_PASSWORD`.  
Jika error "Connection refused" → MySQL belum Start atau `DB_PORT` salah.

---

## Install aplikasi (setelah database ada)

Lalu:

```bat
php artisan key:generate
npm ci
npm run build
php artisan migrate --force
php artisan db:seed --force
php artisan sipepeng:sync-siakad-super-admin
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Atau satu perintah:

```bat
scripts\first-install.bat
```

---

## Apache subfolder

Jika URL browser **tanpa** `/public` (mis. `/sipepeng/login`):

1. File `index.php` dan `.htaccess` di **root proyek** sudah meneruskan ke `public/`.
2. Buka `public\.htaccess`, **aktifkan** baris RewriteBase (hapus `#`):

```apache
RewriteBase /sipepeng/public
```

3. Laragon → **Reload Apache**.

Jika route/CSS masih 404, coba `APP_URL` dengan `/public`:

```env
APP_URL=http://98.142.245.18/sipepeng/public
```

---

## Troubleshooting

### `could not find driver` + `Connection: sqlite`

`.env` masih `DB_CONNECTION=sqlite`. Ubah ke MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sipepeng
DB_USERNAME=root
DB_PASSWORD=
```

Lalu:

```bat
php artisan config:clear
php artisan migrate --force
php artisan db:seed --force
php artisan sipepeng:sync-siakad-super-admin
php artisan config:cache
```

### `could not find driver` + `Connection: mysql`

Ekstensi PHP `pdo_mysql` belum aktif. Laragon → **Menu → PHP → Extensions** → centang **pdo_mysql** → restart Apache.

---

## Verifikasi

| Cek | URL |
|-----|-----|
| Health | `http://98.142.245.18/sipepeng/up` |
| Login | `http://98.142.245.18/sipepeng/login` |
| Siakad | `php scripts\test-siakad-connection.php` |

---

## Nanti pakai domain

Saat domain sudah siap (mis. `https://sipepeng.stikesgunungsari.ac.id`):

1. Buat virtual host → document root `C:\webserver\www\sipepeng\public`
2. Ubah `.env`:
   ```env
   APP_URL=https://sipepeng.stikesgunungsari.ac.id
   SESSION_SECURE_COOKIE=true
   ```
3. Comment/hapus `RewriteBase` di `public\.htaccess`
4. `php artisan config:clear` → `php artisan config:cache`
5. Reload Apache

---

Lihat juga: [DEPLOY-WINDOWS-LARAGON.md](../DEPLOY-WINDOWS-LARAGON.md)
