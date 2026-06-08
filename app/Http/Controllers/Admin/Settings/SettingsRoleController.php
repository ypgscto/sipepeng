<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateRoleMappingRequest;
use App\Models\SipepengRole;
use App\Services\ActivityLogger;
use App\Support\Settings\SettingsPermissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsRoleController extends Controller
{
    public function index(): View
    {
        $roles = SipepengRole::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.settings.roles', [
            'roles' => $roles,
            'mapTypes' => config('sipepeng_settings.siakad_map_types', []),
        ]);
    }

    public function update(
        UpdateRoleMappingRequest $request,
        SipepengRole $role,
        ActivityLogger $logger,
    ): RedirectResponse {
        $validated = $request->validated();
        $before = $role->only(['siakad_map_type', 'siakad_map_key']);

        $role->update([
            'siakad_map_type' => $validated['siakad_map_type'] ?: null,
            'siakad_map_key' => $validated['siakad_map_key'] ?: null,
        ]);

        $logger->logCrud(
            'updated',
            $role,
            $before,
            $role->only(['siakad_map_type', 'siakad_map_key']),
            'Mapping role SIAKAD diperbarui.',
            logName: 'security',
            request: $request,
        );

        return redirect()
            ->route('admin.settings.roles.index')
            ->with('success', "Mapping role {$role->name} berhasil disimpan.");
    }
}
