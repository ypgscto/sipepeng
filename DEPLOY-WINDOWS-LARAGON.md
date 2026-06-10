# Pemindahan SiPepeng ke Windows Server (Laragon)

> **Update aplikasi yang sudah jalan?** Lihat **[docs/DEPLOY-LANGKAH.md](docs/DEPLOY-LANGKAH.md)** (checklist singkat) atau **[docs/DEPLOY-UPDATE.md](docs/DEPLOY-UPDATE.md)** — detail `git pull`, migrasi, dan verifikasi.

Panduan ini untuk **instalasi baru** di server production:

- **OS:** Windows Server  
- **Laragon:** terpasang (mis. `C:\webserver`)  
- **Path aplikasi:** `C:\webserver\www\sipepeng`  
- **Development (sumber):** `C:\laragon\www\sipepeng`  
- **Repository GitHub:** `https://github.com/ypgscto/sipepeng.git` (branch `main`)

Siakad-API harus sudah bisa diakses dari server production (URL + token). Biasanya terpasang di `C:\webserver\www\siakad-api`.

---

## Ringkasan alur

```
[PC Development]  ──commit & push──►  GitHub (ypgscto/sipepeng)
                                              │
[Server Baru]  ──git clone──────────►  C:\webserver\www\sipepeng
                                              │
                                              ├─ composer install
                                              ├─ npm run build
                                              ├─ .env production
                                              ├─ migrate + seed
                                              └─ Laragon vhost → folder public/
```

---

## Tahap 0 — Siapkan GitHub (sekali, di PC development)

Jika repository belum ada di GitHub:

```bat
cd C:\laragon\www\sipepeng
git init
git add .
git commit -m "Initial commit SiPepeng"
git branch -M main
git remote add origin https://github.com/ypgscto/sipepeng.git
git push -u origin main
```

> Buat repository kosong `ypgscto/sipepeng` di GitHub terlebih dahulu (tanpa README auto-generated jika ingin push tanpa konflik).

---

## Tahap 1 — Cek server production (sekali)

Di server, pastikan Laragon sudah memuat:

| Komponen | Versi minimal |
|----------|----------------|
| PHP | 8.2+ |
| MySQL / MariaDB | 8.x |
| Composer | 2.x |
| Node.js | 20+ (untuk build CSS/JS) |
| Git | 2.x (untuk `git clone` / `git pull`) |
| Apache atau Nginx | sesuai Laragon |

Di Laragon: **Menu → PHP → Extensions** — aktifkan minimal:  
`pdo_mysql`, `openssl`, `mbstring`, `curl`, `fileinfo`, `gd`, `zip`, `bcmath`, `xml`.

Buka **Terminal** Laragon di server, cek:

```bat
php -v
composer -V
node -v
npm -v
git --version
mysql --version
```

---

## Tahap 2 — Siapkan folder di server

```bat
mkdir C:\webserver\www\sipepeng
```

Instalasi baru — tidak perlu backup folder lama.

---

## Tahap 3 — Clone dari GitHub (disarankan)

Di **server**:

```bat
cd C:\webserver\www
git clone https://github.com/ypgscto/sipepeng.git sipepeng
cd sipepeng
```

### Alternatif — ZIP dari development

**PowerShell (PC development):**

```powershell
cd C:\laragon\www\sipepeng

robocopy . C:\Temp\sipepeng-deploy /E /XD node_modules vendor .git storage\logs storage\framework\cache storage\framework\sessions storage\framework\views bootstrap\cache /XF .env *.sql

Compress-Archive -Path C:\Temp\sipepeng-deploy\* -DestinationPath C:\Temp\sipepeng-deploy.zip -Force
```

Salin ZIP ke server, ekstrak ke `C:\webserver\www\sipepeng`.

### Yang TIDAK boleh ikut ke production

| Jangan pindahkan | Alasan |
|------------------|--------|
| `.env` | Buat baru di server |
| `node_modules\` | Install ulang di server |
| `vendor\` | `composer install` di server |
| `*.sql` | Database dibuat terpisah |
| Isi `storage\logs\` | Log lokal development |

---

## Tahap 4 — Install dependensi & `.env`

Buka **Terminal Laragon** di server:

```bat
cd C:\webserver\www\sipepeng

