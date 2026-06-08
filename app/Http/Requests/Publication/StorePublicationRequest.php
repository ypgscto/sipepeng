<?php

namespace App\Http\Requests\Publication;

use App\Support\Publication\PublicationPermissions;
use App\Support\Publication\PublicationValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class StorePublicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return PublicationPermissions::canCreate($this->user());
    }

    public function rules(): array
    {
        return PublicationValidationRules::record(false);
    }
}
