<?php

namespace App\Services\Report;

use App\DataTransferObjects\Report\ReportFilterDto;
use App\Exceptions\Report\ReportScopeException;
use App\Models\User;
use App\Services\Lppm\LppmSiakadLookup;
use App\Services\Siakad\SiakadReferenceService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ReportFilterService
{
    public function __construct(
        protected SiakadReferenceService $siakad,
        protected LppmSiakadLookup $lookup,
    ) {}

    public function fromRequest(Request $request, ?User $user = null): ReportFilterDto
    {
        $user ??= $request->user();
        $calendarYear = $request->filled('calendar_year')
            ? (int) $request->input('calendar_year')
            : (int) now()->format('Y');

        $dto = new ReportFilterDto(
            tahunAkademikId: $request->string('tahun_akademik_id')->toString() ?: null,
            semesterId: $request->string('semester_id')->toString() ?: null,
            prodiId: $request->string('prodi_id')->toString() ?: null,
            dosenId: $request->string('dosen_id')->toString() ?: null,
            status: $request->string('status')->toString() ?: null,
            calendarYear: $calendarYear,
            dateFrom: $request->string('date_from')->toString() ?: null,
            dateTo: $request->string('date_to')->toString() ?: null,
        );

        return $this->applyRoleScope($dto, $user);
    }

    /**
     * @throws ReportScopeException
     */
    public function applyRoleScope(ReportFilterDto $filter, User $user): ReportFilterDto
    {
        if ($user->hasAnyRole(config('sipepeng_reports.view_all_roles', []))) {
            return $filter;
        }

        if ($user->hasRole('dosen')) {
            $dosenId = $user->siakad_login ?: $user->siakad_user_id;
            if ($dosenId) {
                $filter->dosenId = $dosenId;
            }
        }

        if ($user->hasRole('ketua_prodi')) {
            $resolved = $this->resolveUserProdiId($user);
            if ($resolved === null || $resolved === '') {
                throw new ReportScopeException(
                    'Prodi Anda belum terpetakan dari referensi SIAKAD. Hubungi administrator LPPM.',
                );
            }
            $filter->prodiId = $resolved;
        }

        return $filter;
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     */
    public function applyToProposalQuery(Builder $query, ReportFilterDto $filter, string $dateColumn = 'submitted_at'): Builder
    {
        if ($filter->tahunAkademikId) {
            $query->where('tahun_akademik_id', $filter->tahunAkademikId);
        }
        if ($filter->semesterId) {
            $query->where('semester_id', $filter->semesterId);
        }
        if ($filter->prodiId) {
            $query->where('prodi_id', $filter->prodiId);
        }
        if ($filter->dosenId) {
            $query->where('ketua_dosen_id', $filter->dosenId);
        }
        if ($filter->status) {
            $query->where('status', $filter->status);
        }
        if ($filter->calendarYear) {
            $query->where(function (Builder $q) use ($filter, $dateColumn): void {
                $q->whereYear($dateColumn, $filter->calendarYear)
                    ->orWhereYear('created_at', $filter->calendarYear);
            });
        }
        if ($filter->dateFrom) {
            $query->whereDate($dateColumn, '>=', $filter->dateFrom);
        }
        if ($filter->dateTo) {
            $query->whereDate($dateColumn, '<=', $filter->dateTo);
        }

        return $query;
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     */
    public function applyToProdiRecordQuery(Builder $query, ReportFilterDto $filter, ?string $extraYearColumn = null): Builder
    {
        $table = $query->getModel()->getTable();

        if ($filter->prodiId) {
            $query->where("{$table}.prodi_id", $filter->prodiId);
        }
        if ($filter->calendarYear) {
            $query->where(function (Builder $q) use ($filter, $extraYearColumn, $table): void {
                $q->whereYear("{$table}.submitted_at", $filter->calendarYear)
                    ->orWhereYear("{$table}.created_at", $filter->calendarYear);
                if ($extraYearColumn) {
                    $column = str_contains($extraYearColumn, '.') ? $extraYearColumn : "{$table}.{$extraYearColumn}";
                    $q->orWhere($column, $filter->calendarYear);
                }
            });
        }
        if ($filter->status) {
            $query->where("{$table}.status", $filter->status);
        }

        return $query;
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     */
    public function applyDosenAuthorScope(Builder $query, ReportFilterDto $filter, string $authorsRelation): Builder
    {
        if ($filter->dosenId === null || $filter->dosenId === '') {
            return $query;
        }

        return $query->whereHas($authorsRelation, function (Builder $authorQuery) use ($filter): void {
            $authorQuery->where('dosen_id', $filter->dosenId);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function filterOptions(?User $user = null): array
    {
        $taData = $this->siakad->forTab('tahun_akademik');
        $semesterData = $this->siakad->forTab('semester');

        $prodiOptions = $this->lookup->prodiOptions();
        $dosenOptions = $this->lookup->dosenOptions();

        $user ??= auth()->user();
        if ($user !== null && ! $user->hasAnyRole(config('sipepeng_reports.view_all_roles', []))) {
            if ($user->hasRole('ketua_prodi')) {
                $prodiId = $this->resolveUserProdiId($user);
                if ($prodiId) {
                    $prodiOptions = array_values(array_filter(
                        $prodiOptions,
                        fn (array $option): bool => ($option['value'] ?? '') === $prodiId,
                    ));
                }
            }

            if ($user->hasRole('dosen')) {
                $dosenId = $user->siakad_login ?: $user->siakad_user_id;
                if ($dosenId) {
                    $dosenOptions = array_values(array_filter(
                        $dosenOptions,
                        fn (array $option): bool => ($option['value'] ?? '') === $dosenId,
                    ));
                }
            }
        }

        return [
            'prodiOptions' => $prodiOptions,
            'dosenOptions' => $dosenOptions,
            'tahunAkademikOptions' => collect($taData['records'] ?? [])->map(fn ($r) => [
                'value' => (string) ($r['siakad_id'] ?? $r['id'] ?? ''),
                'label' => (string) ($r['nama_tahun_akademik'] ?? $r['nama'] ?? $r['siakad_id'] ?? ''),
            ])->filter(fn ($o) => $o['value'] !== '')->values()->all(),
            'semesterOptions' => collect($semesterData['records'] ?? [])->map(fn ($r) => [
                'value' => (string) ($r['siakad_id'] ?? $r['id'] ?? ''),
                'label' => (string) ($r['nama_semester'] ?? $r['nama'] ?? $r['siakad_id'] ?? ''),
            ])->filter(fn ($o) => $o['value'] !== '')->values()->all(),
            'calendarYears' => range((int) now()->format('Y'), (int) now()->format('Y') - 5),
        ];
    }

    protected function resolveUserProdiId(User $user): ?string
    {
        foreach ($this->lookup->dosenOptions() as $opt) {
            if (($opt['value'] ?? '') === ($user->siakad_login ?? $user->siakad_user_id)) {
                return $opt['prodi_id'] ?? null;
            }
        }

        return null;
    }
}