copy .env.example .env
notepad .env
```

Isi minimal `.env` (lihat **Tahap 6**), lalu:

```bat
scripts\first-install.bat
```

Atau manual:

```bat
composer install --no-dev --optimize-autoloader
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

Pastikan folder `public\build` terisi setelah build.

---

## Tahap 5 — Database production

1. Laragon → **Start All** → buka **HeidiSQL** atau **phpMyAdmin**.  
2. Buat database:

```sql
CREATE DATABASE sipepeng CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

3. (Opsional) user khusus:

```sql
CREATE USER 'sipepeng_app'@'localhost' IDENTIFIED BY 'PASSWORD_KUAT';
GRANT ALL PRIVILEGES ON sipepeng.* TO 'sipepeng_app'@'localhost';
FLUSH PRIVILEGES;
```

4. Isi di `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sipepeng
DB_USERNAME=root
DB_PASSWORD=
```

5. Jalankan migrasi + seed (**setelah** `.env` DB benar):

```bat
cd C:\webserver\www\sipepeng
php artisan migrate --force
php artisan db:seed --force
php artisan sipepeng:sync-siakad-super-admin
```

Seeder membuat peran LPPM, master data awal, dan super admin Siakad (jika `SIPEPENG_BOOTSTRAP_SUPER_ADMIN=true`).

---

## Tahap 6 — File `.env` production

Edit `C:\webserver\www\sipepeng\.env`:

```env
APP_NAME=SiPepeng
APP_ENV=production
APP_DEBUG=false
APP_URL=https://sipepeng.stikesgunungsari.ac.id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sipepeng
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
QUEUE_CONNECTION=database
CACHE_STORE=database

SIAKAD_API_BASE_URL=https://siakad-api.stikesgunungsari.ac.id
SIAKAD_API_TOKEN=TOKEN_DARI_SIAKAD_API
SIAKAD_API_TIMEOUT=120

SIPEPENG_ALLOW_LOCAL_LOGIN_FALLBACK=false
SIPEPENG_AUTO_ALLOW_LOGIN_ON_FIRST_LOGIN=false
SIPEPENG_BOOTSTRAP_SUPER_ADMIN=true
SIPEPENG_PURGE_DEMO_ACCOUNTS=true
```

Sesuaikan:

- `APP_URL` → domain/IP production yang dipakai user (**harus persis** dengan URL browser).  
- `SIAKAD_API_BASE_URL` → URL Siakad-API yang bisa dijangkau **dari server ini**.  
- `APP_DEBUG=false` wajib di production.  
- Setelah instalasi stabil, boleh set `SIPEPENG_BOOTSTRAP_SUPER_ADMIN=false` agar `db:seed` tidak mengulang bootstrap super admin.

Setelah ubah `.env`:

```bat
php artisan config:clear
php artisan config:cache
```

Uji koneksi Siakad:

```bat
php scripts\test-siakad-connection.php
```

---

## Tahap 7 — Storage & permission Windows

```bat
cd C:\webserver\www\sipepeng
php artisan storage:link
```

Pastikan user yang menjalankan Apache/PHP bisa **menulis** ke:

- `storage\`
- `bootstrap\cache\`

Dokumen proposal disimpan di `storage\app\` (tidak public).

---

## Tahap 8 — Virtual host Laragon (penting)

Document root harus ke folder **`public`**, bukan root proyek.

### Apache (umum di Laragon)

1. Laragon → **Menu → Apache → sites-enabled**.  
2. Buat/edit file mis. `sipepeng.conf`:

```apache
<VirtualHost *:80>
    DocumentRoot "C:/webserver/www/sipepeng/public"
    ServerName sipepeng.stikesgunungsari.ac.id
    ServerAlias www.sipepeng.stikesgunungsari.ac.id

    <Directory "C:/webserver/www/sipepeng/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

3. Laragon → **Reload Apache**.

### Fallback document root salah

Jika document root sementara masih ke folder proyek (bukan `public/`), file `index.php` dan `.htaccess` di root proyek akan meneruskan ke `public/`. Tetap disarankan vhost langsung ke `public/`.

