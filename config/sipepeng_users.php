<?php

return [

    'per_page' => (int) env('SIPEPENG_USERS_PER_PAGE', 20),

    /*
    | Peran yang boleh ditetapkan admin di Pengaturan Pengguna.
    |
    | @var list<string>
    */
    'assignable_roles' => [
        'super_admin',
        'admin_lppm',
        'ketua_lppm',
        'pimpinan',
        'ketua_prodi',
        'reviewer',
        'dosen',
        'mahasiswa',
    ],

];
