<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class AppSettingsService
{
    protected const CACHE_PREFIX = 'sipepeng_setting:';

    public function get(string $group, string $key, mixed $default = null): mixed
    {
        $cacheKey = self::CACHE_PREFIX."{$group}.{$key}";

        return Cache::remember($cacheKey, 300, function () use ($group, $key, $default) {
            $setting = AppSetting::query()
                ->where('group', $group)
                ->where('key', $key)
                ->where('is_active', true)
                ->first();

            if ($setting === null || $setting->value === null || $setting->value === '') {
                return $default;
            }

            return $this->castValue($setting->value, $setting->value_type);
        });
    }

    public function getBool(string $group, string $key, bool $default = false): bool
    {
        return (bool) $this->get($group, $key, $default);
    }

    public function getInt(string $group, string $key, int $default = 0): int
    {
        return (int) $this->get($group, $key, $default);
    }

    public function getSecret(string $group, string $key): ?string
    {
        $encrypted = $this->getRawValue($group, $key);

        if ($encrypted === null || $encrypted === '') {
            return null;
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (\Throwable) {
            return null;
        }
    }

    public function set(
        string $group,
        string $key,
        mixed $value,
        ?string $valueType = null,
        ?string $label = null,
        ?int $updatedBy = null,
        bool $encrypt = false,
    ): AppSetting {
        if ($encrypt && is_string($value) && $value !== '') {
            $storedValue = Crypt::encryptString($value);
            $valueType ??= 'encrypted';
        } else {
            $storedValue = $this->serializeValue($value, $valueType);
        }

        $setting = AppSetting::query()->updateOrCreate(
            ['group' => $group, 'key' => $key],
            array_filter([
                'value' => $storedValue,
                'value_type' => $valueType ?? $this->inferType($value),
                'label' => $label,
                'is_active' => true,
                'updated_by' => $updatedBy ?? auth()->id(),
            ], fn ($item) => $item !== null),
        );

        $this->forgetCache($group, $key);

        return $setting;
    }

    public function forgetCache(string $group, string $key): void
    {
        Cache::forget(self::CACHE_PREFIX."{$group}.{$key}");
    }

    public function hasStoredSecret(string $group, string $key): bool
    {
        $value = $this->getRawValue($group, $key);

        return $value !== null && $value !== '';
    }

    protected function getRawValue(string $group, string $key): ?string
    {
        $setting = AppSetting::query()
            ->where('group', $group)
            ->where('key', $key)
            ->where('is_active', true)
            ->first();

        return $setting?->value;
    }

    protected function serializeValue(mixed $value, ?string $type): string
    {
        return match ($type ?? $this->inferType($value)) {
            'boolean' => $value ? 'true' : 'false',
            'integer' => (string) (int) $value,
            'float' => (string) (float) $value,
            'json' => json_encode($value, JSON_THROW_ON_ERROR),
            default => (string) $value,
        };
    }

    protected function inferType(mixed $value): string
    {
        return match (true) {
            is_bool($value) => 'boolean',
            is_int($value) => 'integer',
            is_float($value) => 'float',
            is_array($value) => 'json',
            default => 'string',
        };
    }

    protected function castValue(?string $value, ?string $type): mixed
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'float' => (float) $value,
            'json' => json_decode((string) $value, true),
            'encrypted' => null,
            default => $value,
        };
    }
}
