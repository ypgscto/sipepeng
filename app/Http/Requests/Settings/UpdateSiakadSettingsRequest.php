<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSiakadSettingsRequest extends FormRequest
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
            'base_url' => ['nullable', 'url', 'max:255'],
            'api_token_new' => ['nullable', 'string', 'max:500'],
            'cache_enabled' => ['required', 'boolean'],
            'cache_ttl_minutes' => ['required', 'integer', 'min:1', 'max:10080'],
            'timeout' => ['required', 'integer', 'min:5', 'max:600'],
        ];
    }
}
