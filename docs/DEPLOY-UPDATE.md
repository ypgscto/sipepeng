# Panduan Deploy Update SiPepeng ke Server

Dokumen ini untuk **update aplikasi yang sudah jalan** di server production.  
**Instalasi pertama** di server baru: lihat [DEPLOY-WINDOWS-LARAGON.md](../DEPLOY-WINDOWS-LARAGON.md).

---

## Path yang dipakai

| Lingkungan | Path contoh |
|------------|-------------|
| Development (Laragon lokal) | `C:\laragon\www\sipepeng` |
| Production (Windows Server) | `C:\webserver\www\sipepeng` |
| Document root web | `...\sipepeng\public` |
| Repository GitHub | `https://github.com/ypgscto/sipepeng.git` |

Sesuaikan path jika instalasi Anda berbeda.

---

## Alur update (ringkas)

```
[Development]  git add → commit → push origin main
                                    │
[Server]       git pull origin main → scripts\post-deploy.bat
```

---

## Metode deploy (pilih satu)

### A — Git pull (disarankan)

**Lokal:** commit & push semua perubahan ke GitHub.

**Server:**

```bat
cd C:\webserver\www\sipepeng
git pull origin main
scripts\post-deploy.bat
```

*(Ganti `main` dengan nama branch production Anda.)*

**Linux:**

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

### B — ZIP / Robocopy (tanpa Git di server)

**Lokal (PowerShell):**

```powershell
cd C:\laragon\www\sipepeng

robocopy . C:\Temp\sipepeng-update /E /XD node_modules vendor .git storage\logs storage\framework\cache storage\framework\sessions storage\framework\views bootstrap\cache /XF .env *.sql

Compress-Archive -Path C:\Temp\sipepeng-update\* -DestinationPath C:\Temp\sipepeng-update.zip -Force
```

Salin ZIP ke server, ekstrak **menimpa** ke `C:\webserver\www\sipepeng` **kecuali** file yang tidak boleh ditimpa (lihat bawah), lalu jalankan `scripts\post-deploy.bat`.

### C — Robocopy langsung ke server

```bat
robocopy C:\laragon\www\sipepeng \\SERVER\C$\webserver\www\sipepeng /E /XD node_modules vendor .git /XF .env *.sql
```

Lalu di server: `scripts\post-deploy.bat`.

---

## Yang TIDAK boleh ditimpah di server

| File / folder | Alasan |
|---------------|--------|
| `.env` | Konfigurasi production (DB, URL, token Siakad, `APP_KEY`) |
| `storage\app\` (isi upload) | Proposal, surat, lampiran yang sudah diunggah user |
| `storage\logs\` | Log production |
| `vendor\` | Install ulang via Composer di server |
| `node_modules\` | Install ulang via npm di server |
| Backup database (`*.sql`) | Pindah terpisah |

**Backup `.env` sebelum update:**

```bat
copy C:\webserver\www\sipepeng\.env C:\Temp\sipepeng.env.backup
```

---

## Perintah post-deploy (isi `scripts\post-deploy.bat`)

```bat
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Kapan migrasi wajib

Jalankan `php artisan migrate --force` setiap update jika ada file baru di `database/migrations/`.

Cek status:

```bat
php artisan migrate:status
```

---

## Kapan `npm run build` wajib

Wajib jika ada perubahan di:

- `resources/css/`
- `resources/js/`
- `resources/views/` (aset Vite)
- `vite.config.js`, `package.json`

Contoh fitur yang membutuhkan build: maskot animasi, popup motivasi, styling dashboard.

---

## Perubahan `.env` antar versi

Setelah pull, bandingkan `.env` server dengan `.env.example` di repo:

```bat
fc C:\webserver\www\sipepeng\.env C:\webserver\www\sipepeng\.env.example
```

Variabel SiPepeng umum:

| Variabel | Kapan perlu diubah |
|----------|-------------------|
| `APP_URL` | Domain/subfolder berubah |
| `SESSION_SECURE_COOKIE` | Wajib `true` di HTTPS |
| `SIAKAD_API_*` | Token/URL Siakad berubah |
| `SIPEPENG_MOTIVATION_*` | Interval popup motivasi maskot |

Setelah ubah `.env`:

```bat
php artisan config:clear
php artisan config:cache
```

---

## Checklist verifikasi setelah update

| Cek | Cara |
|-----|------|
| Health | Buka `/up` |
| Login | `/login` — akun SIAKAD-GS |
| CSS/JS | DevTools → Network → `/build/` |
| Maskot | Bounce kiri bawah dashboard; popup motivasi |
| Modul utama | Buka Penelitian / PKM / HKI (sesuai role) |
| Upload | Unggah PDF kecil |
| Siakad | `php scripts\test-siakad-connection.php` |
| Log error | `storage\logs\laravel.log` (baris terakhir) |

---

## Troubleshooting setelah update

| Gejala | Solusi |
|--------|--------|
| 500 setelah pull | `php artisan optimize:clear`; cek log; jangan timpa `.env` |
| CSS/JS hilang | `npm ci && npm run build` |
| Error 419 login/form | `APP_URL` harus persis URL browser; `SESSION_SECURE_COOKIE=true` di HTTPS |
| Migrasi gagal | Backup DB; `php artisan migrate:status`; perbaiki error SQL |
| Siakad gagal | Token/URL; jalankan `scripts\test-siakad-connection.php` |
| `APP_KEY` invalid | Restore `.env` backup; jangan generate ulang key di production |

---

## Rollback cepat

1. Restore kode versi sebelumnya:
   ```bat
   git log --oneline -5
   git checkout <commit-hash-sebelumnya>
   ```
2. Restore `.env` backup jika tertimpa.
3. Rollback migrasi (hati-hati, bisa hilangkan kolom):
   ```bat
   php artisan migrate:rollback --step=1
   ```
4. `scripts\post-deploy.bat`
5. Restore database dari backup `.sql` jika migrasi sudah merusak data.

---

## Sinkron super admin (jika perlu)

Jika konfigurasi super admin di `config/sipepeng_bootstrap.php` berubah:

```bat
php artisan sipepeng:sync-siakad-super-admin
```

---

Lihat juga: [DEPLOY-WINDOWS-LARAGON.md](../DEPLOY-WINDOWS-LARAGON.md), [configuration-env.md](configuration-env.md), [backup-restore.md](backup-restore.md).
