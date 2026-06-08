<?php

return [

    'view_all_roles' => ['super_admin', 'admin_lppm', 'ketua_lppm', 'pimpinan'],

    'manage_roles' => ['super_admin', 'admin_lppm'],

    'proposer_roles' => ['dosen', 'super_admin', 'admin_lppm'],

    'storage_disk' => env('HKI_STORAGE_DISK', 'local'),

    'storage_path' => 'lppm/hki',

    'uploads' => [
        'file_application' => ['mimes' => ['pdf'], 'max_kb' => 10240],
        'file_statement' => ['mimes' => ['pdf'], 'max_kb' => 5120],
        'file_certificate' => ['mimes' => ['pdf'], 'max_kb' => 10240],
        'file_supporting' => ['mimes' => ['pdf'], 'max_kb' => 15360],
    ],

    'linkable_proposal_statuses' => ['approved', 'review_completed', 'admin_verified'],

    'statuses' => [
        'draft' => ['label' => 'Draft', 'stage' => 'submission', 'color' => '#64748b', 'editable' => true],
        'submitted' => ['label' => 'Diajukan', 'stage' => 'submission', 'color' => '#0ea5e9', 'editable' => false],
        'revision_required' => ['label' => 'Perlu Revisi', 'stage' => 'submission', 'color' => '#f59e0b', 'editable' => true],
        'admin_pending' => ['label' => 'Menunggu Verifikasi', 'stage' => 'verification', 'color' => '#6366f1', 'editable' => false],
        'verified' => ['label' => 'Terverifikasi', 'stage' => 'verification', 'color' => '#10b981', 'editable' => false],
        'rejected' => ['label' => 'Ditolak', 'stage' => 'verification', 'color' => '#ef4444', 'editable' => false, 'terminal' => true],
        'registered' => ['label' => 'Terdaftar', 'stage' => 'registered', 'color' => '#059669', 'editable' => false, 'terminal' => true],
    ],

    'transitions' => [
        'draft' => ['submit' => 'submitted'],
        'revision_required' => ['submit' => 'submitted'],
        'submitted' => ['start_admin_review' => 'admin_pending'],
        'admin_pending' => [
            'verify' => 'verified',
            'reject' => 'rejected',
            'request_revision' => 'revision_required',
        ],
        'verified' => ['confirm_registered' => 'registered'],
    ],

];
