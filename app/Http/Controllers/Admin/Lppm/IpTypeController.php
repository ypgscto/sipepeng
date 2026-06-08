<?php

namespace App\Http\Controllers\Admin\Lppm;

class IpTypeController extends LppmMasterController
{
    protected function entityKey(): string
    {
        return 'ip-types';
    }
}
