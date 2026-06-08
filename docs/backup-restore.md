# Backup & Restore Database SiPepeng

## Siapa yang boleh backup

Hanya pengguna dengan peran **`super_admin`**.

Menu: **Admin → Pengaturan → Backup Database**

## Backup via aplikasi

1. Login sebagai super admin.
2. Buka **Pengaturan → Backup**.
3. Klik **Buat Backup**.
4. Unduh file dari daftar log backup.

### Detail teknis

- File disimpan di `storage/app/backups/` (disk `local`, **private**).
- Format: `.sql` (MySQL) atau `.sqlite` (SQLite).
- Retensi: 10 file terakhir (config `sipepeng_settings.backup.retention_count`).
- Throttle: maks. 3 backup per 10 menit.
- Aktivitas tercatat di activity log (`security`).

## Restore MySQL

```bash
# Maintenance mode (disarankan)
php artisan down

mysql -u USER -p DATABASE_NAME < storage/app/backups/sipepeng_backup_YYYY-MM-DD_HHMMSS.sql

php artisan up
```

## Restore SQLite

```bash
php artisan down
copy storage\app\backups\sipepeng_backup_YYYY-MM-DD_HHMMSS.sqlite database\database.sqlite
php artisan up
```

## APP_KEY dan token SIAKAD

Token SIAKAD-API di database dienkripsi dengan `APP_KEY`.

Jika `APP_KEY` berubah setelah restore:

1. Login super admin (fallback lokal jika dikonfigurasi).
2. Pengaturan → SIAKAD-API → masukkan **token baru**.
3. Simpan.

## Backup server (disarankan produksi)

Selain backup UI, jadwalkan mysqldump di server:

```bash
mysqldump -u user -p sipepeng > /backup/sipepeng_$(date +%F).sql
```

- Enkripsi file backup at-rest.
- Jangan commit backup ke Git.
- Uji restore minimal **sekali per kuartal**.

## Troubleshooting

| Gejala | Tindakan |
|--------|----------|
| Backup gagal | Pastikan `mysqldump` ada di PATH (MySQL) atau permission `storage/` |
| File backup 0 byte | Cek log Laravel (`storage/logs/laravel.log`) |
| Unduh 404 | File mungkin sudah dihapus oleh retensi — buat backup baru |
