<?php

namespace App\Support\Siakad;

use App\Services\AppSettingsService;
use RuntimeException;

final class SiakadConfig
{
    public static function baseUrl(): string
    {
        $fromSettings = app(AppSettingsService::class)->get('siakad', 'base_url');

        if (is_string($fromSettings) && $fromSettings !== '') {
            return rtrim($fromSettings, '/');
        }

        return rtrim((string) config('siakad.base_url', ''), '/');
    }

    public static function token(): string
    {
        $fromSettings = app(AppSettingsService::class)->getSecret('siakad', 'api_token');

        if (is_string($fromSettings) && $fromSettings !== '') {
            return $fromSettings;
        }

        return (string) config('siakad.token', '');
    }

    public static function timeout(): int
    {
        $fromSettings = app(AppSettingsService::class)->getInt('siakad', 'timeout', 0);

        if ($fromSettings > 0) {
            return $fromSettings;
        }

        return (int) config('siakad.timeout', 120);
    }

    public static function endpointPath(string $resourceKey): string
    {
        $path = config("siakad.endpoints.{$resourceKey}");

        if (! is_string($path) || $path === '') {
            throw new RuntimeException(
                "Endpoint Siakad-API untuk [{$resourceKey}] belum dikonfigurasi di config/siakad.php.",
            );
        }

        if (! str_starts_with($path, '/')) {
            throw new RuntimeException(
                "Endpoint [{$resourceKey}] harus diawali '/' (nilai saat ini: {$path}).",
            );
        }

        return $path;
    }

    public static function isConfigured(): bool
    {
        return self::baseUrl() !== '' && self::token() !== '';
    }

    public static function tokenIsConfigured(): bool
    {
        return self::token() !== '';
    }

    public static function tokenDecryptFailed(): bool
    {
        $settings = app(AppSettingsService::class);

        if (! $settings->hasStoredSecret('siakad', 'api_token')) {
            return false;
        }

        return self::token() === '' && (string) config('siakad.token', '') === '';
    }
}
