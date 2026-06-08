<?php

namespace App\Http\Requests\Research\Concerns;

use App\Support\Research\ResearchValidationRules;

trait PreparesResearchProposalInput
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'budget_items' => ResearchValidationRules::filterBudgetItems($this->input('budget_items')),
        ]);
    }
}
