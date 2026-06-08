<?php

namespace App\Http\Controllers\Admin\Lppm;

class DocumentCategoryController extends LppmMasterController
{
    protected function entityKey(): string
    {
        return 'document-categories';
    }
}
