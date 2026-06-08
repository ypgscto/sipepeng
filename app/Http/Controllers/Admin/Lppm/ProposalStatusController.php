<?php

namespace App\Http\Controllers\Admin\Lppm;

class ProposalStatusController extends LppmMasterController
{
    protected function entityKey(): string
    {
        return 'proposal-statuses';
    }
}
