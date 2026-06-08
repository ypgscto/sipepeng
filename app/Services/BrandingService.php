<?php

namespace App\Services;

class BrandingService
{
    public function __construct(
        protected AppSettingsService $settings,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        $defaults = config('sipeng_branding', []);

        return [
            'app_name' => $this->get('app_name', $defaults['app_name'] ?? 'SiPepeng'),
            'app_subtitle' => $this->get('app_subtitle', $defaults['app_subtitle'] ?? ''),
            'institution_name' => $this->get('institution_name', $defaults['institution_name'] ?? ''),
            'institution_url' => $this->get('institution_url', $defaults['institution_url'] ?? ''),
            'institution_url_label' => $this->get('institution_url_label', $defaults['institution_url_label'] ?? ''),
            'footer_credit' => $this->get('footer_credit', $defaults['footer_credit'] ?? 'YPGS IT DIVISION'),
            'module' => $this->get('module', $defaults['module'] ?? 'LPPM'),
            'logo_path' => $this->logoPath(),
            'logo_url' => $this->logoUrl(),
            'has_logo' => $this->hasLogo(),
        ];
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->settings->get('general', $key);

        if ($value === null || $value === '') {
            return config("sipeng_branding.{$key}", $default);
        }

        return $value;
    }

    public function logoPath(): ?string
    {
        $configured = $this->settings->get('general', 'logo_path');

        if (is_string($configured) && $configured !== '' && file_exists(public_path($configured))) {
            return $configured;
        }

        if (file_exists(public_path('images/logo-stikes.png'))) {
            return 'images/logo-stikes.png';
        }

        return null;
    }

    public function logoUrl(): ?string
    {
        $path = $this->logoPath();

        return $path !== null ? asset($path) : null;
    }

    public function hasLogo(): bool
    {
        return $this->logoPath() !== null;
    }
}
