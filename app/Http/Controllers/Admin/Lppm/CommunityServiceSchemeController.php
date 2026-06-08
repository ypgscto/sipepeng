<?php

namespace App\Http\Controllers\Admin\Lppm;

class CommunityServiceSchemeController extends LppmMasterController
{
    protected function entityKey(): string
    {
        return 'community-service-schemes';
    }
}
