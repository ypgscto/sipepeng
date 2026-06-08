<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UploadLogoRequest;
use App\Services\ActivityLogger;
use App\Services\AppSettingsService;
use App\Services\BrandingService;
use App\Support\Settings\SettingsPermissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class SettingsLogoController extends Controller
{
    public function edit(BrandingService $branding): View
    {
        return view('admin.settings.logo', [
            'logoUrl' => $branding->logoUrl(),
            'hasLogo' => $branding->hasLogo(),
        ]);
    }

    public function update(
        UploadLogoRequest $request,
        AppSettingsService $settings,
        ActivityLogger $logger,
    ): RedirectResponse {
        $file = $request->file('logo');
        $extension = strtolower($file->getClientOriginalExtension());
        $subdirectory = trim((string) config('sipepeng_settings.logo.public_subdirectory', 'images'), '/');
        $filename = (string) config('sipepeng_settings.logo.filename', 'institution-logo');
        $relativePath = "{$subdirectory}/{$filename}.{$extension}";
        $destination = public_path($relativePath);

        File::ensureDirectoryExists(public_path($subdirectory));

        $previousPath = $settings->get('general', 'logo_path');
        if (is_string($previousPath) && $previousPath !== $relativePath && file_exists(public_path($previousPath))) {
            File::delete(public_path($previousPath));
        }

        $file->move(public_path($subdirectory), "{$filename}.{$extension}");

        $settings->set('general', 'logo_path', $relativePath, updatedBy: $request->user()->id);

        $logger->logAudit(
            'logo_uploaded',
            null,
            'Logo institusi diperbarui.',
            ['logo_path' => $relativePath],
            $request,
            logName: 'security',
        );

        return redirect()
            ->route('admin.settings.logo.edit')
            ->with('success', 'Logo institusi berhasil diunggah.');
    }
}
