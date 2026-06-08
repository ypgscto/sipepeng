<?php

namespace App\Http\Controllers;

use App\Services\SidebarMenu;
use Illuminate\View\View;

class ModulePlaceholderController extends Controller
{
    public function __invoke(SidebarMenu $sidebarMenu): View
    {
        $routeName = request()->route()?->getName();

        return view('modules.placeholder', [
            'label' => $sidebarMenu->labelForRoute($routeName),
            'routeName' => $routeName,
        ]);
    }
}
