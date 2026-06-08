<?php

namespace App\Http\Requests\CommunityService;

use App\Support\CommunityService\PkmPermissions;
use App\Support\CommunityService\PkmValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCommunityServiceProposalRequest extends FormRequest
{
    public function authorize(): bool
    {
        $proposal = $this->route('proposal');

        return $proposal && PkmPermissions::canEdit($this->user(), $proposal);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return PkmValidationRules::proposal(true);
    }
}
