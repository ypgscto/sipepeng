<?php

namespace App\Services\Report;

use App\DataTransferObjects\Report\ReportFilterDto;
use App\Models\CommunityService\CommunityServiceProposal;
use App\Models\IntellectualProperty\IpRegistration;
use App\Models\Lppm\Partner;
use App\Models\Publication\Publication;
use App\Models\Report\ProposalTeamMember;
use App\Models\Research\ResearchProposal;
use App\Models\ResearchEthics\ResearchEthicsApplication;

class DashboardStatisticsService
{
    public function __construct(protected ReportFilterService $filters) {}

    /**
     * @return array<string, mixed>
     */
    public function build(ReportFilterDto $filter): array
    {
        $researchQ = $this->filters->applyToProposalQuery(ResearchProposal::query(), $filter);
        $pkmQ = $this->filters->applyToProposalQuery(CommunityServiceProposal::query(), $filter);

        $researchApproved = (clone $researchQ)->whereIn('status', config('sipepeng_reports.approved_statuses.research', []))->count();
        $pkmApproved = (clone $pkmQ)->whereIn('status', config('sipepeng_reports.approved_statuses.pkm', []))->count();

        $researchSubmitted = (clone $researchQ)->whereIn('status', config('sipepeng_reports.submitted_statuses.research', []))->count();
        $pkmSubmitted = (clone $pkmQ)->whereIn('status', config('sipepeng_reports.submitted_statuses.pkm', []))->count();

        $researchRevision = (clone $researchQ)->whereIn('status', config('sipepeng_reports.revision_statuses.research', []))->count();
        $pkmRevision = (clone $pkmQ)->whereIn('status', config('sipepeng_reports.revision_statuses.pkm', []))->count();

        $researchRejected = (clone $researchQ)->whereIn('status', config('sipepeng_reports.rejected_statuses.research', []))->count();
        $pkmRejected = (clone $pkmQ)->whereIn('status', config('sipepeng_reports.rejected_statuses.pkm', []))->count();

        $pubQ = $this->filters->applyToProdiRecordQuery(Publication::query(), $filter, 'publication_year');
        $hkiQ = $this->filters->applyToProdiRecordQuery(IpRegistration::query(), $filter);

        $activePartners = Partner::query()->where('is_active', true)
            ->whereHas('communityServiceProposals', function ($q) use ($filter): void {
                $this->filters->applyToProposalQuery($q, $filter);
            })->count();

        $lecturersResearch = (clone $researchQ)->whereNotIn('status', ['draft'])
            ->distinct('ketua_dosen_id')->count('ketua_dosen_id');
        $lecturersPkm = (clone $pkmQ)->whereNotIn('status', ['draft'])
            ->distinct('ketua_dosen_id')->count('ketua_dosen_id');

        $studentsQ = ProposalTeamMember::query()->where('member_type', 'mahasiswa');
        if ($filter->prodiId) {
            $studentsQ->where('prodi_id', $filter->prodiId);
        }

        $ethicsQ = $this->filters->applyToProdiRecordQuery(ResearchEthicsApplication::query(), $filter);

        return [
            'research_total' => (clone $researchQ)->count(),
            'pkm_total' => (clone $pkmQ)->count(),
            'research_approved_count' => $researchApproved,
            'pkm_approved_count' => $pkmApproved,
            'proposals_submitted' => $researchSubmitted + $pkmSubmitted,
            'proposals_approved' => $researchApproved + $pkmApproved,
            'proposals_revision' => $researchRevision + $pkmRevision,
            'proposals_rejected' => $researchRejected + $pkmRejected,
            'publications_count' => (clone $pubQ)->whereIn('status', config('sipepeng_reports.publication_count_statuses', []))->count(),
            'hki_count' => (clone $hkiQ)->whereIn('status', config('sipepeng_reports.hki_count_statuses', []))->count(),
            'ethics_count' => (clone $ethicsQ)->whereIn('status', config('sipepeng_reports.ethics_count_statuses', []))->count(),
            'active_partners' => $activePartners > 0 ? $activePartners : Partner::query()->where('is_active', true)->count(),
            'active_lecturers' => max($lecturersResearch, $lecturersPkm) === 0
                ? (clone $researchQ)->distinct('ketua_dosen_id')->count('ketua_dosen_id') + (clone $pkmQ)->distinct('ketua_dosen_id')->count('ketua_dosen_id')
                : $lecturersResearch + $lecturersPkm,
            'students_involved' => (clone $studentsQ)->distinct('mahasiswa_id')->count('mahasiswa_id'),
            'research_funding_total' => (float) (clone $researchQ)->sum('total_rab'),
            'pkm_funding_total' => (float) (clone $pkmQ)->sum('total_rab'),
        ];
    }

    /**
     * @return list<array{key: string, label: string, value: string, icon: string, tone: string}>
     */
    public function statCards(ReportFilterDto $filter): array
    {
        $s = $this->build($filter);

        return [
            ['key' => 'research_total', 'label' => 'Penelitian Tahun Berjalan', 'value' => (string) $s['research_total'], 'icon' => 'research', 'tone' => 'emerald'],
            ['key' => 'pkm_total', 'label' => 'PkM Tahun Berjalan', 'value' => (string) $s['pkm_total'], 'icon' => 'community', 'tone' => 'teal'],
            ['key' => 'proposals_submitted', 'label' => 'Proposal Masuk', 'value' => (string) $s['proposals_submitted'], 'icon' => 'document', 'tone' => 'sky'],
            ['key' => 'proposals_approved', 'label' => 'Proposal Disetujui', 'value' => (string) $s['proposals_approved'], 'icon' => 'research', 'tone' => 'emerald'],
            ['key' => 'proposals_revision', 'label' => 'Proposal Revisi', 'value' => (string) $s['proposals_revision'], 'icon' => 'document', 'tone' => 'amber'],
            ['key' => 'proposals_rejected', 'label' => 'Proposal Ditolak', 'value' => (string) $s['proposals_rejected'], 'icon' => 'document', 'tone' => 'slate'],
            ['key' => 'publications_count', 'label' => 'Publikasi', 'value' => (string) $s['publications_count'], 'icon' => 'document', 'tone' => 'indigo'],
            ['key' => 'hki_count', 'label' => 'HKI', 'value' => (string) $s['hki_count'], 'icon' => 'certificate', 'tone' => 'indigo'],
            ['key' => 'active_partners', 'label' => 'Mitra Aktif', 'value' => (string) $s['active_partners'], 'icon' => 'handshake', 'tone' => 'teal'],
            ['key' => 'active_lecturers', 'label' => 'Dosen Aktif Meneliti', 'value' => (string) $s['active_lecturers'], 'icon' => 'document', 'tone' => 'emerald'],
            ['key' => 'students_involved', 'label' => 'Mahasiswa Terlibat', 'value' => (string) $s['students_involved'], 'icon' => 'document', 'tone' => 'sky'],
            ['key' => 'research_funding_total', 'label' => 'Dana Penelitian (RAB)', 'value' => 'Rp '.number_format($s['research_funding_total'], 0, ',', '.'), 'icon' => 'chart', 'tone' => 'amber'],
            ['key' => 'pkm_funding_total', 'label' => 'Dana PkM (RAB)', 'value' => 'Rp '.number_format($s['pkm_funding_total'], 0, ',', '.'), 'icon' => 'chart', 'tone' => 'amber'],
        ];
    }
}
