<?php

return [

    /*
    | Popup otomatis pertama kali setelah halaman dimuat (detik).
    */
    'first_delay_seconds' => (int) env('SIPEPENG_MOTIVATION_FIRST_DELAY_SECONDS', 30),

    /*
    | Interval popup otomatis (menit).
    */
    'interval_minutes' => (int) env('SIPEPENG_MOTIVATION_INTERVAL_MINUTES', 5),

    /*
    | Setelah user menutup popup, jeda sebelum popup otomatis muncul lagi (menit).
    */
    'snooze_minutes' => (int) env('SIPEPENG_MOTIVATION_SNOOZE_MINUTES', 5),

    /*
    | Kutipan motivasi internal — penelitian & pengabdian masyarakat (LPPM).
    |
    | @var list<array{text: string, source: string}>
    */
    'quotes' => [
        // Penelitian
        [
            'text' => 'Penelitian bermakna dimulai dari pertanyaan yang relevan dengan permasalahan nyata di lapangan.',
            'source' => 'Penelitian',
        ],
        [
            'text' => 'Metode yang tepat membuat temuan penelitian lebih kuat dan dapat dipertanggungjawabkan.',
            'source' => 'Penelitian',
        ],
        [
            'text' => 'Literatur yang mendalam adalah fondasi proposal penelitian yang kredibel.',
            'source' => 'Penelitian',
        ],
        [
            'text' => 'Data primer yang valid lebih bernilai daripada asumsi yang tidak teruji.',
            'source' => 'Penelitian',
        ],
        [
            'text' => 'Penelitian kesehatan berorientasi pada peningkatan kualitas hidup masyarakat.',
            'source' => 'Penelitian',
        ],
        [
            'text' => 'Replikasi dan validasi memperkuat keabsahan hasil penelitian.',
            'source' => 'Penelitian',
        ],
        [
            'text' => 'Catatan laboratorium dan lapangan hari ini adalah aset ilmiah untuk masa depan.',
            'source' => 'Penelitian',
        ],
        [
            'text' => 'Penelitian yang baik dimulai dari masalah yang nyata dan data yang kuat.',
            'source' => 'Penelitian',
        ],

        // Pengabdian masyarakat
        [
            'text' => 'Pengabdian adalah jembatan antara ilmu di kampus dan kebutuhan di masyarakat.',
            'source' => 'Pengabdian Masyarakat',
        ],
        [
            'text' => 'Program pengabdian yang partisipatif melibatkan masyarakat sebagai mitra sejati.',
            'source' => 'Pengabdian Masyarakat',
        ],
        [
            'text' => 'Dampak pengabdian terukur dari perubahan perilaku, bukan hanya jumlah peserta.',
            'source' => 'Pengabdian Masyarakat',
        ],
        [
            'text' => 'Edukasi kesehatan yang tepat dapat menyelamatkan banyak keluarga.',
            'source' => 'Pengabdian Masyarakat',
        ],
        [
            'text' => 'Pengabdian berkelanjutan lebih bermakna daripada kegiatan sekali jadi.',
            'source' => 'Pengabdian Masyarakat',
        ],
        [
            'text' => 'Pemberdayaan masyarakat dimulai dari mendengarkan sebelum memberi solusi.',
            'source' => 'Pengabdian Masyarakat',
        ],
        [
            'text' => 'Pengabdian kepada masyarakat bukan hanya kegiatan, tetapi kontribusi ilmu untuk perubahan.',
            'source' => 'Pengabdian Masyarakat',
        ],
        [
            'text' => 'Setiap desa dan puskesmas mitra adalah ruang belajar bagi dosen dan mahasiswa.',
            'source' => 'Pengabdian Masyarakat',
        ],

        // Publikasi & luaran
        [
            'text' => 'Publikasi ilmiah adalah cara berbagi pengetahuan agar bermanfaat lebih luas.',
            'source' => 'Publikasi & Luaran',
        ],
        [
            'text' => 'Artikel jurnal yang terindeks adalah luaran penelitian yang membanggakan institusi.',
            'source' => 'Publikasi & Luaran',
        ],
        [
            'text' => 'Buku ajar dan modul hasil penelitian membantu generasi pembelajar berikutnya.',
            'source' => 'Publikasi & Luaran',
        ],
        [
            'text' => 'Luaran yang baik lahir dari perencanaan, pelaksanaan, dan pelaporan yang tertib.',
            'source' => 'Publikasi & Luaran',
        ],
        [
            'text' => 'Luaran penelitian yang terencana sejak awal memperkuat nilai kinerja LPPM.',
            'source' => 'Publikasi & Luaran',
        ],
        [
            'text' => 'Sitasi yang meningkat menandakan kontribusi ilmiah yang diakui komunitas.',
            'source' => 'Publikasi & Luaran',
        ],

        // HKI & inovasi
        [
            'text' => 'Inovasi sederhana yang terlindungi HKI dapat memberi manfaat jangka panjang.',
            'source' => 'HKI & Inovasi',
        ],
        [
            'text' => 'Produk atau protokol hasil riset layak dipertimbangkan sebagai kekayaan intelektual.',
            'source' => 'HKI & Inovasi',
        ],
        [
            'text' => 'Dokumentasi inovasi sejak dini mempermudah proses pendaftaran HKI.',
            'source' => 'HKI & Inovasi',
        ],

        // Kolaborasi & etika
        [
            'text' => 'Penelitian kolaboratif memperluas wawasan dan memperkuat jaringan ilmiah.',
            'source' => 'Kolaborasi & Etika',
        ],
        [
            'text' => 'Integritas akademik adalah nilai utama dalam setiap tahap penelitian dan pelaporan.',
            'source' => 'Kolaborasi & Etika',
        ],
        [
            'text' => 'Persetujuan etik penelitian melindungi subjek dan menjaga kredibilitas institusi.',
            'source' => 'Kolaborasi & Etika',
        ],
        [
            'text' => 'Transparansi data dan analisis membangun kepercayaan terhadap hasil riset.',
            'source' => 'Kolaborasi & Etika',
        ],
        [
            'text' => 'Tim peneliti yang saling melengkapi mengubah ide menjadi karya yang utuh.',
            'source' => 'Kolaborasi & Etika',
        ],

        // Dokumentasi & pelaporan
        [
            'text' => 'Dokumentasi kegiatan hari ini adalah bukti kinerja LPPM di masa akreditasi.',
            'source' => 'Dokumentasi & Pelaporan',
        ],
        [
            'text' => 'Laporan kemajuan yang tepat waktu menjaga kepercayaan pemberi pendanaan.',
            'source' => 'Dokumentasi & Pelaporan',
        ],
        [
            'text' => 'Arsip proposal, kontrak, dan bukti kegiatan adalah pelindung saat audit kinerja.',
            'source' => 'Dokumentasi & Pelaporan',
        ],
        [
            'text' => 'Input data ke SiPepeng hari ini menghemat waktu pelaporan di akhir periode.',
            'source' => 'Dokumentasi & Pelaporan',
        ],
        [
            'text' => 'Data yang lengkap membuat laporan lebih cepat, akurat, dan akuntabel.',
            'source' => 'Dokumentasi & Pelaporan',
        ],
        [
            'text' => 'Setiap proposal yang rapi mempercepat proses review dan validasi.',
            'source' => 'Dokumentasi & Pelaporan',
        ],

        // Semangat LPPM
        [
            'text' => 'Penelitian dan pengabdian adalah bagian penting dari identitas dosen profesional.',
            'source' => 'Semangat LPPM',
        ],
        [
            'text' => 'Setiap dosen peneliti berkontribusi pada reputasi akademik STIKES Gunung Sari.',
            'source' => 'Semangat LPPM',
        ],
        [
            'text' => 'Semangat menulis dan melaporkan adalah kebiasaan dosen yang unggul.',
            'source' => 'Semangat LPPM',
        ],
        [
            'text' => 'Kegiatan LPPM yang terdokumentasi baik akan memperkuat kinerja institusi.',
            'source' => 'Semangat LPPM',
        ],
        [
            'text' => 'Satu kegiatan yang dilaporkan dengan baik dapat menjadi banyak luaran bermanfaat.',
            'source' => 'Semangat LPPM',
        ],
        [
            'text' => 'Mulai dari langkah kecil hari ini, karya besar akan terwujud di waktu yang tepat.',
            'source' => 'Semangat LPPM',
        ],
    ],

];
