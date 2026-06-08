<?php

return [

    /*
    | Login via Siakad-API (password diverifikasi di SIAKAD, bukan di SiPepeng).
    */
    'login_endpoint' => env('SIAKAD_AUTH_LOGIN_ENDPOINT', '/api/auth/login-app'),

    /*
    | Setelah login Siakad, akun lokal dibuat tetapi login SiPepeng tetap
    | diblokir sampai admin mengaktifkan is_allowed_login.
    */
    'auto_allow_login_on_first_login' => (bool) env('SIPEPENG_AUTO_ALLOW_LOGIN_ON_FIRST_LOGIN', false),

    /*
    | Fallback login lokal (super_admin) jika Siakad tidak tersedia.
    | Nonaktifkan di produksi kecuali diperlukan untuk maintenance.
    */
    'allow_local_fallback' => (bool) env('SIPEPENG_ALLOW_LOCAL_LOGIN_FALLBACK', false),

    /*
    | Peran dari Siakad hanya diterapkan saat user baru pertama kali masuk ke DB lokal.
    */
    'apply_siakad_roles_on_update' => (bool) env('SIPEPENG_APPLY_SIAKAD_ROLES_ON_UPDATE', true),

    'email_domain' => env('SIPEPENG_SIAKAD_EMAIL_DOMAIN', 'stikesgunungsari.ac.id'),

    'denied_jenis_user' => ['0', '1'],

    /*
    | Cadangan pemetaan jika belum ada di tabel sipepeng_roles.
    |
    | @var array<string, list<string>>
    */
    'role_map' => [
        '8' => ['admin_lppm'],
        '7' => ['dosen'],
        '6' => ['ketua_prodi'],
        '5' => ['pimpinan'],
        '4' => [],
    ],

    'level_id_role_map' => [
        '100' => ['mahasiswa'],
    ],

    /*
    | Override peran berdasarkan login/email Siakad (lowercase).
    | Berguna untuk super admin IT yang tidak terpetakan otomatis.
    |
    | @var array<string, list<string>>
    */
    'login_role_overrides' => [
        'bashar.ypgs@gmail.com' => ['super_admin'],
    ],

    /*
    | Peran yang otomatis boleh login saat pertama kali masuk (tanpa aktivasi manual).
    */
    'auto_allow_login_roles' => ['super_admin'],

    /*
    | Role yang boleh login meskipun jenis_user Siakad diblokir (mis. super admin IT).
    */
    'bypass_denied_jenis_user_roles' => ['super_admin'],

];
