<?php

return [

    'view_all_roles' => ['super_admin', 'admin_lppm', 'ketua_lppm', 'pimpinan'],

    'manage_roles' => ['super_admin', 'admin_lppm'],

    'approve_roles' => ['super_admin', 'admin_lppm', 'ketua_lppm'],

    'issue_roles' => ['super_admin', 'admin_lppm'],

    'proposer_roles' => ['dosen', 'super_admin', 'admin_lppm'],

    'storage_disk' => env('LETTER_STORAGE_DISK', 'local'),

    'storage_path' => 'lppm/letters',

    'place_of_issue' => 'Mataram',

    'institution_name' => 'STIKES Gunung Sari',

    'default_number_pattern' => '{prefix}/{seq:04d}/{year}',

    'draft_number_pattern' => 'DRAFT/SRT/{year}/{seq:04d}',

    'linkable_proposal_statuses' => ['approved', 'review_completed', 'admin_verified'],

    'uploads' => [
        'file_signed_scan' => ['mimes' => ['pdf', 'jpg', 'jpeg', 'png'], 'max_kb' => 5120],
    ],

    'statuses' => [
        'draft' => ['label' => 'Draft', 'stage' => 'submission', 'color' => '#64748b', 'editable' => true],
        'submitted' => ['label' => 'Diajukan', 'stage' => 'submission', 'color' => '#0ea5e9', 'editable' => false],
        'pending_approval' => ['label' => 'Menunggu Persetujuan', 'stage' => 'approval', 'color' => '#6366f1', 'editable' => false],
        'revision_required' => ['label' => 'Perlu Revisi', 'stage' => 'submission', 'color' => '#f59e0b', 'editable' => true],
        'approved' => ['label' => 'Disetujui', 'stage' => 'approval', 'color' => '#10b981', 'editable' => false],
        'rejected' => ['label' => 'Ditolak', 'stage' => 'approval', 'color' => '#ef4444', 'editable' => false, 'terminal' => true],
        'issued' => ['label' => 'Diterbitkan', 'stage' => 'issued', 'color' => '#059669', 'editable' => false, 'terminal' => true],
    ],

    'transitions' => [
        'draft' => ['submit' => 'submitted'],
        'revision_required' => ['submit' => 'submitted'],
        'submitted' => ['start_approval' => 'pending_approval', 'auto_approve' => 'approved'],
        'pending_approval' => [
            'approve' => 'approved',
            'reject' => 'rejected',
            'request_revision' => 'revision_required',
        ],
        'approved' => ['issue' => 'issued'],
    ],

    'letter_type_rules' => [
        'surat_tugas_penelitian' => [
            'applies_to' => 'research',
            'requires_proposal_link' => true,
            'requires_partner_link' => false,
            'allow_dosen_create' => true,
        ],
        'surat_tugas_pkm' => [
            'applies_to' => 'community_service',
            'requires_proposal_link' => true,
            'requires_partner_link' => false,
            'allow_dosen_create' => true,
        ],
        'surat_izin_penelitian' => [
            'applies_to' => 'research',
            'requires_proposal_link' => true,
            'requires_partner_link' => false,
            'allow_dosen_create' => true,
        ],
        'surat_izin_pkm' => [
            'applies_to' => 'community_service',
            'requires_proposal_link' => true,
            'requires_partner_link' => false,
            'allow_dosen_create' => true,
        ],
        'surat_permohonan_data' => [
            'applies_to' => 'general',
            'requires_proposal_link' => false,
            'requires_partner_link' => false,
            'allow_dosen_create' => true,
        ],
        'surat_pengantar_mitra' => [
            'applies_to' => 'partner',
            'requires_proposal_link' => false,
            'requires_partner_link' => true,
            'allow_dosen_create' => false,
        ],
        'surat_undangan_reviewer' => [
            'applies_to' => 'reviewer',
            'requires_proposal_link' => false,
            'requires_partner_link' => false,
            'allow_dosen_create' => false,
        ],
        'surat_undangan_seminar' => [
            'applies_to' => 'general',
            'requires_proposal_link' => false,
            'requires_partner_link' => false,
            'allow_dosen_create' => false,
        ],
        'surat_keterangan_selesai_penelitian' => [
            'applies_to' => 'research',
            'requires_proposal_link' => true,
            'requires_partner_link' => false,
            'allow_dosen_create' => false,
        ],
        'surat_keterangan_selesai_pkm' => [
            'applies_to' => 'community_service',
            'requires_proposal_link' => true,
            'requires_partner_link' => false,
            'allow_dosen_create' => false,
        ],
        'surat_keterangan_luaran' => [
            'applies_to' => 'publication',
            'requires_proposal_link' => false,
            'requires_partner_link' => false,
            'allow_dosen_create' => true,
        ],
    ],

];
