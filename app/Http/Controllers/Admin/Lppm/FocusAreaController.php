<?php

namespace App\Http\Controllers\Admin\Lppm;

class FocusAreaController extends LppmMasterController
{
    protected function entityKey(): string
    {
        return 'focus-areas';
    }
}
