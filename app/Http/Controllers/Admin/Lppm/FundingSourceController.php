<?php

namespace App\Http\Controllers\Admin\Lppm;

class FundingSourceController extends LppmMasterController
{
    protected function entityKey(): string
    {
        return 'funding-sources';
    }
}
