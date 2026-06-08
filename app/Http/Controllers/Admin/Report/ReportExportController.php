<?php

namespace App\Http\Controllers\Admin\Report;

use App\Http\Controllers\Controller;
use App\Exceptions\Report\ReportScopeException;
use App\Services\ActivityLogger;
use App\Services\Report\ReportExportService;
use App\Services\Report\ReportFilterService;
use App\Support\Report\ReportPermissions;
use Illuminate\Http\Request;

class ReportExportController extends Controller
{
    public function __construct(
        protected ReportFilterService $filterService,
        protected ReportExportService $export,
    ) {}

    public function excel(string $type, Request $request, ActivityLogger $logger)
    {
        abort_unless(ReportPermissions::canExportType($request->user(), $type), 403);
        abort_unless(isset(config('sipepeng_reports.types')[$type]), 404);

        try {
            $filter = $this->filterService->fromRequest($request);
        } catch (ReportScopeException $exception) {
            abort(403, $exception->getMessage());
        }

        $logger->logAudit(
            'report_exported',
            null,
            'Export Excel laporan.',
            ['type' => $type, 'format' => 'excel', 'filter' => $filter->toArray()],
            $request,
            logName: 'lppm_report',
        );

        return $this->export->excel($type, $filter);
    }

    public function pdf(string $type, Request $request, ActivityLogger $logger)
    {
        abort_unless(ReportPermissions::canExportType($request->user(), $type), 403);
        abort_unless(isset(config('sipepeng_reports.types')[$type]), 404);

        try {
            $filter = $this->filterService->fromRequest($request);
        } catch (ReportScopeException $exception) {
            abort(403, $exception->getMessage());
        }

        $logger->logAudit(
            'report_exported',
            null,
            'Export PDF laporan.',
            ['type' => $type, 'format' => 'pdf', 'filter' => $filter->toArray()],
            $request,
            logName: 'lppm_report',
        );

        return $this->export->pdf($type, $filter);
    }
}