### Subfolder (jika URL seperti `http://98.142.245.18/sipepeng/`)

Panduan lengkap IP sementara: **[docs/DEPLOY-IP-TEMPORARY.md](docs/DEPLOY-IP-TEMPORARY.md)**

1. Di `.env`:
   ```env
   APP_URL=http://98.142.245.18/sipepeng
   SESSION_SECURE_COOKIE=false
   ```
   *(Ganti IP sesuai server. `SESSION_SECURE_COOKIE=false` untuk HTTP sementara.)*
2. Edit `public\.htaccess`, aktifkan baris:
   ```apache
   RewriteBase /sipepeng/public
   ```
3. Setelah ubah `.env`:
   ```bat
   php artisan config:clear
   php artisan config:cache
   ```

Middleware `ConfigureSubfolderSession` menyesuaikan URL & cookie session otomatis dari path request.

---

## Tahap 9 — Setelah aplikasi hidup (Siakad & pengguna)

1. Login super admin IT via **SIAKAD-GS** (mis. `bashar.ypgs@gmail.com`).  
2. **Pengaturan → SIAKAD-API** — pastikan URL & token benar (override `.env` jika perlu).  
3. **Data Referensi Siakad** — refresh prodi, dosen, mahasiswa.  
4. Aktifkan user LPPM yang boleh login (`is_allowed_login`) lewat menu pengaturan/admin yang tersedia.  
5. Uji login dengan akun dosen/ketua prodi yang sudah diaktifkan.

---

## Tahap 10 — Verifikasi

| Cek | Cara |
|-----|------|
| Health | Buka `APP_URL/up` |
| Halaman login | Buka `APP_URL/login` |
| Tidak error 500 | Lihat `storage\logs\laravel.log` |
| CSS/JS | Tab Network: file dari `/build/` |
| Maskot bounce | Pojok kiri bawah dashboard |
| Siakad | `php scripts\test-siakad-connection.php` |
| Upload proposal | Unggah PDF kecil di modul penelitian |

---

## Update versi berikutnya

**Development:** commit & push ke GitHub.

**Server:**

```bat
cd C:\webserver\www\sipepeng
git pull origin main
scripts\post-deploy.bat
```

Detail lengkap: [docs/DEPLOY-UPDATE.md](docs/DEPLOY-UPDATE.md).

---

## Troubleshooting Windows + Laragon

| Masalah | Solusi |
|---------|--------|
| 500 Internal Server Error | `storage\logs\laravel.log`; `php artisan config:clear`; cek `APP_KEY` |
| Halaman kosong / no CSS | `npm run build`; cek `public\build` |
| `vendor` tidak ada | `composer install` di server |
| Login Siakad gagal | URL/token API; user belum `is_allowed_login` |
| Error 419 setelah submit | Cek `APP_URL` persis dengan browser; `SESSION_SECURE_COOKIE` di HTTPS |
| Upload gagal | Permission tulis folder `storage` |
| `public` tidak jalan | DocumentRoot harus `...\sipepeng\public` |
| MySQL connection refused | Laragon MySQL Start; cek `DB_*` di `.env` |
| `APP_KEY` berubah setelah deploy | Jangan timpa `.env`; backup sebelum update |

---

## Checklist singkat (instalasi baru)

- [ ] Repository `ypgscto/sipepeng` sudah di-push dari development  
- [ ] `git clone` ke `C:\webserver\www\sipepeng`  
- [ ] `scripts\first-install.bat` atau langkah manual selesai  
- [ ] `.env` production + database `sipepeng`  
- [ ] `php artisan storage:link`  
- [ ] Vhost → `public`  
- [ ] `APP_DEBUG=false`  
- [ ] `php scripts\test-siakad-connection.php` OK  
- [ ] Login super admin + aktivasi user LPPM  
- [ ] Backup database terjadwal  

---

Lihat juga: [DEPLOY.md](DEPLOY.md) (varian Linux), [docs/installation.md](docs/installation.md), [docs/configuration-env.md](docs/configuration-env.md).
