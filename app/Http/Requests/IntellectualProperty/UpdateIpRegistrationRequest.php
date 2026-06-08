<?php

namespace App\Http\Requests\IntellectualProperty;

use App\Support\IntellectualProperty\IpPermissions;
use App\Support\IntellectualProperty\IpValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdateIpRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $registration = $this->route('ipRegistration');

        return $registration && IpPermissions::canEdit($this->user(), $registration);
    }

    public function rules(): array
    {
        return IpValidationRules::record(true);
    }
}
