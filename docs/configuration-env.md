# Konfigurasi Environment (`.env`)

## Aplikasi

| Variabel | Deskripsi | Produksi |
|----------|-----------|----------|
| `APP_NAME` | Nama tampilan | `SiPepeng` |
| `APP_ENV` | Environment | `production` |
| `APP_DEBUG` | Mode debug | `false` |
| `APP_URL` | URL dasar (HTTPS) | `https://sipepeng.stikes...` |
| `APP_KEY` | Kunci enkripsi Laravel | Generate sekali; **jangan rotate** tanpa re-enter token Siakad |

## Database

| Variabel | Deskripsi |
|----------|-----------|
| `DB_CONNECTION` | `mysql` atau `sqlite` |
| `DB_HOST` | Host MySQL |
| `DB_PORT` | Port (3306) |
| `DB_DATABASE` | Nama database |
| `DB_USERNAME` | User DB (least privilege) |
| `DB_PASSWORD` | Password DB |

## Session & keamanan

| Variabel | Deskripsi | Produksi |
|----------|-----------|----------|
| `SESSION_DRIVER` | `database` atau `file` | `database` |
| `SESSION_LIFETIME` | Menit | `120` |
| `SESSION_ENCRYPT` | Enkripsi session | `true` (disarankan) |
| `SESSION_SECURE_COOKIE` | Cookie HTTPS only | `true` |

## SIAKAD-API (fallback `.env`)

Konfigurasi utama dapat di-override via **Pengaturan → SIAKAD-API** (disimpan di database, token terenkripsi).

| Variabel | Deskripsi |
|----------|-----------|
| `SIAKAD_API_BASE_URL` | URL root API, mis. `http://siakad-api.test` |
| `SIAKAD_API_TOKEN` | Bearer token server-to-server |
| `SIAKAD_API_TIMEOUT` | Timeout detik (default 120) |
| `SIAKAD_API_PAGE_LIMIT` | Page size sync referensi (500) |
| `SIAKAD_AUTH_LOGIN_ENDPOINT` | Endpoint login, default `/api/auth/login-app` |
| `SIAKAD_ENDPOINT_*` | Path endpoint referensi (prodi, dosen, dll.) |
| `SIAKAD_API_HOST` | Header Host jika base URL `127.0.0.1` |

Prioritas: **DB settings** → **`.env`** (lihat `App\Support\Siakad\SiakadConfig`).

## SiPepeng — autentikasi

| Variabel | Default | Deskripsi |
|----------|---------|-----------|
| `SIPEPENG_AUTO_ALLOW_LOGIN_ON_FIRST_LOGIN` | `false` | User baru dari Siakad langsung boleh login |
| `SIPEPENG_ALLOW_LOCAL_LOGIN_FALLBACK` | `false` | Fallback password lokal jika Siakad down (maintenance) |
| `SIPEPENG_SIAKAD_EMAIL_DOMAIN` | `stikesgunungsari.ac.id` | Domain email sintetis untuk login non-email |

## Logging & queue

| Variabel | Produksi |
|----------|----------|
| `LOG_LEVEL` | `warning` atau `error` |
| `QUEUE_CONNECTION` | `database` atau `redis` |
| `FILESYSTEM_DISK` | `local` |

## Mail (opsional)

Notifikasi saat ini in-app. Untuk email nanti:

```env
MAIL_MAILER=smtp
MAIL_HOST=...
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS=lppm@stikesgunungsari.ac.id
MAIL_FROM_NAME="SiPepeng"
```

## Branding default

Footer kredit: **YPGS IT Division, 2026** — di `config/sipeng_branding.php` atau Pengaturan → Profil Aplikasi.
