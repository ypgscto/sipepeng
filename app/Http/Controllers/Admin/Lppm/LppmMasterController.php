<?php

namespace App\Http\Controllers\Admin\Lppm;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Lppm\Concerns\ManagesLppmMaster;

abstract class LppmMasterController extends Controller
{
    use ManagesLppmMaster;
}
