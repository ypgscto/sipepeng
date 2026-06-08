<?php

namespace App\Http\Requests\Letter;

use App\Support\Letter\LetterPermissions;
use App\Support\Letter\LetterValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreLetterApprovalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return LetterPermissions::canApprove($this->user());
    }

    public function rules(): array
    {
        return LetterValidationRules::approvalRules();
    }
}
