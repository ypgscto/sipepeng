<?php

namespace App\Services\Report;

use App\DataTransferObjects\Report\ReportFilterDto;
use App\Exports\Reports\GenericReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportService
{
    public function __construct(protected ReportQueryService $queries) {}

    public function excel(string $type, ReportFilterDto $filter): BinaryFileResponse
    {
        [$headings, $rows] = $this->dataset($type, $filter);
        $filename = 'laporan-'.$type.'-'.now()->format('Ymd-His').'.xlsx';

        return Excel::download(new GenericReportExport($headings, $rows), $filename);
    }

    public function pdf(string $type, ReportFilterDto $filter): StreamedResponse
    {
        [$headings, $rows] = $this->dataset($type, $filter);
        $title = config('sipepeng_reports.types.'.$type.'.label', $type);

        $pdf = Pdf::loadView('admin.reports.pdf.table', [
            'title' => $title,
            'headings' => $headings,
            'rows' => $rows,
            'filter' => $filter,
            'generatedAt' => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('laporan-'.$type.'.pdf');
    }

    /**
     * @return array{0: list<string>, 1: list<list<string|int|float|null>>}
     */
    public function dataset(string $type, ReportFilterDto $filter): array
    {
        return match ($type) {
            'research' => $this->exportResearch($filter),
            'pkm' => $this->exportPkm($filter),
            'publications' => $this->exportPublications($filter),
            'hki' => $this->exportHki($filter),
            'ethics' => $this->exportEthics($filter),
            'partners' => $this->exportPartners($filter),
            'funding' => $this->exportFunding($filter),
            'lecturer-performance' => $this->exportLecturerPerformance($filter),
            'prodi-performance' => $this->exportProdiPerformance($filter),
            'accreditation' => $this->exportAccreditation($filter),
            default => [[], []],
        };
    }

    /**
     * @param  Collection<int, mixed>  $records
     * @return list<list<string|int|float|null>>
     */
    protected function trimForExport(Collection $records): array
    {
        $max = $this->queries->exportMaxRows();
        if ($records->count() > $max) {
            abort(422, "Export melebihi batas {$max} baris. Persempit filter laporan terlebih dahulu.");
        }

        return $records->values()->all();
    }

    /**
     * @return array{0: list<string>, 1: list<list<string|int|float|null>>}
     */
    protected function exportResearch(ReportFilterDto $filter): array
    {
        $headings = ['Nomor', 'TA', 'Prodi', 'Skema', 'Judul', 'Ketua', 'Status', 'Total RAB'];
        $records = $this->trimForExport($this->queries->researchForExport($filter));
        $rows = collect($records)->map(fn ($r) => [
            $r->proposal_number, $r->tahun_akademik_nama_snapshot, $r->prodi_nama_snapshot,
            $r->skema?->name, $r->judul, $r->ketua_dosen_nama_snapshot, $r->status, $r->total_rab,
        ])->all();

        return [$headings, $rows];
    }

    protected function exportPkm(ReportFilterDto $filter): array
    {
        $headings = ['Nomor', 'TA', 'Prodi', 'Mitra', 'Judul', 'Ketua', 'Status', 'Total RAB'];
        $records = $this->trimForExport($this->queries->pkmForExport($filter));
        $rows = collect($records)->map(fn ($r) => [
            $r->proposal_number, $r->tahun_akademik_nama_snapshot, $r->prodi_nama_snapshot,
            $r->mitra_nama_snapshot, $r->judul, $r->ketua_dosen_nama_snapshot, $r->status, $r->total_rab,
        ])->all();

        return [$headings, $rows];
    }

    protected function exportPublications(ReportFilterDto $filter): array
    {
        $headings = ['Nomor', 'Judul', 'Jenis', 'Tahun', 'Prodi', 'Status', 'DOI'];
        $records = $this->trimForExport($this->queries->publicationsForExport($filter));
        $rows = collect($records)->map(fn ($r) => [
            $r->registration_number, $r->judul, $r->publicationType?->name, $r->publication_year,
            $r->prodi_nama_snapshot, $r->status, $r->doi,
        ])->all();

        return [$headings, $rows];
    }

    protected function exportHki(ReportFilterDto $filter): array
    {
        $headings = ['Nomor', 'Judul', 'Jenis', 'Prodi', 'Status', 'No. Sertifikat'];
        $records = $this->trimForExport($this->queries->hkiForExport($filter));
        $rows = collect($records)->map(fn ($r) => [
            $r->registration_number, $r->judul, $r->ipType?->name, $r->prodi_nama_snapshot, $r->status, $r->certificate_number,
        ])->all();

        return [$headings, $rows];
    }

    protected function exportEthics(ReportFilterDto $filter): array
    {
        $headings = ['Nomor', 'Proposal', 'Ketua', 'Prodi', 'Risiko', 'Status', 'Valid Until'];
        $records = $this->trimForExport($this->queries->ethicsForExport($filter));
        $rows = collect($records)->map(fn ($r) => [
            $r->application_number, $r->proposal_judul_snapshot, $r->ketua_dosen_nama_snapshot,
            $r->prodi_nama_snapshot, $r->risk_level, $r->status, $r->valid_until?->format('Y-m-d'),
        ])->all();

        return [$headings, $rows];
    }

    protected function exportPartners(ReportFilterDto $filter): array
    {
        $headings = ['Kode', 'Nama', 'Jenis', 'Kota', 'Aktif', 'Jumlah PkM'];
        $records = $this->trimForExport($this->queries->partnersForExport($filter));
        $rows = collect($records)->map(fn ($r) => [
            $r->partner_code, $r->name, $r->partnerType?->name, $r->city, $r->is_active ? 'Ya' : 'Tidak', $r->pkm_count ?? 0,
        ])->all();

        return [$headings, $rows];
    }

    protected function exportFunding(ReportFilterDto $filter): array
    {
        $headings = ['Modul', 'Nomor', 'Judul', 'Prodi', 'Skema', 'Total RAB', 'Status', 'TA'];
        $records = $this->queries->funding($filter);
        if ($records->count() > $this->queries->exportMaxRows()) {
            abort(422, 'Export melebihi batas baris. Persempit filter laporan terlebih dahulu.');
        }
        $rows = $records->map(fn ($r) => [
            $r->modul, $r->nomor, $r->judul, $r->prodi, $r->skema, $r->total_rab, $r->status, $r->tahun_akademik,
        ])->all();

        return [$headings, $rows];
    }

    protected function exportLecturerPerformance(ReportFilterDto $filter): array
    {
        $headings = ['Dosen ID', 'Nama', 'Penelitian', 'PkM', 'Publikasi', 'HKI'];
        $records = $this->queries->lecturerPerformance($filter);
        if ($records->count() > $this->queries->exportMaxRows()) {
            abort(422, 'Export melebihi batas baris. Persempit filter laporan terlebih dahulu.');
        }
        $rows = $records->map(fn ($r) => [
            $r['dosen_id'], $r['nama'], $r['penelitian'], $r['pkm'], $r['publikasi'], $r['hki'],
        ])->all();

        return [$headings, $rows];
    }

    protected function exportProdiPerformance(ReportFilterDto $filter): array
    {
        $headings = ['Prodi ID', 'Prodi', 'Penelitian', 'PkM', 'Publikasi', 'HKI', 'Total RAB'];
        $records = $this->queries->prodiPerformance($filter);
        if ($records->count() > $this->queries->exportMaxRows()) {
            abort(422, 'Export melebihi batas baris. Persempit filter laporan terlebih dahulu.');
        }
        $rows = $records->map(fn ($r) => [
            $r['prodi_id'], $r['prodi_nama'], $r['penelitian'], $r['pkm'], $r['publikasi'], $r['hki'], $r['rab'],
        ])->all();

        return [$headings, $rows];
    }

    protected function exportAccreditation(ReportFilterDto $filter): array
    {
        $headings = ['Kode Indikator', 'Indikator', 'Modul', 'Nilai', 'Satuan'];
        $rows = app(AccreditationReportService::class)->build($filter);
        $mapped = collect($rows)->map(fn ($r) => [$r['code'], $r['label'], $r['module'], $r['value'], $r['unit']])->all();

        return [$headings, $mapped];
    }
}
