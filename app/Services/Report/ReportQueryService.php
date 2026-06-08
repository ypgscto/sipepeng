<?php

namespace App\Services\Report;

use App\DataTransferObjects\Report\ReportFilterDto;
use App\Models\CommunityService\CommunityServiceProposal;
use App\Models\IntellectualProperty\IpRegistration;
use App\Models\Lppm\FundingSource;
use App\Models\Lppm\Partner;
use App\Models\Publication\Publication;
use App\Models\Publication\PublicationAuthor;
use App\Models\IntellectualProperty\IpInventor;
use App\Models\Research\ResearchProposal;
use App\Models\ResearchEthics\ResearchEthicsApplication;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportQueryService
{
    public function __construct(protected ReportFilterService $filters) {}

    public function research(ReportFilterDto $filter): LengthAwarePaginator
    {
        $q = $this->filters->applyToProposalQuery(
            ResearchProposal::query()->with(['skema', 'bidangFokus']),
            $filter,
        )->orderByDesc('submitted_at');

        return $q->paginate(25)->withQueryString();
    }

    public function pkm(ReportFilterDto $filter): LengthAwarePaginator
    {
        return $this->filters->applyToProposalQuery(
            CommunityServiceProposal::query()->with(['skema', 'mitra']),
            $filter,
        )->orderByDesc('submitted_at')->paginate(25)->withQueryString();
    }

    public function publications(ReportFilterDto $filter): LengthAwarePaginator
    {
        $query = Publication::query()->with(['publicationType', 'authors']);
        $query = $this->filters->applyToProdiRecordQuery($query, $filter, 'publication_year');
        $query = $this->filters->applyDosenAuthorScope($query, $filter, 'authors');

        return $query->orderByDesc('publication_year')->paginate(25)->withQueryString();
    }

    public function hki(ReportFilterDto $filter): LengthAwarePaginator
    {
        $query = IpRegistration::query()->with(['ipType', 'inventors']);
        $query = $this->filters->applyToProdiRecordQuery($query, $filter);
        $query = $this->filters->applyDosenAuthorScope($query, $filter, 'inventors');

        return $query->orderByDesc('submitted_at')->paginate(25)->withQueryString();
    }

    public function ethics(ReportFilterDto $filter): LengthAwarePaginator
    {
        $q = $this->filters->applyToProdiRecordQuery(ResearchEthicsApplication::query(), $filter);
        if ($filter->dosenId) {
            $q->where('ketua_dosen_id', $filter->dosenId);
        }

        return $q->orderByDesc('submitted_at')->paginate(25)->withQueryString();
    }

    public function partners(ReportFilterDto $filter): LengthAwarePaginator
    {
        return Partner::query()
            ->with(['partnerType'])
            ->withCount(['communityServiceProposals as pkm_count' => function ($q) use ($filter): void {
                $this->filters->applyToProposalQuery($q, $filter);
            }])
            ->orderBy('name')
            ->paginate(25)->withQueryString();
    }

    /**
     * @return Collection<int, object>
     */
    public function funding(ReportFilterDto $filter): Collection
    {
        $research = $this->filters->applyToProposalQuery(ResearchProposal::query()->with('skema'), $filter)
            ->select('id', 'proposal_number', 'judul', 'prodi_nama_snapshot', 'skema_id', 'total_rab', 'status', 'tahun_akademik_nama_snapshot')
            ->get()
            ->map(fn ($r) => (object) [
                'modul' => 'Penelitian',
                'nomor' => $r->proposal_number,
                'judul' => $r->judul,
                'prodi' => $r->prodi_nama_snapshot,
                'skema' => $r->skema?->name,
                'total_rab' => $r->total_rab,
                'status' => $r->status,
                'tahun_akademik' => $r->tahun_akademik_nama_snapshot,
            ]);

        $pkm = $this->filters->applyToProposalQuery(CommunityServiceProposal::query()->with('skema'), $filter)
            ->select('id', 'proposal_number', 'judul', 'prodi_nama_snapshot', 'skema_id', 'total_rab', 'status', 'tahun_akademik_nama_snapshot')
            ->get()
            ->map(fn ($r) => (object) [
                'modul' => 'PkM',
                'nomor' => $r->proposal_number,
                'judul' => $r->judul,
                'prodi' => $r->prodi_nama_snapshot,
                'skema' => $r->skema?->name,
                'total_rab' => $r->total_rab,
                'status' => $r->status,
                'tahun_akademik' => $r->tahun_akademik_nama_snapshot,
            ]);

        return $research->concat($pkm)->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function lecturerPerformance(ReportFilterDto $filter): Collection
    {
        $dosenIds = collect();

        $research = $this->filters->applyToProposalQuery(ResearchProposal::query(), $filter)
            ->select('ketua_dosen_id', 'ketua_dosen_nama_snapshot', DB::raw('count(*) as cnt'))
            ->groupBy('ketua_dosen_id', 'ketua_dosen_nama_snapshot')->get();
        foreach ($research as $r) {
            $dosenIds->put($r->ketua_dosen_id, ['nama' => $r->ketua_dosen_nama_snapshot, 'penelitian' => $r->cnt, 'pkm' => 0, 'publikasi' => 0, 'hki' => 0]);
        }

        $pkm = $this->filters->applyToProposalQuery(CommunityServiceProposal::query(), $filter)
            ->select('ketua_dosen_id', 'ketua_dosen_nama_snapshot', DB::raw('count(*) as cnt'))
            ->groupBy('ketua_dosen_id', 'ketua_dosen_nama_snapshot')->get();
        foreach ($pkm as $p) {
            $row = $dosenIds->get($p->ketua_dosen_id, ['nama' => $p->ketua_dosen_nama_snapshot, 'penelitian' => 0, 'pkm' => 0, 'publikasi' => 0, 'hki' => 0]);
            $row['pkm'] = $p->cnt;
            $row['nama'] = $p->ketua_dosen_nama_snapshot;
            $dosenIds->put($p->ketua_dosen_id, $row);
        }

        $pubAuthors = PublicationAuthor::query()
            ->select('dosen_id', 'dosen_nama_snapshot', DB::raw('count(*) as cnt'))
            ->when($filter->dosenId, fn ($q) => $q->where('dosen_id', $filter->dosenId))
            ->whereHas('publication', fn ($q) => $this->filters->applyToProdiRecordQuery($q, $filter, 'publication_year'))
            ->groupBy('dosen_id', 'dosen_nama_snapshot')->get();
        foreach ($pubAuthors as $a) {
            $row = $dosenIds->get($a->dosen_id, ['nama' => $a->dosen_nama_snapshot, 'penelitian' => 0, 'pkm' => 0, 'publikasi' => 0, 'hki' => 0]);
            $row['publikasi'] = $a->cnt;
            $dosenIds->put($a->dosen_id, $row);
        }

        $inventors = IpInventor::query()
            ->select('dosen_id', 'dosen_nama_snapshot', DB::raw('count(*) as cnt'))
            ->when($filter->dosenId, fn ($q) => $q->where('dosen_id', $filter->dosenId))
            ->whereHas('ipRegistration', fn ($q) => $this->filters->applyToProdiRecordQuery($q, $filter))
            ->groupBy('dosen_id', 'dosen_nama_snapshot')->get();
        foreach ($inventors as $i) {
            $row = $dosenIds->get($i->dosen_id, ['nama' => $i->dosen_nama_snapshot, 'penelitian' => 0, 'pkm' => 0, 'publikasi' => 0, 'hki' => 0]);
            $row['hki'] = $i->cnt;
            $dosenIds->put($i->dosen_id, $row);
        }

        return $dosenIds->map(fn ($row, $id) => array_merge($row, ['dosen_id' => $id]))->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function prodiPerformance(ReportFilterDto $filter): Collection
    {
        $prodis = collect();

        $research = $this->filters->applyToProposalQuery(ResearchProposal::query(), $filter)
            ->select('prodi_id', 'prodi_nama_snapshot', DB::raw('count(*) as cnt'), DB::raw('sum(total_rab) as rab'))
            ->groupBy('prodi_id', 'prodi_nama_snapshot')->get();
        foreach ($research as $r) {
            $prodis->put($r->prodi_id, [
                'prodi_id' => $r->prodi_id,
                'prodi_nama' => $r->prodi_nama_snapshot,
                'penelitian' => $r->cnt,
                'pkm' => 0,
                'publikasi' => 0,
                'hki' => 0,
                'rab' => (float) $r->rab,
            ]);
        }

        $pkm = $this->filters->applyToProposalQuery(CommunityServiceProposal::query(), $filter)
            ->select('prodi_id', 'prodi_nama_snapshot', DB::raw('count(*) as cnt'), DB::raw('sum(total_rab) as rab'))
            ->groupBy('prodi_id', 'prodi_nama_snapshot')->get();
        foreach ($pkm as $p) {
            $row = $prodis->get($p->prodi_id, ['prodi_id' => $p->prodi_id, 'prodi_nama' => $p->prodi_nama_snapshot, 'penelitian' => 0, 'pkm' => 0, 'publikasi' => 0, 'hki' => 0, 'rab' => 0]);
            $row['pkm'] = $p->cnt;
            $row['rab'] += (float) $p->rab;
            $prodis->put($p->prodi_id, $row);
        }

        $pubs = $this->filters->applyToProdiRecordQuery(Publication::query(), $filter, 'publication_year')
            ->select('prodi_id', 'prodi_nama_snapshot', DB::raw('count(*) as cnt'))
            ->groupBy('prodi_id', 'prodi_nama_snapshot')->get();
        foreach ($pubs as $pub) {
            $row = $prodis->get($pub->prodi_id, ['prodi_id' => $pub->prodi_id, 'prodi_nama' => $pub->prodi_nama_snapshot, 'penelitian' => 0, 'pkm' => 0, 'publikasi' => 0, 'hki' => 0, 'rab' => 0]);
            $row['publikasi'] = $pub->cnt;
            $prodis->put($pub->prodi_id, $row);
        }

        $hkis = $this->filters->applyToProdiRecordQuery(IpRegistration::query(), $filter)
            ->select('prodi_id', 'prodi_nama_snapshot', DB::raw('count(*) as cnt'))
            ->groupBy('prodi_id', 'prodi_nama_snapshot')->get();
        foreach ($hkis as $h) {
            $row = $prodis->get($h->prodi_id, ['prodi_id' => $h->prodi_id, 'prodi_nama' => $h->prodi_nama_snapshot, 'penelitian' => 0, 'pkm' => 0, 'publikasi' => 0, 'hki' => 0, 'rab' => 0]);
            $row['hki'] = $h->cnt;
            $prodis->put($h->prodi_id, $row);
        }

        return $prodis->values();
    }

    public function fundingBySource(ReportFilterDto $filter): Collection
    {
        return FundingSource::query()
            ->select('lppm_funding_sources.name', 'lppm_funding_sources.source_category', DB::raw('COALESCE(SUM(lppm_research_proposals.total_rab),0) + COALESCE(SUM(pkm.total_rab),0) as total'))
            ->leftJoin('lppm_research_scheme_funding_sources as rsfs', 'rsfs.funding_source_id', '=', 'lppm_funding_sources.id')
            ->leftJoin('lppm_research_schemes as rs', 'rs.id', '=', 'rsfs.research_scheme_id')
            ->leftJoin('lppm_research_proposals', function ($join) use ($filter): void {
                $join->on('lppm_research_proposals.skema_id', '=', 'rs.id');
                if ($filter->calendarYear) {
                    $join->whereYear('lppm_research_proposals.submitted_at', $filter->calendarYear);
                }
            })
            ->leftJoin('lppm_community_service_scheme_funding_sources as csfs', 'csfs.funding_source_id', '=', 'lppm_funding_sources.id')
            ->leftJoin('lppm_community_service_schemes as css', 'css.id', '=', 'csfs.community_service_scheme_id')
            ->leftJoin('lppm_community_service_proposals as pkm', function ($join) use ($filter): void {
                $join->on('pkm.skema_id', '=', 'css.id');
                if ($filter->calendarYear) {
                    $join->whereYear('pkm.submitted_at', $filter->calendarYear);
                }
            })
            ->groupBy('lppm_funding_sources.id', 'lppm_funding_sources.name', 'lppm_funding_sources.source_category')
            ->get();
    }

    public function exportMaxRows(): int
    {
        return (int) config('sipepeng_reports.sync_export_max_rows', 5000);
    }

    /**
     * @return Collection<int, ResearchProposal>
     */
    public function researchForExport(ReportFilterDto $filter): Collection
    {
        return $this->filters->applyToProposalQuery(
            ResearchProposal::query()->with(['skema']),
            $filter,
        )->orderByDesc('submitted_at')
            ->limit($this->exportMaxRows() + 1)
            ->get();
    }

    /**
     * @return Collection<int, CommunityServiceProposal>
     */
    public function pkmForExport(ReportFilterDto $filter): Collection
    {
        return $this->filters->applyToProposalQuery(
            CommunityServiceProposal::query()->with(['skema']),
            $filter,
        )->orderByDesc('submitted_at')
            ->limit($this->exportMaxRows() + 1)
            ->get();
    }

    /**
     * @return Collection<int, Publication>
     */
    public function publicationsForExport(ReportFilterDto $filter): Collection
    {
        $query = Publication::query()->with(['publicationType']);
        $query = $this->filters->applyToProdiRecordQuery($query, $filter, 'publication_year');
        $query = $this->filters->applyDosenAuthorScope($query, $filter, 'authors');

        return $query->orderByDesc('publication_year')
            ->limit($this->exportMaxRows() + 1)
            ->get();
    }

    /**
     * @return Collection<int, IpRegistration>
     */
    public function hkiForExport(ReportFilterDto $filter): Collection
    {
        $query = IpRegistration::query()->with(['ipType']);
        $query = $this->filters->applyToProdiRecordQuery($query, $filter);
        $query = $this->filters->applyDosenAuthorScope($query, $filter, 'inventors');

        return $query->orderByDesc('submitted_at')
            ->limit($this->exportMaxRows() + 1)
            ->get();
    }

    /**
     * @return Collection<int, ResearchEthicsApplication>
     */
    public function ethicsForExport(ReportFilterDto $filter): Collection
    {
        $query = $this->filters->applyToProdiRecordQuery(ResearchEthicsApplication::query(), $filter);
        if ($filter->dosenId) {
            $query->where('ketua_dosen_id', $filter->dosenId);
        }

        return $query->orderByDesc('submitted_at')
            ->limit($this->exportMaxRows() + 1)
            ->get();
    }

    /**
     * @return Collection<int, Partner>
     */
    public function partnersForExport(ReportFilterDto $filter): Collection
    {
        return Partner::query()
            ->with(['partnerType'])
            ->withCount(['communityServiceProposals as pkm_count' => function ($q) use ($filter): void {
                $this->filters->applyToProposalQuery($q, $filter);
            }])
            ->orderBy('name')
            ->limit($this->exportMaxRows() + 1)
            ->get();
    }
}
