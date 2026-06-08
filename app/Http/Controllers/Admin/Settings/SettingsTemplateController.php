<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Support\Settings\SettingsPermissions;
use Illuminate\View\View;

class SettingsTemplateController extends Controller
{
    public function index(): View
    {
        return view('admin.settings.templates', [
            'canManage' => SettingsPermissions::canManage(auth()->user()),
        ]);
    }
}
