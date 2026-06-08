<?php

namespace App\Http\Controllers\Admin\Lppm;

class PublicationTypeController extends LppmMasterController
{
    protected function entityKey(): string
    {
        return 'publication-types';
    }
}
