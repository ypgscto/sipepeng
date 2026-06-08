<?php

namespace App\Services\Report;

use App\DataTransferObjects\Report\ReportFilterDto;

class AccreditationReportService
{
    public function __construct(protected DashboardStatisticsService $dashboard) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function build(ReportFilterDto $filter): array
    {
        $stats = $this->dashboard->build($filter);
        $rows = [];

        foreach (config('sipepeng_reports.accreditation_indicators', []) as $indicator) {
            $rows[] = [
                'code' => $indicator['code'],
                'label' => $indicator['label'],
                'module' => $indicator['module'],
                'value' => $stats[$indicator['metric']] ?? 0,
                'unit' => str_contains($indicator['metric'], 'rab') || str_contains($indicator['metric'], 'funding') ? 'Rp' : 'jumlah',
            ];
        }

        return $rows;
    }
}
