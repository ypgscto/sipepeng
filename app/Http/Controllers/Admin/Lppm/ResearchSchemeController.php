<?php

namespace App\Http\Controllers\Admin\Lppm;

class ResearchSchemeController extends LppmMasterController
{
    protected function entityKey(): string
    {
        return 'research-schemes';
    }
}
