<?php

namespace App\Http\Requests\Research;

use App\Support\Research\ResearchPermissions;
use App\Support\Research\ResearchValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class AssignReviewerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ResearchPermissions::canAssignReviewer($this->user());
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return ResearchValidationRules::assignReviewer();
    }
}
