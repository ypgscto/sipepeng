<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateSiakadSettingsRequest;
use App\Services\ActivityLogger;
use App\Services\AppSettingsService;
use App\Support\Siakad\SiakadConfig;
use App\Support\Settings\SettingsPermissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsSiakadController extends Controller
{
    public function edit(AppSettingsService $settings): View
    {
        return view('admin.settings.siakad', [
            'baseUrl' => SiakadConfig::baseUrl(),
            'envBaseUrl' => (string) config('siakad.base_url', ''),
            'tokenConfigured' => SiakadConfig::tokenIsConfigured(),
            'tokenDecryptFailed' => SiakadConfig::tokenDecryptFailed(),
            'isConfigured' => SiakadConfig::isConfigured(),
            'cacheEnabled' => $settings->getBool('siakad', 'cache_enabled', true),
            'cacheTtlMinutes' => $settings->getInt('siakad', 'cache_ttl_minutes', 360),
            'timeout' => SiakadConfig::timeout(),
        ]);
    }

    public function update(
        UpdateSiakadSettingsRequest $request,
        AppSettingsService $settings,
        ActivityLogger $logger,
    ): RedirectResponse {
        $validated = $request->validated();
        $userId = $request->user()->id;

        if (isset($validated['base_url'])) {
            $settings->set('siakad', 'base_url', rtrim($validated['base_url'], '/'), updatedBy: $userId);
        }

        $settings->set('siakad', 'cache_enabled', $request->boolean('cache_enabled'), updatedBy: $userId);
        $settings->set('siakad', 'cache_ttl_minutes', (int) $validated['cache_ttl_minutes'], updatedBy: $userId);
        $settings->set('siakad', 'timeout', (int) $validated['timeout'], updatedBy: $userId);

        if (! empty($validated['api_token_new'])) {
            $settings->set(
                'siakad',
                'api_token',
                $validated['api_token_new'],
                encrypt: true,
                updatedBy: $userId,
            );
        }

        $auditProperties = [
            'base_url' => rtrim((string) ($validated['base_url'] ?? SiakadConfig::baseUrl()), '/'),
            'cache_enabled' => (bool) $validated['cache_enabled'],
            'cache_ttl_minutes' => (int) $validated['cache_ttl_minutes'],
            'timeout' => (int) $validated['timeout'],
            'token_updated' => ! empty($validated['api_token_new']),
        ];

        $logger->logAudit(
            'settings_updated',
            null,
            'Konfigurasi SIAKAD-API diperbarui.',
            $auditProperties,
            $request,
            logName: 'security',
        );

        return redirect()
            ->route('admin.settings.siakad.edit')
            ->with('success', 'Konfigurasi SIAKAD-API berhasil disimpan.');
    }
}
