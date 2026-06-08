# Panduan Penggunaan Awal SiPepeng

## Login

1. Buka halaman **Masuk** (`/login`).
2. Gunakan **email atau username SIAKAD** dan kata sandi yang sama dengan SIAKAD.
3. Jika muncul pesan akun belum diaktifkan, hubungi **administrator LPPM** untuk mengaktifkan `is_allowed_login` dan peran.
4. Reset password hanya melalui SIAKAD — SiPepeng tidak menyimpan password Siakad.

Pola login mengikuti SIMAWA-GS: verifikasi via `POST /api/auth/login-app`, provisioning user lokal, gate `is_allowed_login`.

## Dashboard

- Ringkasan statistik penelitian, PkM, publikasi, HKI, dan dana.
- Filter tahun akademik, prodi, dosen — **otomatis dibatasi sesuai peran** Anda.
- Tombol **Laporan LPPM** menuju modul laporan lengkap.

## Modul utama

### Penelitian & PkM

1. **Buat proposal** (dosen/admin).
2. Unggah berkas wajib (PDF).
3. Isi RAB jika ada.
4. **Ajukan** — status berubah ke antrian admin LPPM.
5. Alur: verifikasi admin → review → keputusan ketua LPPM.

### Publikasi, HKI, Etik, Surat

Alur serupa: draft → ajukan → verifikasi admin LPPM → selesai.

Notifikasi muncul di **bell** kanan atas.

## Laporan & export

| Peran | Akses |
|-------|-------|
| Admin LPPM / Ketua LPPM / Pimpinan | Semua laporan + export |
| Ketua prodi | Laporan prodi sendiri |
| Dosen | Penelitian/PkM/publikasi/HKI/etik milik sendiri |

Export **Excel** dan **PDF** tersedia untuk peran yang diizinkan (maks. 5000 baris per export).

## Notifikasi

- Klik bell → daftar notifikasi.
- **Tandai dibaca** atau buka link aksi ke proposal terkait.

## Pengaturan (admin LPPM / super admin)

- **Profil & footer** — nama institusi, footer default YPGS IT Division, 2026.
- **Logo** — PNG/JPG/WebP.
- **SIAKAD-API** — URL dan token (token tidak ditampilkan).
- **Mapping role** — petakan jenis user Siakad ke peran SiPepeng.

**Backup database** — hanya **super_admin** (Pengaturan → Backup).

## FAQ

**Proposal tidak muncul di daftar**  
Pastikan Anda login sebagai ketua proposal atau admin LPPM.

**Filter prodi kosong di dashboard**  
Refresh referensi SIAKAD (admin) dan pastikan akun dosen terdaftar di data dosen Siakad.

**Akses ditolak**  
Peran Anda tidak mencakup halaman tersebut — lihat pesan di `/akses-ditolak`.
