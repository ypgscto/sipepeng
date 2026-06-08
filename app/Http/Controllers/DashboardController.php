<?php

namespace App\Http\Controllers;

use App\Exceptions\Report\ReportScopeException;
use App\Services\Report\DashboardStatisticsService;
use App\Services\Report\ReportChartDataService;
use App\Services\Report\ReportFilterService;
use App\Services\Siakad\SiakadReferenceCacheService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(
        Request $request,
        ReportFilterService $filterService,
        DashboardStatisticsService $statistics,
        ReportChartDataService $charts,
        SiakadReferenceCacheService $cache,
    ): View {
        $scopeError = null;

        try {
            $filter = $filterService->fromRequest($request);
            $stats = $statistics->statCards($filter);
            $chartData = $charts->all($filter);
        } catch (ReportScopeException $exception) {
            $filter = null;
            $stats = [];
            $chartData = [];
            $scopeError = $exception->getMessage();
        }

        $cacheRow = $cache->getFresh('tahun_akademik');

        return view('dashboard', [
            'stats' => $stats,
            'charts' => $chartData,
            'filter' => $filter,
            'scopeError' => $scopeError,
            'filterOptions' => $filterService->filterOptions($request->user()),
            'filters' => $request->only(['tahun_akademik_id', 'semester_id', 'prodi_id', 'dosen_id', 'status', 'calendar_year']),
            'modules' => collect(config('sipeng_sidebar.items', []))
                ->filter(fn (array $item): bool => ($item['route'] ?? '') !== 'dashboard')
                ->values()
                ->all(),
            'siakadStatus' => $cacheRow
                ? 'Cache: '.$cacheRow->fetched_at?->diffForHumans()
                : 'Belum dikonfigurasi',
        ]);
    }
}
