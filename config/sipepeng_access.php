<?php

return [

    /*
    | Role yang boleh mengakses dashboard utama SiPepeng.
    | super_admin selalu diizinkan oleh middleware role.
    */
    'dashboard' => [
        'roles' => [
            'super_admin',
            'admin_lppm',
            'ketua_lppm',
            'pimpinan',
            'dosen',
            'ketua_prodi',
            'reviewer',
            'mahasiswa',
        ],
    ],

];
