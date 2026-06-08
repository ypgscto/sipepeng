<?php

namespace App\Http\Controllers\Admin\Lppm;

class LetterTypeController extends LppmMasterController
{
    protected function entityKey(): string
    {
        return 'letter-types';
    }
}
