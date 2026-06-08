<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleMappingRequest extends FormRequest
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
        $mapTypes = array_keys(config('sipepeng_settings.siakad_map_types', []));

        return [
            'siakad_map_type' => ['nullable', 'string', Rule::in(array_merge($mapTypes, ['']))],
            'siakad_map_key' => ['nullable', 'string', 'max:50'],
        ];
    }
}
