<?php

namespace App\Http\Requests\Publication;

use App\Support\Publication\PublicationPermissions;
use App\Support\Publication\PublicationValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePublicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $publication = $this->route('publication');

        return $publication && PublicationPermissions::canEdit($this->user(), $publication);
    }

    public function rules(): array
    {
        return PublicationValidationRules::record(true);
    }
}
