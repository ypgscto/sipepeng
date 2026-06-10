# SiPepeng

Sistem Informasi Penelitian dan Pengabdian Masyarakat — STIKES Gunung Sari (LPPM).

## Dokumentasi

| Dokumen | Isi |
|---------|-----|
| [docs/installation.md](docs/installation.md) | Instalasi aplikasi (lokal) |
| [DEPLOY-WINDOWS-LARAGON.md](DEPLOY-WINDOWS-LARAGON.md) | **Deploy production — Windows Server + Laragon** |
| [DEPLOY.md](DEPLOY.md) | Deploy production — Linux |
| [docs/DEPLOY-UPDATE.md](docs/DEPLOY-UPDATE.md) | Update versi via `git pull` |
| [docs/DEPLOY-LANGKAH.md](docs/DEPLOY-LANGKAH.md) | **Langkah deploy production (checklist)** |
| [docs/DEPLOY-IP-TEMPORARY.md](docs/DEPLOY-IP-TEMPORARY.md) | **Deploy sementara via IP (tanpa domain)** |
| [docs/configuration-env.md](docs/configuration-env.md) | Variabel `.env` |
| [docs/getting-started.md](docs/getting-started.md) | Panduan pengguna awal |
| [docs/backup-restore.md](docs/backup-restore.md) | Backup & restore database |

## Repository GitHub

```
https://github.com/ypgscto/sipepeng.git
```

## Quick start (Laragon)

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

Login: akun SIAKAD yang sudah diaktifkan admin LPPM.

Footer default: **YPGS IT Division, 2026**

## Deploy production (server baru)

1. Push kode ke GitHub (`ypgscto/sipepeng`).
2. Di server: `git clone` → `.env` → `scripts\first-install.bat`.
3. Ikuti **[DEPLOY-WINDOWS-LARAGON.md](DEPLOY-WINDOWS-LARAGON.md)** (Windows) atau **[DEPLOY.md](DEPLOY.md)** (Linux).
