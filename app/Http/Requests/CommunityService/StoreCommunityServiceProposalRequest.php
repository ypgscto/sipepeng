<?php

namespace App\Http\Requests\CommunityService;

use App\Support\CommunityService\PkmPermissions;
use App\Support\CommunityService\PkmValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreCommunityServiceProposalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return PkmPermissions::canCreate($this->user());
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return PkmValidationRules::proposal(false);
    }
}
