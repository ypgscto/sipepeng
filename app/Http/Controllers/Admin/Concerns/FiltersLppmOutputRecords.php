<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait FiltersLppmOutputRecords
{
    protected function applyOutputFilters(Builder $query, Request $request, string $yearColumn = 'publication_year'): Builder
    {
        if ($search = trim((string) $request->query('q', ''))) {
            $query->where(function (Builder $q) use ($search): void {
                $q->where('judul', 'like', "%{$search}%")
                    ->orWhere('registration_number', 'like', "%{$search}%")
                    ->orWhere('proposal_judul_snapshot', 'like', "%{$search}%");
            });
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($tahun = $request->query('tahun')) {
            if ($yearColumn === 'publication_year') {
                $query->where('publication_year', (int) $tahun);
            } else {
                $query->whereYear($yearColumn, (int) $tahun);
            }
        }

        if ($prodiId = $request->query('prodi_id')) {
            $query->where('prodi_id', $prodiId);
        }

        if ($sourceType = $request->query('source_type')) {
            $query->where('source_type', $sourceType);
        }

        if ($dosenId = $request->query('dosen_id')) {
            $query->whereHas('authors', fn (Builder $q) => $q->where('dosen_id', $dosenId));
        }

        return $query;
    }

    protected function applyIpFilters(Builder $query, Request $request): Builder
    {
        if ($search = trim((string) $request->query('q', ''))) {
            $query->where(function (Builder $q) use ($search): void {
                $q->where('judul', 'like', "%{$search}%")
                    ->orWhere('registration_number', 'like', "%{$search}%")
                    ->orWhere('application_number', 'like', "%{$search}%");
            });
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($tahun = $request->query('tahun')) {
            $query->whereYear('application_date', (int) $tahun);
        }

        if ($prodiId = $request->query('prodi_id')) {
            $query->where('prodi_id', $prodiId);
        }

        if ($sourceType = $request->query('source_type')) {
            $query->where('source_type', $sourceType);
        }

        if ($dosenId = $request->query('dosen_id')) {
            $query->whereHas('inventors', fn (Builder $q) => $q->where('dosen_id', $dosenId));
        }

        return $query;
    }

    protected function applyEthicsFilters(Builder $query, Request $request): Builder
    {
        if ($search = trim((string) $request->query('q', ''))) {
            $query->where(function (Builder $q) use ($search): void {
                $q->where('proposal_judul_snapshot', 'like', "%{$search}%")
                    ->orWhere('application_number', 'like', "%{$search}%")
                    ->orWhere('ketua_dosen_nama_snapshot', 'like', "%{$search}%");
            });
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($tahun = $request->query('tahun')) {
            $query->whereYear('submitted_at', (int) $tahun);
        }

        if ($prodiId = $request->query('prodi_id')) {
            $query->where('prodi_id', $prodiId);
        }

        if ($dosenId = $request->query('dosen_id')) {
            $query->where('ketua_dosen_id', $dosenId);
        }

        return $query;
    }
}
