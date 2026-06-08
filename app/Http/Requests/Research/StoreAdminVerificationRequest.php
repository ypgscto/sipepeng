<?php

namespace App\Http\Requests\Research;

use App\Support\Research\ResearchPermissions;
use App\Support\Research\ResearchValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreAdminVerificationRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->has('is_document_complete')) {
            $this->merge(['is_document_complete' => false]);
        }
    }

    public function authorize(): bool
    {
        return ResearchPermissions::canVerifyAdmin($this->user());
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return ResearchValidationRules::adminVerification();
    }
}
