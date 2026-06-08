<?php

use App\Models\Lppm\CommunityServiceScheme;
use App\Models\Lppm\DocumentCategory;
use App\Models\Lppm\DocumentTemplate;
use App\Models\Lppm\FocusArea;
use App\Models\Lppm\FundingSource;
use App\Models\Lppm\IpType;
use App\Models\Lppm\LetterType;
use App\Models\Lppm\OutputType;
use App\Models\Lppm\PartnerType;
use App\Models\Lppm\ProposalStatus;
use App\Models\Lppm\PublicationType;
use App\Models\Lppm\ResearchScheme;
use App\Models\Lppm\Reviewer;
use App\Models\Lppm\ScienceCluster;

return [

    'manage_roles' => ['super_admin', 'admin_lppm'],

    'view_roles' => ['super_admin', 'admin_lppm', 'ketua_lppm'],

    'entities' => [
        'research-schemes' => [
            'label' => 'Skema Penelitian',
            'model' => ResearchScheme::class,
            'form' => 'research-scheme',
            'search' => ['code', 'name', 'academic_year_label'],
            'sort' => ['sort_order', 'name'],
        ],
        'community-service-schemes' => [
            'label' => 'Skema Pengabdian',
            'model' => CommunityServiceScheme::class,
            'form' => 'community-service-scheme',
            'search' => ['code', 'name', 'academic_year_label'],
            'sort' => ['sort_order', 'name'],
        ],
        'output-types' => [
            'label' => 'Jenis Luaran',
            'model' => OutputType::class,
            'form' => 'output-type',
            'search' => ['code', 'name'],
            'sort' => ['sort_order', 'name'],
        ],
        'funding-sources' => [
            'label' => 'Sumber Dana',
            'model' => FundingSource::class,
            'form' => 'funding-source',
            'search' => ['code', 'name', 'institution_name'],
            'sort' => ['sort_order', 'name'],
        ],
        'focus-areas' => [
            'label' => 'Bidang Fokus',
            'model' => FocusArea::class,
            'form' => 'focus-area',
            'search' => ['code', 'name'],
            'sort' => ['sort_order', 'name'],
        ],
        'science-clusters' => [
            'label' => 'Rumpun Ilmu',
            'model' => ScienceCluster::class,
            'form' => 'science-cluster',
            'search' => ['code', 'name', 'feeder_code'],
            'sort' => ['sort_order', 'name'],
        ],
        'partner-types' => [
            'label' => 'Jenis Mitra',
            'model' => PartnerType::class,
            'form' => 'partner-type',
            'search' => ['code', 'name'],
            'sort' => ['sort_order', 'name'],
        ],
        'document-templates' => [
            'label' => 'Template Dokumen',
            'model' => DocumentTemplate::class,
            'form' => 'document-template',
            'search' => ['template_code', 'name', 'file_name'],
            'code_column' => 'template_code',
            'sort' => ['sort_order', 'name'],
        ],
        'reviewers' => [
            'label' => 'Reviewer',
            'model' => Reviewer::class,
            'form' => 'reviewer',
            'search' => [],
            'has_code' => false,
            'sort' => ['id'],
        ],
        'document-categories' => [
            'label' => 'Kategori Dokumen',
            'model' => DocumentCategory::class,
            'form' => 'document-category',
            'search' => ['code', 'name'],
            'sort' => ['sort_order', 'name'],
        ],
        'ip-types' => [
            'label' => 'Jenis HKI',
            'model' => IpType::class,
            'form' => 'ip-type',
            'search' => ['code', 'name'],
            'sort' => ['sort_order', 'name'],
        ],
        'publication-types' => [
            'label' => 'Jenis Publikasi',
            'model' => PublicationType::class,
            'form' => 'publication-type',
            'search' => ['code', 'name', 'feeder_code'],
            'sort' => ['sort_order', 'name'],
        ],
        'letter-types' => [
            'label' => 'Jenis Surat',
            'model' => LetterType::class,
            'form' => 'letter-type',
            'search' => ['code', 'name', 'letter_prefix'],
            'sort' => ['sort_order', 'name'],
        ],
        'proposal-statuses' => [
            'label' => 'Status Proposal',
            'model' => ProposalStatus::class,
            'form' => 'proposal-status',
            'search' => ['code', 'name'],
            'sort' => ['sort_order', 'name'],
        ],
    ],

];
