<?php

namespace App\Http\Controllers\Admin\Lppm;

class PartnerTypeController extends LppmMasterController
{
    protected function entityKey(): string
    {
        return 'partner-types';
    }
}
