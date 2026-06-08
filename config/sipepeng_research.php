<?php

return [

    'view_all_roles' => ['super_admin', 'admin_lppm', 'ketua_lppm'],

    'manage_roles' => ['super_admin', 'admin_lppm'],

    'proposer_roles' => ['dosen', 'super_admin', 'admin_lppm'],

    'reviewer_roles' => ['reviewer'],

    'decision_roles' => ['super_admin', 'admin_lppm', 'ketua_lppm'],

    'storage_disk' => env('RESEARCH_STORAGE_DISK', 'local'),

    'storage_path' => 'lppm/research',

    'uploads' => [
        'file_proposal' => ['mimes' => ['pdf'], 'max_kb' => 10240],
        'file_pengesahan' => ['mimes' => ['pdf'], 'max_kb' => 5120],
        'file_pernyataan' => ['mimes' => ['pdf'], 'max_kb' => 5120],
    ],

    'statuses' => [
        'draft' => ['label' => 'Draft', 'stage' => 'submission', 'color' => '#64748b', 'editable' => true],
        'submitted' => ['label' => 'Diajukan', 'stage' => 'submission', 'color' => '#0ea5e9', 'editable' => false],
        'revision_required' => ['label' => 'Perlu Revisi', 'stage' => 'submission', 'color' => '#f59e0b', 'editable' => true],
        'admin_pending' => ['label' => 'Menunggu Verifikasi Admin', 'stage' => 'admin_review', 'color' => '#6366f1', 'editable' => false],
        'admin_verified' => ['label' => 'Terverifikasi Admin', 'stage' => 'admin_review', 'color' => '#10b981', 'editable' => false],
        'admin_rejected' => ['label' => 'Ditolak Admin', 'stage' => 'admin_review', 'color' => '#ef4444', 'editable' => false, 'terminal' => true],
        'review_assigned' => ['label' => 'Review Ditugaskan', 'stage' => 'peer_review', 'color' => '#8b5cf6', 'editable' => false],
        'review_completed' => ['label' => 'Review Selesai', 'stage' => 'peer_review', 'color' => '#0d9488', 'editable' => false],
        'approved' => ['label' => 'Disetujui', 'stage' => 'assignment', 'color' => '#059669', 'editable' => false, 'terminal' => true],
        'rejected' => ['label' => 'Ditolak', 'stage' => 'assignment', 'color' => '#dc2626', 'editable' => false, 'terminal' => true],
    ],

    'transitions' => [
        'draft' => ['submit' => 'submitted'],
        'revision_required' => ['submit' => 'submitted'],
        'submitted' => ['start_admin_review' => 'admin_pending'],
        'admin_pending' => [
            'verify' => 'admin_verified',
            'reject' => 'admin_rejected',
            'request_revision' => 'revision_required',
        ],
        'admin_verified' => ['assign_review' => 'review_assigned'],
        'review_assigned' => ['complete_review' => 'review_completed'],
        'review_completed' => [
            'approve' => 'approved',
            'reject' => 'rejected',
        ],
    ],

];
