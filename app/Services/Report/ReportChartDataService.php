<?php

namespace App\Services\Report;

use App\DataTransferObjects\Report\ReportFilterDto;
use App\Models\CommunityService\CommunityServiceProposal;
use App\Models\IntellectualProperty\IpRegistration;
use App\Models\Publication\Publication;
use App\Models\Research\ResearchProposal;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportChartDataService
{
    public function __construct(protected ReportFilterService $filters) {}

    /**
     * @return array<string, mixed>
     */
    public function all(ReportFilterDto $filter): array
    {
        return [
            'research_by_prodi' => $this->researchByProdi($filter),
            'pkm_by_prodi' => $this->pkmByProdi($filter),
            'publication_by_year' => $this->publicationByYear($filter),
            'output_by_type' => $this->outputByType($filter),
            'funding_by_category' => $this->fundingByCategory($filter),
        ];
    }

    /**
     * @return list<array{label: string, value: int}>
     */
    public function researchByProdi(ReportFilterDto $filter): array
    {
        return $this->groupByProdi(
            $this->filters->applyToProposalQuery(ResearchProposal::query(), $filter),
        );
    }

    /**
     * @return list<array{label: string, value: int}>
     */
    public function pkmByProdi(ReportFilterDto $filter): array
    {
        return $this->groupByProdi(
            $this->filters->applyToProposalQuery(CommunityServiceProposal::query(), $filter),
        );
    }

    /**
     * @return list<array{label: string, value: int}>
     */
    public function publicationByYear(ReportFilterDto $filter): array
    {
        $rows = $this->filters->applyToProdiRecordQuery(Publication::query(), $filter, 'publication_year')
            ->whereIn('status', config('sipepeng_reports.publication_count_statuses', []))
            ->select('publication_year', DB::raw('count(*) as total'))
            ->whereNotNull('publication_year')
            ->groupBy('publication_year')
            ->orderBy('publication_year')
            ->get();

        return $rows->map(fn ($r) => ['label' => (string) $r->publication_year, 'value' => (int) $r->total])->all();
    }

    /**
     * @return list<array{label: string, value: int}>
     */
    public function outputByType(ReportFilterDto $filter): array
    {
        $pubs = $this->filters->applyToProdiRecordQuery(
            Publication::query()->join('lppm_publication_types as pt', 'pt.id', '=', 'lppm_publications.publication_type_id'),
            $filter,
            'publication_year',
        )
            ->select('pt.name as label', DB::raw('count(*) as total'))
            ->groupBy('pt.name')
            ->get()
            ->map(fn ($r) => ['label' => 'Publikasi: '.$r->label, 'value' => (int) $r->total]);

        $hkis = $this->filters->applyToProdiRecordQuery(
            IpRegistration::query()->join('lppm_ip_types as it', 'it.id', '=', 'lppm_ip_registrations.ip_type_id'),
            $filter,
        )
            ->select('it.name as label', DB::raw('count(*) as total'))
            ->groupBy('it.name')
            ->get()
            ->map(fn ($r) => ['label' => 'HKI: '.$r->label, 'value' => (int) $r->total]);

        return $pubs->concat($hkis)->values()->all();
    }

    /**
     * @return list<array{label: string, value: float, category: string}>
     */
    public function fundingByCategory(ReportFilterDto $filter): array
    {
        $rows = DB::table('lppm_funding_sources')
            ->select('name as label', 'source_category as category', DB::raw('0 as value'))
            ->get();

        $researchRab = (float) $this->filters->applyToProposalQuery(ResearchProposal::query(), $filter)->sum('total_rab');
        $pkmRab = (float) $this->filters->applyToProposalQuery(CommunityServiceProposal::query(), $filter)->sum('total_rab');
        $total = $researchRab + $pkmRab;

        if ($rows->isEmpty()) {
            return [
                ['label' => 'Internal (estimasi RAB)', 'value' => $total * 0.6, 'category' => 'internal'],
                ['label' => 'Eksternal (estimasi RAB)', 'value' => $total * 0.4, 'category' => 'external'],
            ];
        }

        return $rows->map(function ($r) use ($total) {
            $share = $total / max(1, DB::table('lppm_funding_sources')->count());

            return ['label' => $r->label, 'value' => $share, 'category' => $r->category];
        })->all();
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @return list<array{label: string, value: int}>
     */
    protected function groupByProdi($query): array
    {
        return $query
            ->select('prodi_nama_snapshot as label', DB::raw('count(*) as value'))
            ->groupBy('prodi_id', 'prodi_nama_snapshot')
            ->orderByDesc('value')
            ->limit(12)
            ->get()
            ->map(fn ($r) => ['label' => (string) $r->label, 'value' => (int) $r->value])
            ->all();
    }
}
