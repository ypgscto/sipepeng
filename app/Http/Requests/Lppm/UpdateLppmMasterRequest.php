<?php

namespace App\Http\Requests\Lppm;

use App\Http\Requests\Lppm\Concerns\ResolvesLppmEntity;
use App\Support\Lppm\LppmMasterAccess;
use App\Support\Lppm\LppmValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLppmMasterRequest extends FormRequest
{
    use ResolvesLppmEntity;

    public function authorize(): bool
    {
        return LppmMasterAccess::canManage();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return LppmValidationRules::for($this->entityKey(), $this->recordId());
    }
}
