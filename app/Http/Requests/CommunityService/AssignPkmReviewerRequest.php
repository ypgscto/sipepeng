<?php

namespace App\Http\Requests\CommunityService;

use App\Support\CommunityService\PkmPermissions;
use App\Support\CommunityService\PkmValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class AssignPkmReviewerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return PkmPermissions::canAssignReviewer($this->user());
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return PkmValidationRules::assignReviewer();
    }
}
