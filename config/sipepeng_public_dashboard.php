<?php

return [

    'cache_ttl_seconds' => 900,

    'allowed_years_back' => 10,

    'statuses' => [
        'research' => ['approved'],
        'pkm' => ['approved'],
        'publication' => ['verified', 'published_confirmed'],
        'hki' => ['verified', 'registered', 'granted', 'approved'],
        'ethics' => ['approved'],
    ],

    'about' => [
        'title' => 'Tentang SiPepeng',
        'body' => 'SiPepeng (Sistem Informasi Penelitian dan Pengabdian Masyarakat) adalah portal transparansi dan layanan LPPM STIKES Gunung Sari untuk kegiatan penelitian, pengabdian masyarakat, publikasi ilmiah, dan HKI. Dashboard ini menampilkan ringkasan kinerja agregat yang telah divalidasi.',
    ],

    'lppm_focus' => [
        'Peningkatan kualitas penelitian dan PkM berbasis kebutuhan masyarakat dan dunia kesehatan.',
        'Penguatan publikasi ilmiah dan perlindungan HKI sivitas akademika.',
        'Kolaborasi multidisiplin antar program studi dan mitra eksternal.',
        'Pembinaan etika penelitian dan tata kelola LPPM yang akuntabel.',
    ],

    'featured_themes' => [
        ['title' => 'Kesehatan Masyarakat', 'description' => 'Intervensi promotif-preventif dan pemberdayaan masyarakat.'],
        ['title' => 'Keperawatan & Kebidanan', 'description' => 'Inovasi klinis, edukasi pasien, dan model asuhan.'],
        ['title' => 'Farmasi & Gizi', 'description' => 'Studi obat-obatan, nutrisi, dan keamanan pangan.'],
        ['title' => 'Teknologi Kesehatan', 'description' => 'Digital health, telemedicine, dan sistem informasi kesehatan.'],
    ],

    'announcements' => [],

    'calendar_events' => [],

];
