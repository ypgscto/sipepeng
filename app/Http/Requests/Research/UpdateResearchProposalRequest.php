<?php

namespace App\Http\Requests\Research;

use App\Http\Requests\Research\Concerns\PreparesResearchProposalInput;
use App\Support\Research\ResearchPermissions;
use App\Support\Research\ResearchValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdateResearchProposalRequest extends FormRequest
{
    use PreparesResearchProposalInput;
    public function authorize(): bool
    {
        $proposal = $this->route('proposal');

        return $proposal && ResearchPermissions::canEdit($this->user(), $proposal);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return ResearchValidationRules::proposal(true);
    }
}
