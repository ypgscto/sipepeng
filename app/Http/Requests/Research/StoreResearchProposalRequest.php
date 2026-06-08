<?php

namespace App\Http\Requests\Research;

use App\Http\Requests\Research\Concerns\PreparesResearchProposalInput;
use App\Support\Research\ResearchPermissions;
use App\Support\Research\ResearchValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreResearchProposalRequest extends FormRequest
{
    use PreparesResearchProposalInput;
    public function authorize(): bool
    {
        return ResearchPermissions::canCreate($this->user());
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return ResearchValidationRules::proposal(false);
    }
}
