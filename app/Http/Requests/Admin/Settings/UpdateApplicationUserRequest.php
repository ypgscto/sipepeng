<?php

namespace App\Http\Requests\Admin\Settings;

use App\Support\Settings\SettingsPermissions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateApplicationUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && SettingsPermissions::canManage($user);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var \App\Models\User|null $user */
        $user = $this->route('user');
        $siakad = $user?->isSiakadSourced() ?? false;

        $rules = [
            'is_active' => ['sometimes', 'boolean'],
            'is_allowed_login' => ['sometimes', 'boolean'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', Rule::in(config('sipepeng_users.assignable_roles', []))],
        ];

        if (! $siakad) {
            $userId = $user?->id;
            $rules['name'] = ['required', 'string', 'max:255'];
            $rules['email'] = ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)];
            $rules['password'] = ['nullable', 'confirmed', Password::defaults()];
        }

        return $rules;
    }
}
