<?php

return [

    /*
    | Role yang boleh mengakses halaman pengaturan aplikasi.
    */
    'view_roles' => ['super_admin', 'admin_lppm'],

    'manage_roles' => ['super_admin', 'admin_lppm'],

    /*
    | Backup database hanya super_admin (operasi sensitif).
    */
    'backup_roles' => ['super_admin'],

    'logo' => [
        'max_kilobytes' => 2048,
        'allowed_mimes' => ['png', 'jpg', 'jpeg', 'webp'],
        'public_subdirectory' => 'images',
        'filename' => 'institution-logo',
    ],

    'backup' => [
        'disk' => 'local',
        'directory' => 'backups',
        'retention_count' => 10,
    ],

    'siakad_map_types' => [
        'jenis_user' => 'Jenis User SIAKAD',
        'level_id' => 'Level ID SIAKAD',
    ],

];
