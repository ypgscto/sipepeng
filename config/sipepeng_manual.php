<?php

return [

    'title' => 'Panduan & SOP SiPepeng',

    'version' => '1.0',

    'updated_at' => '2026-06-05',

    /*
    | Daftar modul panduan (slug => metadata).
    | Konten HTML: resources/views/manual/modules/{slug}.blade.php
    */
    'modules' => [
        'umum' => [
            'title' => 'Umum & Login',
            'summary' => 'Login SIAKAD-GS, navigasi, dashboard, notifikasi, dan profil.',
            'icon' => 'home',
        ],
        'siakad-referensi' => [
            'title' => 'Data Referensi SIAKAD',
            'summary' => 'Sinkron dan refresh data prodi, dosen, mahasiswa dari Siakad-API.',
            'icon' => 'sync',
        ],
        'data-master' => [
            'title' => 'Data Master LPPM',
            'summary' => 'Skema, kategori dokumen, reviewer, template, dan referensi pendukung.',
            'icon' => 'database',
        ],
        'penelitian' => [
            'title' => 'Penelitian',
            'summary' => 'Proposal penelitian, RAB, review, keputusan, dan lampiran.',
            'icon' => 'research',
        ],
        'pengabdian' => [
            'title' => 'Pengabdian Masyarakat (PkM)',
            'summary' => 'Proposal PkM, anggota tim, anggaran, review, dan pelaporan.',
            'icon' => 'community',
        ],
        'publikasi' => [
            'title' => 'Publikasi Ilmiah',
            'summary' => 'Pencatatan publikasi, verifikasi admin LPPM, dan lampiran.',
            'icon' => 'publication',
        ],
        'hki' => [
            'title' => 'HKI dan Paten',
            'summary' => 'Pendaftaran HKI, antrian verifikasi, dan status kekayaan intelektual.',
            'icon' => 'certificate',
        ],
        'etik-penelitian' => [
            'title' => 'Etik Penelitian',
            'summary' => 'Pengajuan etik, dokumen persetujuan, dan verifikasi LPPM.',
            'icon' => 'shield',
        ],
        'surat' => [
            'title' => 'Surat LPPM',
            'summary' => 'Pembuatan surat, nomor surat, tanda tangan, dan unduh PDF.',
            'icon' => 'letter',
        ],
        'laporan' => [
            'title' => 'Laporan',
            'summary' => 'Dashboard laporan, filter per peran, export Excel/PDF.',
            'icon' => 'report',
        ],
        'pengaturan' => [
            'title' => 'Pengaturan Sistem',
            'summary' => 'Branding, SIAKAD-API, mapping peran, template, dan backup.',
            'icon' => 'cog',
        ],
    ],

];
