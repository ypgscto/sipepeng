<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use Illuminate\Database\Seeder;

class AppSettingSeeder extends Seeder
{
    public function run(): void
    {
        $branding = config('sipeng_branding', []);

        $settings = [
            [
                'group' => 'general',
                'key' => 'app_name',
                'value' => $branding['app_name'] ?? 'SiPepeng',
                'value_type' => 'string',
                'label' => 'Nama Aplikasi',
            ],
            [
                'group' => 'general',
                'key' => 'app_subtitle',
                'value' => $branding['app_subtitle'] ?? '',
                'value_type' => 'string',
                'label' => 'Subjudul Aplikasi',
            ],
            [
                'group' => 'general',
                'key' => 'institution_name',
                'value' => $branding['institution_name'] ?? '',
                'value_type' => 'string',
                'label' => 'Nama Institusi',
            ],
            [
                'group' => 'general',
                'key' => 'institution_url',
                'value' => $branding['institution_url'] ?? '',
                'value_type' => 'string',
                'label' => 'URL Institusi',
            ],
            [
                'group' => 'general',
                'key' => 'institution_url_label',
                'value' => $branding['institution_url_label'] ?? '',
                'value_type' => 'string',
                'label' => 'Label URL Institusi',
            ],
            [
                'group' => 'general',
                'key' => 'footer_credit',
                'value' => $branding['footer_credit'] ?? 'YPGS IT DIVISION',
                'value_type' => 'string',
                'label' => 'Footer Credit',
            ],
            [
                'group' => 'general',
                'key' => 'module',
                'value' => $branding['module'] ?? 'LPPM',
                'value_type' => 'string',
                'label' => 'Modul',
            ],
            [
                'group' => 'siakad',
                'key' => 'cache_ttl_minutes',
                'value' => '360',
                'value_type' => 'integer',
                'label' => 'TTL Cache Referensi SIAKAD (menit)',
            ],
            [
                'group' => 'siakad',
                'key' => 'cache_enabled',
                'value' => 'true',
                'value_type' => 'boolean',
                'label' => 'Aktifkan Cache Referensi SIAKAD',
            ],
            [
                'group' => 'siakad',
                'key' => 'timeout',
                'value' => (string) config('siakad.timeout', 120),
                'value_type' => 'integer',
                'label' => 'Timeout API (detik)',
            ],
        ];

        foreach ($settings as $setting) {
            AppSetting::query()->updateOrCreate(
                ['group' => $setting['group'], 'key' => $setting['key']],
                array_merge($setting, ['is_active' => true]),
            );
        }
    }
}
