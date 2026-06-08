<?php

namespace App\Http\Controllers\Admin\Lppm;

class ScienceClusterController extends LppmMasterController
{
    protected function entityKey(): string
    {
        return 'science-clusters';
    }
}
