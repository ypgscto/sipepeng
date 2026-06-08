# Instalasi SiPepeng

> **Deploy production (server baru):** gunakan **[DEPLOY-WINDOWS-LARAGON.md](../DEPLOY-WINDOWS-LARAGON.md)** (Windows + Laragon) atau **[DEPLOY.md](../DEPLOY.md)** (Linux).  
> **Update versi:** **[docs/DEPLOY-UPDATE.md](DEPLOY-UPDATE.md)**.

## Prasyarat

- PHP 8.2+ (ekstensi: mbstring, openssl, pdo, tokenizer, xml, ctype, json, fileinfo)
- Composer 2.x
- Node.js 20+ dan npm
- MySQL 8 / MariaDB 10.6+ (produksi) atau SQLite (pengembangan)
- Web server: Laragon, Apache, atau Nginx

## Langkah instalasi

### 1. Clone / salin proyek

```bash
cd C:\laragon\www
git clone <repo-url> sipepeng
cd sipepeng
```

### 2. Dependensi PHP

```bash
composer install
```

### 3. Environment

```bash
copy .env.example .env
php artisan key:generate
```

Edit `.env` — lihat [configuration-env.md](configuration-env.md).

### 4. Database

**MySQL (produksi):**

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sipepeng
DB_USERNAME=...
DB_PASSWORD=...
```

**SQLite (dev):**

```env
DB_CONNECTION=sqlite
# DB_DATABASE=  # kosongkan; Laravel memakai database/database.sqlite
```

Jalankan migrasi dan seeder:

```bash
php artisan migrate --seed
```

Seeder membuat peran LPPM, mapping awal, dan akun super admin (jika dikonfigurasi di seeder).

### 5. Frontend assets

```bash
npm install
npm run build
```

Development dengan hot reload:

```bash
npm run dev
```

### 6. Storage & permission

```bash
php artisan storage:link
```

Pastikan folder ini writable:

- `storage/`
- `bootstrap/cache/`
- `public/images/` (upload logo)

Dokumen proposal disimpan di `storage/app/` (disk `local`, **bukan** public).

### 7. Virtual host

Contoh Laragon: `http://sipepeng.test`

Set `APP_URL` sesuai URL aplikasi.

### 8. Verifikasi

```bash
php artisan test
curl http://sipepeng.test/up
```

Buka `/login` di browser.

## Produksi

- `APP_ENV=production`
- `APP_DEBUG=false`
- HTTPS + `SESSION_SECURE_COOKIE=true`
- Queue worker jika menggunakan fitur antrian: `php artisan queue:work`
- Scheduler: tambahkan cron `* * * * * php artisan schedule:run`

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| 500 setelah deploy | `php artisan config:clear`, cek permission `storage/` |
| CSS/JS kosong | `npm run build`, cek `public/build/` |
| Login gagal semua user | Cek SIAKAD-API URL & token |
| Referensi prodi kosong | Pengaturan → SIAKAD-API → refresh referensi |
