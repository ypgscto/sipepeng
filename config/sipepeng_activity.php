<?php

return [

    'log_names' => [
        'lppm_master',
        'lppm_research',
        'lppm_pkm',
        'lppm_publication',
        'lppm_hki',
        'lppm_ethics',
        'lppm_letter',
        'lppm_report',
        'siakad_reference',
        'security',
        'lppm_notification',
    ],

    'sensitive_fields' => [
        'password',
        'remember_token',
        'token',
        'api_token',
        'api_token_new',
    ],

    'crud_events' => ['created', 'updated', 'deleted', 'restored'],

    'workflow_events' => [
        'submitted',
        'status_changed',
        'revision_requested',
        'admin_verification',
        'reviewer_assigned',
        'review_submitted',
        'funding_decided',
        'decision',
    ],

    'security_events' => [
        'login_success',
        'login_failed',
        'logout',
        'report_exported',
        'reference_refreshed',
        'settings_updated',
        'logo_uploaded',
        'backup_created',
        'backup_downloaded',
    ],

];
