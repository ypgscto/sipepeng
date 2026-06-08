<?php

namespace App\Http\Requests\Research;

use App\Support\Research\ResearchPermissions;
use App\Support\Research\ResearchValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class SubmitReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        $proposal = $this->route('proposal');

        return $proposal && ResearchPermissions::canSubmitReview($this->user(), $proposal);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return ResearchValidationRules::submitReview();
    }
}
