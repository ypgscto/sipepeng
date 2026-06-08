<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGeneralSettingsRequest extends FormRequest
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
        return [
            'app_name' => ['required', 'string', 'max:100'],
            'app_subtitle' => ['nullable', 'string', 'max:255'],
            'institution_name' => ['required', 'string', 'max:150'],
            'institution_url' => ['nullable', 'url', 'max:255'],
            'institution_url_label' => ['nullable', 'string', 'max:150'],
            'module' => ['required', 'string', 'max:50'],
            'footer_credit' => ['required', 'string', 'max:255'],
        ];
    }
}
