<?php

return [

    'view_all_roles' => ['super_admin', 'admin_lppm', 'ketua_lppm', 'pimpinan'],

    'manage_roles' => ['super_admin', 'admin_lppm'],

    'proposer_roles' => ['dosen', 'super_admin', 'admin_lppm'],

    'decision_roles' => ['super_admin', 'admin_lppm', 'ketua_lppm'],

    'storage_disk' => env('ETHICS_STORAGE_DISK', 'local'),

    'storage_path' => 'lppm/ethics',

    'uploads' => [
        'file_protocol' => ['mimes' => ['pdf'], 'max_kb' => 15360],
        'file_ethics_application' => ['mimes' => ['pdf'], 'max_kb' => 10240],
        'file_consent_form' => ['mimes' => ['pdf'], 'max_kb' => 10240],
        'file_approval_letter' => ['mimes' => ['pdf'], 'max_kb' => 5120],
    ],

    'statuses' => [
        'draft' => ['label' => 'Draft', 'stage' => 'submission', 'color' => '#64748b', 'editable' => true],
        'submitted' => ['label' => 'Diajukan', 'stage' => 'submission', 'color' => '#0ea5e9', 'editable' => false],
        'revision_required' => ['label' => 'Perlu Revisi', 'stage' => 'submission', 'color' => '#f59e0b', 'editable' => true],
        'committee_review' => ['label' => 'Review Komite', 'stage' => 'review', 'color' => '#8b5cf6', 'editable' => false],
        'approved' => ['label' => 'Disetujui', 'stage' => 'decision', 'color' => '#059669', 'editable' => false, 'terminal' => true],
        'rejected' => ['label' => 'Ditolak', 'stage' => 'decision', 'color' => '#dc2626', 'editable' => false, 'terminal' => true],
        'expired' => ['label' => 'Kedaluwarsa', 'stage' => 'decision', 'color' => '#94a3b8', 'editable' => false, 'terminal' => true],
    ],

    'transitions' => [
        'draft' => ['submit' => 'submitted'],
        'revision_required' => ['submit' => 'submitted'],
        'submitted' => ['start_committee_review' => 'committee_review'],
        'committee_review' => [
            'approve' => 'approved',
            'reject' => 'rejected',
            'request_revision' => 'revision_required',
        ],
    ],

];
