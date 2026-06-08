<?php

namespace App\Http\Requests\ResearchEthics;

use App\Support\ResearchEthics\EthicsPermissions;
use App\Support\ResearchEthics\EthicsValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreEthicsDecisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return EthicsPermissions::canDecide($this->user());
    }

    public function rules(): array
    {
        return EthicsValidationRules::decision();
    }
}
