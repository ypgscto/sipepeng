<?php

namespace App\Http\Controllers\Admin\Lppm;

class ReviewerController extends LppmMasterController
{
    protected function entityKey(): string
    {
        return 'reviewers';
    }
}
