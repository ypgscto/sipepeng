<?php

namespace App\Http\Controllers\Admin\Report;

use App\Http\Controllers\Controller;
use App\Exceptions\Report\ReportScopeException;
use App\Services\Report\AccreditationReportService;
use App\Services\Report\ReportFilterService;
use App\Services\Report\ReportQueryService;
use App\Support\Report\ReportPermissions;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        protected ReportFilterService $filterService,
        protected ReportQueryService $queries,
        protected AccreditationReportService $accreditation,
    ) {}

    public function index(): View
    {
        abort_unless(ReportPermissions::canViewAny(auth()->user()), 403);

        return view('admin.reports.index', [
            'types' => ReportPermissions::viewableTypes(auth()->user()),
        ]);
    }

    public function show(string $type, Request $request): View
    {
        abort_unless(ReportPermissions::canViewType(auth()->user(), $type), 403);
        abort_unless(isset(config('sipepeng_reports.types')[$type]), 404);

        try {
            $filter = $this->filterService->fromRequest($request);
        } catch (ReportScopeException $exception) {
            return view('admin.reports.scope-error', [
                'message' => $exception->getMessage(),
            ]);
        }

        $meta = config('sipepeng_reports.types.'.$type);

        $data = match ($type) {
            'research' => ['records' => $this->queries->research($filter)],
            'pkm' => ['records' => $this->queries->pkm($filter)],
            'publications' => ['records' => $this->queries->publications($filter)],
            'hki' => ['records' => $this->queries->hki($filter)],
            'ethics' => ['records' => $this->queries->ethics($filter)],
            'partners' => ['records' => $this->queries->partners($filter)],
            'funding' => ['records' => $this->queries->funding($filter), 'collection' => true],
            'lecturer-performance' => ['records' => $this->queries->lecturerPerformance($filter), 'collection' => true],
            'prodi-performance' => ['records' => $this->queries->prodiPerformance($filter), 'collection' => true],
            'accreditation' => ['records' => collect($this->accreditation->build($filter)), 'collection' => true],
            default => abort(404),
        };

        return view('admin.reports.show', array_merge($data, [
            'type' => $type,
            'meta' => $meta,
            'filter' => $filter,
            'filterOptions' => $this->filterService->filterOptions($request->user()),
            'filters' => $request->only(['tahun_akademik_id', 'semester_id', 'prodi_id', 'dosen_id', 'status', 'calendar_year', 'date_from', 'date_to']),
            'canExport' => ReportPermissions::canExportType($request->user(), $type),
        ]));
    }
}
