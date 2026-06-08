<?php

namespace Database\Seeders;

use App\Models\Lppm\CommunityServiceScheme;
use App\Models\Lppm\DocumentCategory;
use App\Models\Lppm\FocusArea;
use App\Models\Lppm\FundingSource;
use App\Models\Lppm\IpType;
use App\Models\Lppm\LetterType;
use App\Models\Lppm\OutputType;
use App\Models\Lppm\Partner;
use App\Models\Lppm\PartnerType;
use App\Models\Lppm\ProposalStatus;
use App\Models\Lppm\PublicationType;
use App\Models\Lppm\ResearchScheme;
use App\Models\Lppm\ScienceCluster;
use Illuminate\Database\Seeder;

class LppmMasterSeeder extends Seeder
{
    public function run(): void
    {
        $internal = FundingSource::query()->updateOrCreate(
            ['code' => 'internal_gs'],
            [
                'name' => 'Internal STIKES Gunung Sari',
                'source_category' => 'internal',
                'institution_name' => 'STIKES Gunung Sari',
                'sort_order' => 10,
                'is_active' => true,
            ],
        );

        FundingSource::query()->updateOrCreate(
            ['code' => 'kemdikti'],
            [
                'name' => 'Kemdiktiristek',
                'source_category' => 'external',
                'institution_name' => 'Kementerian Pendidikan Tinggi',
                'requires_contract' => true,
                'sort_order' => 20,
                'is_active' => true,
            ],
        );

        FocusArea::query()->updateOrCreate(
            ['code' => 'kesehatan_masyarakat'],
            ['name' => 'Kesehatan Masyarakat', 'color' => '#0f766e', 'sort_order' => 10, 'is_active' => true],
        );

        ScienceCluster::query()->updateOrCreate(
            ['code' => 'keperawatan'],
            ['name' => 'Keperawatan', 'level' => 1, 'sort_order' => 10, 'is_active' => true],
        );

        OutputType::query()->updateOrCreate(
            ['code' => 'jurnal_nasional'],
            ['name' => 'Jurnal Nasional Terakreditasi', 'applies_to' => 'research', 'unit_label' => 'artikel', 'sort_order' => 10, 'is_active' => true],
        );

        PartnerType::query()->updateOrCreate(
            ['code' => 'pemerintah_daerah'],
            ['name' => 'Pemerintah Daerah', 'requires_legal_document' => true, 'sort_order' => 10, 'is_active' => true],
        );

        $partnerType = PartnerType::query()->where('code', 'pemerintah_daerah')->first();

        if ($partnerType) {
            Partner::query()->updateOrCreate(
                ['partner_code' => 'MTR-001'],
                [
                    'name' => 'Puskesmas Gunung Sari',
                    'partner_type_id' => $partnerType->id,
                    'city' => 'Mataram',
                    'contact_person' => 'Kepala Puskesmas',
                    'is_active' => true,
                ],
            );

            Partner::query()->updateOrCreate(
                ['partner_code' => 'MTR-002'],
                [
                    'name' => 'Desa Bontokape',
                    'partner_type_id' => $partnerType->id,
                    'city' => 'Lombok Tengah',
                    'contact_person' => 'Kepala Desa',
                    'is_active' => true,
                ],
            );
        }

        DocumentCategory::query()->updateOrCreate(
            ['code' => 'proposal_penelitian'],
            ['name' => 'Proposal Penelitian', 'module_type' => 'research', 'is_required' => true, 'sort_order' => 10, 'is_active' => true],
        );

        DocumentCategory::query()->updateOrCreate(
            ['code' => 'proposal_pengabdian'],
            ['name' => 'Proposal Pengabdian Masyarakat', 'module_type' => 'community_service', 'is_required' => true, 'sort_order' => 20, 'is_active' => true],
        );

        DocumentCategory::query()->updateOrCreate(
            ['code' => 'laporan_penelitian'],
            ['name' => 'Laporan Penelitian', 'module_type' => 'research', 'is_required' => false, 'sort_order' => 30, 'is_active' => true],
        );

        DocumentCategory::query()->updateOrCreate(
            ['code' => 'laporan_pengabdian'],
            ['name' => 'Laporan Pengabdian', 'module_type' => 'community_service', 'is_required' => false, 'sort_order' => 40, 'is_active' => true],
        );

        DocumentCategory::query()->updateOrCreate(
            ['code' => 'publikasi'],
            ['name' => 'Dokumen Publikasi', 'module_type' => 'general', 'is_required' => false, 'sort_order' => 50, 'is_active' => true],
        );

        DocumentCategory::query()->updateOrCreate(
            ['code' => 'hki'],
            ['name' => 'Dokumen HKI', 'module_type' => 'general', 'is_required' => false, 'sort_order' => 60, 'is_active' => true],
        );

        DocumentCategory::query()->updateOrCreate(
            ['code' => 'etik_penelitian'],
            ['name' => 'Dokumen Etik Penelitian', 'module_type' => 'research', 'is_required' => false, 'sort_order' => 70, 'is_active' => true],
        );

        IpType::query()->updateOrCreate(
            ['code' => 'hak_cipta'],
            ['name' => 'Hak Cipta', 'registration_body' => 'Kemenkumham', 'sort_order' => 10, 'is_active' => true],
        );

        PublicationType::query()->updateOrCreate(
            ['code' => 'jurnal_internasional'],
            ['name' => 'Jurnal Internasional', 'indexing_type' => 'international', 'sort_order' => 10, 'is_active' => true],
        );

        $statuses = [
            ['code' => 'draft', 'name' => 'Draft', 'proposal_type' => 'both', 'stage' => 'submission', 'color' => '#64748b', 'is_editable_by_proposer' => true, 'sort_order' => 10],
            ['code' => 'submitted', 'name' => 'Diajukan', 'proposal_type' => 'both', 'stage' => 'submission', 'color' => '#0ea5e9', 'sort_order' => 20],
            ['code' => 'review', 'name' => 'Dalam Review', 'proposal_type' => 'both', 'stage' => 'review', 'color' => '#f59e0b', 'sort_order' => 30],
            ['code' => 'approved', 'name' => 'Disetujui', 'proposal_type' => 'both', 'stage' => 'decision', 'color' => '#10b981', 'is_terminal' => true, 'sort_order' => 40],
            ['code' => 'rejected', 'name' => 'Ditolak', 'proposal_type' => 'both', 'stage' => 'decision', 'color' => '#ef4444', 'is_terminal' => true, 'sort_order' => 50],
        ];

        foreach ($statuses as $status) {
            ProposalStatus::query()->updateOrCreate(
                ['code' => $status['code']],
                array_merge($status, ['is_active' => true]),
            );
        }

        $researchScheme = ResearchScheme::query()->updateOrCreate(
            ['code' => 'penelitian_dosen_tahunan'],
            [
                'name' => 'Penelitian Dosen Tahunan',
                'academic_year_label' => '2025/2026',
                'max_budget' => 15000000,
                'min_team_members' => 1,
                'max_team_members' => 5,
                'requires_ethics_approval' => true,
                'sort_order' => 10,
                'is_active' => true,
            ],
        );
        $researchScheme->fundingSources()->sync([$internal->id]);

        $kemdikti = FundingSource::query()->where('code', 'kemdikti')->first();

        $schemeMahasiswa = ResearchScheme::query()->updateOrCreate(
            ['code' => 'penelitian_mahasiswa'],
            [
                'name' => 'Penelitian Mahasiswa',
                'academic_year_label' => '2025/2026',
                'max_budget' => 5000000,
                'min_team_members' => 2,
                'max_team_members' => 5,
                'requires_ethics_approval' => true,
                'sort_order' => 20,
                'is_active' => true,
            ],
        );
        $schemeMahasiswa->fundingSources()->sync([$internal->id]);

        $schemeKolaboratif = ResearchScheme::query()->updateOrCreate(
            ['code' => 'penelitian_kolaboratif'],
            [
                'name' => 'Penelitian Kolaboratif Prodi',
                'academic_year_label' => '2025/2026',
                'max_budget' => 25000000,
                'min_team_members' => 2,
                'max_team_members' => 8,
                'requires_ethics_approval' => true,
                'sort_order' => 30,
                'is_active' => true,
            ],
        );
        $schemeKolaboratif->fundingSources()->sync([$internal->id]);

        if ($kemdikti) {
            $schemeHibah = ResearchScheme::query()->updateOrCreate(
                ['code' => 'penelitian_hibah_kemdikti'],
                [
                    'name' => 'Penelitian Hibah Kemdiktiristek',
                    'academic_year_label' => '2025/2026',
                    'max_budget' => 100000000,
                    'min_team_members' => 1,
                    'max_team_members' => 10,
                    'requires_ethics_approval' => true,
                    'sort_order' => 40,
                    'is_active' => true,
                ],
            );
            $schemeHibah->fundingSources()->sync([$kemdikti->id]);
        }

        $pkmScheme = CommunityServiceScheme::query()->updateOrCreate(
            ['code' => 'pkm_masyarakat'],
            [
                'name' => 'Pengabdian Masyarakat Berbasis Masyarakat',
                'academic_year_label' => '2025/2026',
                'max_budget' => 10000000,
                'requires_partner' => true,
                'sort_order' => 10,
                'is_active' => true,
            ],
        );
        $pkmScheme->fundingSources()->sync([$internal->id]);
    }
}
