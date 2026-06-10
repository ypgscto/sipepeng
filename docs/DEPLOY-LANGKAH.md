# Langkah Deploy SiPepeng (Production)

Panduan singkat **update ke server** setelah ada perubahan di GitHub.  
Instalasi pertama: [DEPLOY-WINDOWS-LARAGON.md](../DEPLOY-WINDOWS-LARAGON.md).

---

## Path standar

| Lingkungan | Path |
|------------|------|
| Development | `C:\laragon\www\sipepeng` |
| Production (SiPepeng) | `C:\webserver\www\sipepeng` |
| Siakad-API (sibling) | `C:\webserver\www\siakad-api` |
| Document root | `...\sipepeng\public` |
| GitHub | `https://github.com/ypgscto/sipepeng.git` (branch `main`) |

---

## Bagian A — Di PC development (sebelum deploy)

### 1. Pastikan kode sudah di GitHub

```bat
cd C:\laragon\www\sipepeng
git status
git add .
git commit -m "Deskripsi perubahan"
git push origin main
```

### 2. Catat commit yang akan di-deploy

```bat
git log -1 --oneline
```

Simpan hash commit (mis. `c15f838`) untuk rollback jika perlu.

### 3. Siakad-API (jika ada perubahan endpoint)

Siakad-API **bukan** repo yang sama dengan SiPepeng. Salin manual dari dev ke server:

| File dev | Tujuan server |
|----------|---------------|
| `C:\laragon\www\siakad-api\app\Services\SipepengUserReadService.php` | `C:\webserver\www\siakad-api\app\Services\` |
| `C:\laragon\www\siakad-api\app\Http\Controllers\Api\SipepengSyncController.php` | `C:\webserver\www\siakad-api\app\Http\Controllers\Api\` |
| `C:\laragon\www\siakad-api\routes\api.php` | `C:\webserver\www\siakad-api\routes\` |
| `C:\laragon\www\siakad-api\config\siakad_api.php` | `C:\webserver\www\siakad-api\config\` |
| Patch login karyawan (`SiakadAuthService.php`) | `C:\webserver\www\siakad-api\app\Services\` |

Setelah salin, di server Siakad-API:

```bat
cd C:\webserver\www\siakad-api
php artisan config:clear
php artisan route:clear
```

Uji endpoint (ganti token):

```bat
curl -H "Authorization: Bearer TOKEN_SIAKAD" http://127.0.0.1/api/sipepeng/login-users?limit=1
```

---

## Bagian B — Di server production

Jalankan **Command Prompt atau Terminal Laragon** sebagai user yang punya akses folder web.

### 1. Backup (disarankan)

```bat
copy C:\webserver\www\sipepeng\.env C:\Temp\sipepeng.env.%date:~-4,4%%date:~-10,2%%date:~-7,2%.bak
```

Backup database (HeidiSQL / mysqldump) jika update menyertakan migrasi baru.

### 2. Tarik kode terbaru

```bat
cd C:\webserver\www\sipepeng
git pull origin main
```

Jika `git pull` menolak (local changes):

```bat
git stash
git pull origin main
git stash pop
```

*(Selesaikan konflik manual jika ada.)*

### 3. Jalankan post-deploy

```bat
scripts\post-deploy.bat
```

Script ini menjalankan:

- `composer install --no-dev`
- `npm ci` + `npm run build`
- `php artisan migrate --force`
- `php artisan optimize:clear` + cache config/route/view

### 4. Seed role (hanya jika diminta / pertama kali mapping role)

```bat
php artisan db:seed --class=SipepengRoleSeeder
```

### 5. Sinkron user Siakad (setelah modul user sync terpasang)

**Via UI (disarankan):**

1. Login sebagai `super_admin` atau `admin_lppm`
2. **Pengaturan → Sinkron User Login** → klik **Sinkronkan User Login**
3. **Pengaturan → Pengaturan Pengguna** → aktivasi login + peran untuk akun yang perlu

**Via command (contoh):**

```bat
php artisan sipepeng:allow-siakad-user kalemlp2m --role=ketua_lppm
php artisan sipepeng:allow-siakad-user swbahrun@gmail.com --role=ketua_lppm
```

Password login tetap **password SIAKAD-GS**, bukan password lokal.

---

## Bagian C — Verifikasi setelah deploy

| No | Cek | Cara / URL |
|----|-----|------------|
| 1 | Health | Buka `/up` → status OK |
| 2 | Favicon | Tab browser menampilkan ikon maskot (`/images/sipepeng_maskot_icon.png`) |
| 3 | Login | `/login` — uji akun Siakad |
| 4 | CSS/JS | DevTools → Network → file dari `/build/` tidak 404 |
| 5 | Menu Sinkron User | **Pengaturan → Sinkron User Login** atau sidebar **Sistem** |
| 6 | Siakad-API | `php scripts\test-siakad-connection.php` |
| 7 | Panduan | Menu **Panduan / SOP** → `/panduan` |
| 8 | Log error | `storage\logs\laravel.log` — tidak ada error baru |

Hard refresh browser (**Ctrl+F5**) jika favicon atau CSS lama masih tampil.

---

## Bagian D — Troubleshooting cepat

| Gejala | Langkah |
|--------|---------|
| Menu **Sinkron User** tidak muncul | `git log -1` — pastikan commit terbaru; `php artisan view:clear` + `route:clear` |
| Login: *credentials do not match* | Patch Siakad-API belum disalin; uji `POST /api/auth/login-app` |
| Login: *belum diaktifkan* | **Pengaturan Pengguna** → centang *Diizinkan login* |
| Error duplicate email saat login | Pull commit fix provisioning; hapus user stub duplikat di Pengaturan Pengguna |
| Sinkron user gagal | Siakad-API endpoint `/api/sipepeng/login-users` belum ada |
| Halaman 500 | `php artisan optimize:clear`; cek `storage\logs\laravel.log` |
| CSS hilang | `npm ci && npm run build` |
| Error 419 form | `APP_URL` harus sama persis dengan URL browser |

---

## Bagian E — Rollback

```bat
cd C:\webserver\www\sipepeng
git log --oneline -5
git checkout <commit-sebelumnya>
scripts\post-deploy.bat
```

Restore `.env` dari backup jika perlu.

---

## Ringkasan satu halaman

```
[Dev]     git push origin main
              │
[Server]  backup .env (+ DB jika migrasi)
              │
          cd C:\webserver\www\sipepeng
          git pull origin main
          scripts\post-deploy.bat
              │
          (opsional) salin patch Siakad-API manual
          (opsional) sinkron user + aktivasi login
              │
          uji /up, /login, menu Pengaturan
```

---

Lihat juga: [DEPLOY-UPDATE.md](DEPLOY-UPDATE.md), [DEPLOY-IP-TEMPORARY.md](DEPLOY-IP-TEMPORARY.md), [DEPLOY-WINDOWS-LARAGON.md](../DEPLOY-WINDOWS-LARAGON.md).
