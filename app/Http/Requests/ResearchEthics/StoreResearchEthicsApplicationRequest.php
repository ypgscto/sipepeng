<?php

namespace App\Http\Requests\ResearchEthics;

use App\Support\ResearchEthics\EthicsPermissions;
use App\Support\ResearchEthics\EthicsValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreResearchEthicsApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return EthicsPermissions::canCreate($this->user());
    }

    public function rules(): array
    {
        return EthicsValidationRules::record(false);
    }
}
