<?php

namespace App\Services\Public;

use App\DataTransferObjects\Public\PublicDashboardFilterDto;
use App\Models\CommunityService\CommunityServiceProposal;
use App\Models\IntellectualProperty\IpRegistration;
use App\Models\Publication\Publication;
use App\Models\Research\ResearchProposal;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PublicDashboardService
{
    public function resolveYear(?int $year): int
    {
        $current = (int) now()->format('Y');
        $min = $current - (int) config('sipepeng_public_dashboard.allowed_years_back', 10);

        if ($year === null || $year < $min || $year > $current) {
            return $current;
        }

        return $year;
    }

    public function filterFromRequest(?int $year): PublicDashboardFilterDto
    {
        return new PublicDashboardFilterDto(
            year: $this->resolveYear($year),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(PublicDashboardFilterDto $filter): array
    {
        $cacheKey = 'public_dashboard:'.$filter->year;
        $ttl = (int) config('sipepeng_public_dashboard.cache_ttl_seconds', 900);

        return Cache::remember($cacheKey, $ttl, fn (): array => $this->buildPayload($filter));
    }

    /**
     * @return list<array{key: string, label: string, value: string, icon: string, tone: string}>
     */
    public function highlightStats(PublicDashboardFilterDto $filter): array
    {
        $stats = $this->payload($filter)['stats'];

        return array_values(array_filter($stats, fn (array $s): bool => in_array($s['key'], [
            'research_total',
            'pkm_total',
            'publications_total',
            'hki_total',
        ], true)));
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildPayload(PublicDashboardFilterDto $filter): array
    {
        $researchStatuses = config('sipepeng_public_dashboard.statuses.research', ['approved']);
        $pkmStatuses = config('sipepeng_public_dashboard.statuses.pkm', ['approved']);
        $pubStatuses = config('sipepeng_public_dashboard.statuses.publication', ['verified', 'published_confirmed']);
        $hkiStatuses = config('sipepeng_public_dashboard.statuses.hki', ['verified', 'registered', 'granted', 'approved']);

        $researchTotal = $this->countResearchForYear($filter->year, $researchStatuses);
        $pkmTotal = $this->countPkmForYear($filter->year, $pkmStatuses);
        $publicationsTotal = $this->countPublicationsForYear($filter->year, $pubStatuses);
        $hkiTotal = $this->countHkiForYear($filter->year, $hkiStatuses);
        $outputsTotal = $publicationsTotal + $hkiTotal;
        $activitiesCompleted = $researchTotal + $pkmTotal;

        return [
            'stats' => [
                ['key' => 'research_total', 'label' => 'Total Penelitian', 'value' => (string) $researchTotal, 'icon' => 'research', 'tone' => 'emerald'],
                ['key' => 'pkm_total', 'label' => 'Total Pengabdian Masyarakat', 'value' => (string) $pkmTotal, 'icon' => 'community', 'tone' => 'teal'],
                ['key' => 'publications_total', 'label' => 'Total Publikasi', 'value' => (string) $publicationsTotal, 'icon' => 'document', 'tone' => 'indigo'],
                ['key' => 'hki_total', 'label' => 'Total HKI', 'value' => (string) $hkiTotal, 'icon' => 'certificate', 'tone' => 'indigo'],
                ['key' => 'outputs_total', 'label' => 'Total Luaran', 'value' => (string) $outputsTotal, 'icon' => 'document', 'tone' => 'sky'],
                ['key' => 'activities_completed', 'label' => 'Total Kegiatan Selesai', 'value' => (string) $activitiesCompleted, 'icon' => 'research', 'tone' => 'emerald'],
            ],
            'charts' => [
                'research_by_year' => $this->researchByYear($researchStatuses),
                'pkm_by_year' => $this->pkmByYear($pkmStatuses),
                'publications_by_year' => $this->publicationsByYear($pubStatuses),
                'outputs_by_type' => $this->outputsByType($filter->year, $pubStatuses, $hkiStatuses),
            ],
            'filter' => $filter,
        ];
    }

    /**
     * @param  list<string>  $statuses
     */
    protected function countResearchForYear(int $year, array $statuses): int
    {
        return ResearchProposal::query()
            ->whereIn('status', $statuses)
            ->where(function ($q) use ($year): void {
                $q->whereYear('submitted_at', $year)
                    ->orWhereYear('created_at', $year);
            })
            ->count();
    }

    /**
     * @param  list<string>  $statuses
     */
    protected function countPkmForYear(int $year, array $statuses): int
    {
        return CommunityServiceProposal::query()
            ->whereIn('status', $statuses)
            ->where(function ($q) use ($year): void {
                $q->whereYear('submitted_at', $year)
                    ->orWhereYear('created_at', $year);
            })
            ->count();
    }

    /**
     * @param  list<string>  $statuses
     */
    protected function countPublicationsForYear(int $year, array $statuses): int
    {
        return Publication::query()
            ->whereIn('status', $statuses)
            ->where(function ($q) use ($year): void {
                $q->where('publication_year', $year)
                    ->orWhereYear('submitted_at', $year)
                    ->orWhereYear('created_at', $year);
            })
            ->count();
    }

    /**
     * @param  list<string>  $statuses
     */
    protected function countHkiForYear(int $year, array $statuses): int
    {
        return IpRegistration::query()
            ->whereIn('status', $statuses)
            ->where(function ($q) use ($year): void {
                $q->whereYear('submitted_at', $year)
                    ->orWhereYear('created_at', $year);
            })
            ->count();
    }

    /**
     * @param  list<string>  $statuses
     * @return list<array{label: string, value: int}>
     */
    protected function researchByYear(array $statuses): array
    {
        return collect($this->yearRange())->map(fn (int $year): array => [
            'label' => (string) $year,
            'value' => $this->countResearchForYear($year, $statuses),
        ])->all();
    }

    /**
     * @param  list<string>  $statuses
     * @return list<array{label: string, value: int}>
     */
    protected function pkmByYear(array $statuses): array
    {
        return collect($this->yearRange())->map(fn (int $year): array => [
            'label' => (string) $year,
            'value' => $this->countPkmForYear($year, $statuses),
        ])->all();
    }

    /**
     * @param  list<string>  $statuses
     * @return list<array{label: string, value: int}>
     */
    protected function publicationsByYear(array $statuses): array
    {
        $rows = Publication::query()
            ->whereIn('status', $statuses)
            ->whereNotNull('publication_year')
            ->select('publication_year', DB::raw('count(*) as total'))
            ->groupBy('publication_year')
            ->orderBy('publication_year')
            ->get();

        $byYear = $rows->mapWithKeys(fn ($r) => [(int) $r->publication_year => (int) $r->total]);

        return collect($this->yearRange())->map(fn (int $year): array => [
            'label' => (string) $year,
            'value' => (int) ($byYear[$year] ?? 0),
        ])->all();
    }

    /**
     * @param  list<string>  $pubStatuses
     * @param  list<string>  $hkiStatuses
     * @return list<array{label: string, value: int}>
     */
    protected function outputsByType(int $year, array $pubStatuses, array $hkiStatuses): array
    {
        $pubs = Publication::query()
            ->join('lppm_publication_types as pt', 'pt.id', '=', 'lppm_publications.publication_type_id')
            ->whereIn('lppm_publications.status', $pubStatuses)
            ->where(function ($q) use ($year): void {
                $q->where('lppm_publications.publication_year', $year)
                    ->orWhereYear('lppm_publications.submitted_at', $year);
            })
            ->select('pt.name as label', DB::raw('count(*) as total'))
            ->groupBy('pt.name')
            ->get()
            ->map(fn ($r) => ['label' => 'Publikasi: '.$r->label, 'value' => (int) $r->total]);

        $hkis = IpRegistration::query()
            ->join('lppm_ip_types as it', 'it.id', '=', 'lppm_ip_registrations.ip_type_id')
            ->whereIn('lppm_ip_registrations.status', $hkiStatuses)
            ->where(function ($q) use ($year): void {
                $q->whereYear('lppm_ip_registrations.submitted_at', $year)
                    ->orWhereYear('lppm_ip_registrations.created_at', $year);
            })
            ->select('it.name as label', DB::raw('count(*) as total'))
            ->groupBy('it.name')
            ->get()
            ->map(fn ($r) => ['label' => 'HKI: '.$r->label, 'value' => (int) $r->total]);

        return $pubs->concat($hkis)->values()->all();
    }

    /**
     * @return list<int>
     */
    protected function yearRange(): array
    {
        $current = (int) now()->format('Y');
        $back = (int) config('sipepeng_public_dashboard.allowed_years_back', 10);

        return range($current - $back, $current);
    }
}
