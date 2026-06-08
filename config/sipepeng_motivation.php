<?php

return [

    /*
    | Interval popup otomatis (menit).
    */
    'interval_minutes' => (int) env('SIPEPENG_MOTIVATION_INTERVAL_MINUTES', 5),

    /*
    | Setelah user menutup popup, jeda sebelum popup otomatis muncul lagi (menit).
    */
    'snooze_minutes' => (int) env('SIPEPENG_MOTIVATION_SNOOZE_MINUTES', 5),

    /*
    | Kutipan motivasi internal — tidak diambil dari internet.
    |
    | @var list<array{text: string, source: string}>
    */
    'quotes' => [
        [
            'text' => 'Penelitian yang baik dimulai dari masalah yang nyata dan data yang kuat.',
            'source' => 'SiPepeng Assistant',
        ],
        [
            'text' => 'Dokumentasi kegiatan hari ini adalah bukti kinerja LPPM di masa akreditasi.',
            'source' => 'LPPM STIKES Gunung Sari',
        ],
        [
            'text' => 'Pengabdian kepada masyarakat bukan hanya kegiatan, tetapi kontribusi ilmu untuk perubahan.',
            'source' => 'SiPepeng Assistant',
        ],
        [
            'text' => 'Luaran yang baik lahir dari perencanaan, pelaksanaan, dan pelaporan yang tertib.',
            'source' => 'SiPepeng Assistant',
        ],
        [
            'text' => 'Publikasi ilmiah adalah cara ilmu pengetahuan memberi manfaat lebih luas.',
            'source' => 'SiPepeng Assistant',
        ],
        [
            'text' => 'Setiap proposal yang rapi mempercepat proses review dan validasi.',
            'source' => 'SiPepeng Assistant',
        ],
        [
            'text' => 'Kegiatan LPPM yang terdokumentasi baik akan memperkuat kinerja institusi.',
            'source' => 'LPPM STIKES Gunung Sari',
        ],
        [
            'text' => 'Penelitian dan pengabdian adalah bagian penting dari identitas dosen profesional.',
            'source' => 'SiPepeng Assistant',
        ],
        [
            'text' => 'Data yang lengkap membuat laporan lebih cepat, akurat, dan akuntabel.',
            'source' => 'SiPepeng Assistant',
        ],
        [
            'text' => 'Satu kegiatan yang dilaporkan dengan baik dapat menjadi banyak luaran bermanfaat.',
            'source' => 'SiPepeng Assistant',
        ],
    ],

];
