<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Support\Settings\SettingsPermissions;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        return view('admin.settings.index', [
            'canManage' => SettingsPermissions::canManage(auth()->user()),
            'canBackup' => SettingsPermissions::canBackup(auth()->user()),
        ]);
    }
}
