<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class UploadLogoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $maxKb = (int) config('sipepeng_settings.logo.max_kilobytes', 2048);
        $mimes = config('sipepeng_settings.logo.allowed_mimes', ['png', 'jpg', 'jpeg', 'webp']);

        return [
            'logo' => [
                'required',
                File::types($mimes)->max($maxKb),
            ],
        ];
    }
}
