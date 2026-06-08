<?php

return [

    'categories' => [
        'workflow' => 'Workflow',
        'decision' => 'Keputusan',
        'assignment' => 'Penugasan',
        'revision' => 'Revisi',
        'system' => 'Sistem',
    ],

    'severities' => ['info', 'warning', 'urgent'],

    'admin_notify_roles' => ['super_admin', 'admin_lppm', 'ketua_lppm'],

    'types' => [
        'proposal_submitted_research' => [
            'category' => 'workflow',
            'severity' => 'info',
            'title' => 'Proposal penelitian baru diajukan',
        ],
        'proposal_submitted_pkm' => [
            'category' => 'workflow',
            'severity' => 'info',
            'title' => 'Proposal PkM baru diajukan',
        ],
        'proposal_revision_research' => [
            'category' => 'revision',
            'severity' => 'warning',
            'title' => 'Proposal penelitian perlu revisi',
        ],
        'proposal_revision_pkm' => [
            'category' => 'revision',
            'severity' => 'warning',
            'title' => 'Proposal PkM perlu revisi',
        ],
        'proposal_revision_ethics' => [
            'category' => 'revision',
            'severity' => 'warning',
            'title' => 'Aplikasi etik perlu revisi',
        ],
        'reviewer_assigned_research' => [
            'category' => 'assignment',
            'severity' => 'info',
            'title' => 'Penugasan review proposal penelitian',
        ],
        'reviewer_assigned_pkm' => [
            'category' => 'assignment',
            'severity' => 'info',
            'title' => 'Penugasan review proposal PkM',
        ],
        'reviewer_assigned_ethics' => [
            'category' => 'assignment',
            'severity' => 'info',
            'title' => 'Penugasan review etik penelitian',
        ],
        'proposal_decision_research' => [
            'category' => 'decision',
            'severity' => 'info',
            'title' => 'Keputusan proposal penelitian',
        ],
        'proposal_decision_pkm' => [
            'category' => 'decision',
            'severity' => 'info',
            'title' => 'Keputusan proposal PkM',
        ],
        'ethics_decision' => [
            'category' => 'decision',
            'severity' => 'info',
            'title' => 'Keputusan aplikasi etik',
        ],
    ],

];
