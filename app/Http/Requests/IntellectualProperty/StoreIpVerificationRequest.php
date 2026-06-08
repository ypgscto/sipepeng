<?php

namespace App\Http\Requests\IntellectualProperty;

use App\Support\IntellectualProperty\IpPermissions;
use App\Support\IntellectualProperty\IpValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreIpVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return IpPermissions::canVerify($this->user());
    }

    public function rules(): array
    {
        return IpValidationRules::verification();
    }
}
