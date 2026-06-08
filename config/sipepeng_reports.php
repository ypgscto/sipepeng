<?php

return [

    'view_all_roles' => ['super_admin', 'admin_lppm', 'ketua_lppm', 'pimpinan'],

    'export_roles' => ['super_admin', 'admin_lppm', 'ketua_lppm', 'pimpinan', 'ketua_prodi'],

    'prodi_scope_roles' => ['ketua_prodi'],

    'dosen_scope_roles' => ['dosen'],

    'sync_export_max_rows' => 5000,

    'approved_statuses' => [
        'research' => ['approved'],
        'pkm' => ['approved'],
    ],

    'rejected_statuses' => [
        'research' => ['rejected', 'admin_rejected'],
        'pkm' => ['rejected', 'admin_rejected'],
    ],

    'revision_statuses' => [
        'research' => ['revision_required'],
        'pkm' => ['revision_required'],
    ],

    'submitted_statuses' => [
        'research' => ['submitted', 'admin_pending', 'admin_verified', 'review_assigned', 'review_completed', 'approved', 'rejected', 'admin_rejected', 'revision_required'],
        'pkm' => ['submitted', 'admin_pending', 'admin_verified', 'review_assigned', 'review_completed', 'approved', 'rejected', 'admin_rejected', 'revision_required'],
    ],

    'publication_count_statuses' => ['verified', 'published_confirmed'],

    'hki_count_statuses' => ['verified', 'registered', 'granted', 'approved'],

    'ethics_count_statuses' => ['approved'],

    /*
    | Peran yang boleh melihat setiap tipe laporan (super_admin selalu diizinkan).
    */
    'type_roles' => [
        'research' => ['admin_lppm', 'ketua_lppm', 'pimpinan', 'ketua_prodi', 'dosen'],
        'pkm' => ['admin_lppm', 'ketua_lppm', 'pimpinan', 'ketua_prodi', 'dosen'],
        'publications' => ['admin_lppm', 'ketua_lppm', 'pimpinan', 'ketua_prodi', 'dosen'],
        'hki' => ['admin_lppm', 'ketua_lppm', 'pimpinan', 'ketua_prodi', 'dosen'],
        'ethics' => ['admin_lppm', 'ketua_lppm', 'pimpinan', 'ketua_prodi', 'dosen'],
        'partners' => ['admin_lppm', 'ketua_lppm', 'pimpinan', 'ketua_prodi'],
        'funding' => ['admin_lppm', 'ketua_lppm', 'pimpinan', 'ketua_prodi', 'dosen'],
        'lecturer-performance' => ['admin_lppm', 'ketua_lppm', 'pimpinan', 'ketua_prodi'],
        'prodi-performance' => ['admin_lppm', 'ketua_lppm', 'pimpinan', 'ketua_prodi'],
        'accreditation' => ['admin_lppm', 'ketua_lppm', 'pimpinan'],
    ],

    'types' => [
        'research' => ['label' => 'Penelitian', 'icon' => 'research'],
        'pkm' => ['label' => 'Pengabdian Masyarakat', 'icon' => 'community'],
        'publications' => ['label' => 'Publikasi', 'icon' => 'document'],
        'hki' => ['label' => 'HKI', 'icon' => 'certificate'],
        'ethics' => ['label' => 'Etik Penelitian', 'icon' => 'shield'],
        'partners' => ['label' => 'Mitra', 'icon' => 'handshake'],
        'funding' => ['label' => 'Dana', 'icon' => 'chart'],
        'lecturer-performance' => ['label' => 'Kinerja Dosen', 'icon' => 'document'],
        'prodi-performance' => ['label' => 'Kinerja Prodi', 'icon' => 'database'],
        'accreditation' => ['label' => 'Akreditasi', 'icon' => 'certificate'],
    ],

    'accreditation_indicators' => [
        ['code' => 'IND-PEN-01', 'label' => 'Jumlah proposal penelitian disetujui', 'module' => 'research', 'metric' => 'research_approved_count'],
        ['code' => 'IND-PKM-01', 'label' => 'Jumlah kegiatan PkM disetujui', 'module' => 'pkm', 'metric' => 'pkm_approved_count'],
        ['code' => 'IND-PUB-01', 'label' => 'Jumlah publikasi terverifikasi', 'module' => 'publications', 'metric' => 'publications_count'],
        ['code' => 'IND-HKI-01', 'label' => 'Jumlah HKI terdaftar/terverifikasi', 'module' => 'hki', 'metric' => 'hki_count'],
        ['code' => 'IND-ETK-01', 'label' => 'Jumlah persetujuan etik penelitian', 'module' => 'ethics', 'metric' => 'ethics_count'],
        ['code' => 'IND-MTR-01', 'label' => 'Jumlah mitra aktif terlibat PkM', 'module' => 'partners', 'metric' => 'active_partners'],
        ['code' => 'IND-DNA-01', 'label' => 'Total RAB penelitian (Rp)', 'module' => 'funding', 'metric' => 'research_funding_total'],
        ['code' => 'IND-DNA-02', 'label' => 'Total RAB PkM (Rp)', 'module' => 'funding', 'metric' => 'pkm_funding_total'],
        ['code' => 'IND-SDM-01', 'label' => 'Jumlah dosen aktif meneliti/mengabdi', 'module' => 'lecturer', 'metric' => 'active_lecturers'],
        ['code' => 'IND-MSW-01', 'label' => 'Jumlah mahasiswa terlibat kegiatan LPPM', 'module' => 'students', 'metric' => 'students_involved'],
    ],

];
