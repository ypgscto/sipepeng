<?php

return [

    /*
    | Provision super admin awal dari profil Siakad (tanpa password lokal).
    | Login pertama memakai kredensial SIAKAD-GS.
    */
    'seed_on_migrate' => (bool) env('SIPEPENG_BOOTSTRAP_SUPER_ADMIN', true),

    /*
    | Hapus akun demo @sipepeng.test saat bootstrap dijalankan.
    */
    'purge_demo_accounts' => (bool) env('SIPEPENG_PURGE_DEMO_ACCOUNTS', true),

    /*
    | Profil super admin — dipetakan via login_role_overrides + SiakadUserProvisionService.
    |
    | @var list<array<string, mixed>>
    */
    'super_admins' => [
        [
            'login' => 'bashar.ypgs@gmail.com',
            'email' => 'bashar.ypgs@gmail.com',
            'siakad_user_id' => 'bashar.ypgs@gmail.com',
            'nama' => 'Bashar YPGS',
            'jenis_role' => 'superadmin',
            'user_category' => 'pegawai',
        ],
    ],

];
