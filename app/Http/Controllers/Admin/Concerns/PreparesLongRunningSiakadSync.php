<?php

namespace App\Http\Controllers\Admin\Concerns;

trait PreparesLongRunningSiakadSync
{
    protected function prepareLongRunningSync(): void
    {
        $seconds = (int) config('siakad.sync_max_execution_seconds', 900);

        if ($seconds <= 0) {
            @set_time_limit(0);
            @ini_set('max_execution_time', '0');

            return;
        }

        @set_time_limit($seconds);
        @ini_set('max_execution_time', (string) $seconds);
    }
}
