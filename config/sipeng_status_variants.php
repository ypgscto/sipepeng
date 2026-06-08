<?php

return [

    /*
    | Pemetaan kode status ke variant badge (formal & konsisten).
    */
    'map' => [
        'draft' => 'draft',
        'submitted' => 'submitted',
        'admin_pending' => 'review',
        'review_assigned' => 'review',
        'review_completed' => 'review',
        'revision_required' => 'revision',
        'admin_verified' => 'approved',
        'verified' => 'approved',
        'approved' => 'approved',
        'issued' => 'approved',
        'published' => 'approved',
        'active' => 'approved',
        'admin_rejected' => 'rejected',
        'rejected' => 'rejected',
        'cancelled' => 'rejected',
        'pending' => 'submitted',
        'under_review' => 'review',
        'committee_review' => 'review',
    ],

    'default' => 'neutral',

];
