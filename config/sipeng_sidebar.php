<?php

return [

    'groups' => [
        [
            'label' => 'Utama',
            'items' => [
                [
                    'label' => 'Dashboard',
                    'route' => 'dashboard',
                    'icon' => 'home',
                    'active_routes' => ['dashboard'],
                ],
                [
                    'label' => 'Laporan',
                    'route' => 'admin.reports.index',
                    'icon' => 'report',
                    'active_routes' => ['admin.reports.*'],
                    'roles' => ['super_admin', 'admin_lppm', 'ketua_lppm', 'pimpinan', 'ketua_prodi', 'dosen'],
                ],
                [
                    'label' => 'Panduan / SOP',
                    'route' => 'manual.index',
                    'icon' => 'book',
                    'active_routes' => ['manual.*'],
                ],
            ],
        ],
        [
            'label' => 'Transaksi LPPM',
            'items' => [
                [
                    'label' => 'Penelitian',
                    'route' => 'admin.research.index',
                    'icon' => 'research',
                    'active_routes' => ['admin.research.*'],
                ],
                [
                    'label' => 'Pengabdian Masyarakat',
                    'route' => 'admin.community-service.index',
                    'icon' => 'community',
                    'active_routes' => ['admin.community-service.*'],
                ],
                [
                    'label' => 'Publikasi Ilmiah',
                    'route' => 'admin.publications.index',
                    'icon' => 'publication',
                    'active_routes' => ['admin.publications.*'],
                ],
                [
                    'label' => 'HKI dan Paten',
                    'route' => 'admin.hki.index',
                    'icon' => 'certificate',
                    'active_routes' => ['admin.hki.*'],
                ],
                [
                    'label' => 'Etik Penelitian',
                    'route' => 'admin.research-ethics.index',
                    'icon' => 'shield',
                    'active_routes' => ['admin.research-ethics.*'],
                ],
                [
                    'label' => 'Surat LPPM',
                    'route' => 'admin.letters.index',
                    'icon' => 'letter',
                    'active_routes' => ['admin.letters.*'],
                ],
            ],
        ],
        [
            'label' => 'Referensi & Master',
            'items' => [
                [
                    'label' => 'Data Referensi SIAKAD',
                    'route' => 'admin.siakad-reference.index',
                    'icon' => 'sync',
                    'active_routes' => ['admin.siakad-reference.*'],
                    'roles' => ['super_admin', 'admin_lppm', 'ketua_lppm'],
                ],
                [
                    'label' => 'Data Master',
                    'route' => 'admin.master.index',
                    'icon' => 'database',
                    'active_routes' => ['admin.master.index'],
                    'roles' => ['super_admin', 'admin_lppm', 'ketua_lppm'],
                ],
                [
                    'label' => 'Skema Penelitian',
                    'route' => 'admin.master.research-schemes.index',
                    'icon' => 'research',
                    'active_routes' => ['admin.master.research-schemes.*'],
                    'roles' => ['super_admin', 'admin_lppm', 'ketua_lppm'],
                ],
                [
                    'label' => 'Kategori Dokumen',
                    'route' => 'admin.master.document-categories.index',
                    'icon' => 'document',
                    'active_routes' => ['admin.master.document-categories.*'],
                    'roles' => ['super_admin', 'admin_lppm', 'ketua_lppm'],
                ],
            ],
        ],
        [
            'label' => 'Sistem',
            'items' => [
                [
                    'label' => 'Pengaturan',
                    'route' => 'admin.settings.index',
                    'icon' => 'cog',
                    'active_routes' => ['admin.settings.*'],
                    'roles' => ['super_admin', 'admin_lppm'],
                ],
            ],
        ],
    ],

    /*
    | Flat list untuk dashboard shortcuts (backward compatible).
    */
    'items' => [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'home'],
        ['label' => 'Penelitian', 'route' => 'admin.research.index', 'icon' => 'research'],
        ['label' => 'Pengabdian Masyarakat', 'route' => 'admin.community-service.index', 'icon' => 'community'],
        ['label' => 'Publikasi Ilmiah', 'route' => 'admin.publications.index', 'icon' => 'publication'],
        ['label' => 'Laporan', 'route' => 'admin.reports.index', 'icon' => 'report'],
        ['label' => 'Surat LPPM', 'route' => 'admin.letters.index', 'icon' => 'letter'],
    ],

];
