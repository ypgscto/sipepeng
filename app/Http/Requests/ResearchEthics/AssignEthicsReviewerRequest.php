<?php

namespace App\Http\Requests\ResearchEthics;

use App\Support\ResearchEthics\EthicsValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class AssignEthicsReviewerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(config('sipepeng_ethics.manage_roles', []));
    }

    public function rules(): array
    {
        return EthicsValidationRules::assignReviewer();
    }
}
