<?php

namespace App\Http\Requests\Publication;

use App\Support\Publication\PublicationPermissions;
use App\Support\Publication\PublicationValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class StorePublicationVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return PublicationPermissions::canVerify($this->user());
    }

    public function rules(): array
    {
        return PublicationValidationRules::verification();
    }
}
