<?php

namespace App\Http\Controllers\Admin\Lppm;

class OutputTypeController extends LppmMasterController
{
    protected function entityKey(): string
    {
        return 'output-types';
    }
}
