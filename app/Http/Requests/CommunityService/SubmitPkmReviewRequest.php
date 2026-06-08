<?php

namespace App\Http\Requests\CommunityService;

use App\Support\CommunityService\PkmValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class SubmitPkmReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return PkmValidationRules::submitReview();
    }
}
