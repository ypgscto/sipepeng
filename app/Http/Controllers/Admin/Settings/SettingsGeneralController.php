<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateGeneralSettingsRequest;
use App\Services\ActivityLogger;
use App\Services\AppSettingsService;
use App\Support\Settings\SettingsPermissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsGeneralController extends Controller
{
    public function edit(AppSettingsService $settings): View
    {
        return view('admin.settings.general', [
            'values' => [
                'app_name' => $settings->get('general', 'app_name', config('sipeng_branding.app_name')),
                'app_subtitle' => $settings->get('general', 'app_subtitle', config('sipeng_branding.app_subtitle')),
                'institution_name' => $settings->get('general', 'institution_name', config('sipeng_branding.institution_name')),
                'institution_url' => $settings->get('general', 'institution_url', config('sipeng_branding.institution_url')),
                'institution_url_label' => $settings->get('general', 'institution_url_label', config('sipeng_branding.institution_url_label')),
                'module' => $settings->get('general', 'module', config('sipeng_branding.module')),
                'footer_credit' => $settings->get('general', 'footer_credit', config('sipeng_branding.footer_credit')),
            ],
        ]);
    }

    public function update(
        UpdateGeneralSettingsRequest $request,
        AppSettingsService $settings,
        ActivityLogger $logger,
    ): RedirectResponse {
        $validated = $request->validated();
        $userId = $request->user()->id;

        foreach ($validated as $key => $value) {
            $settings->set('general', $key, $value, updatedBy: $userId);
        }

        $logger->logAudit(
            'settings_updated',
            null,
            'Profil aplikasi dan footer diperbarui.',
            ['group' => 'general', 'keys' => array_keys($validated)],
            $request,
            logName: 'security',
        );

        return redirect()
            ->route('admin.settings.general.edit')
            ->with('success', 'Profil aplikasi berhasil disimpan.');
    }
}
