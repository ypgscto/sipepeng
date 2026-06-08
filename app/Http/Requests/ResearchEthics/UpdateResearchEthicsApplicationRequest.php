<?php

namespace App\Http\Requests\ResearchEthics;

use App\Support\ResearchEthics\EthicsPermissions;
use App\Support\ResearchEthics\EthicsValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdateResearchEthicsApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $application = $this->route('ethicsApplication');

        return $application && EthicsPermissions::canEdit($this->user(), $application);
    }

    public function rules(): array
    {
        return EthicsValidationRules::record(true);
    }
}
