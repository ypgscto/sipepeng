<?php

namespace App\Http\Requests\IntellectualProperty;

use App\Support\IntellectualProperty\IpPermissions;
use App\Support\IntellectualProperty\IpValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreIpRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return IpPermissions::canCreate($this->user());
    }

    public function rules(): array
    {
        return IpValidationRules::record(false);
    }
}
