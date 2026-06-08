<?php

namespace App\Http\Requests\Letter;

use App\Models\Lppm\LetterType;
use App\Support\Letter\LetterPermissions;
use App\Support\Letter\LetterValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreLetterRequest extends FormRequest
{
    public function authorize(): bool
    {
        $type = LetterType::query()->find($this->input('letter_type_id'));
        $code = $type?->code;

        return LetterPermissions::canCreateType($this->user(), $code);
    }

    public function rules(): array
    {
        $type = LetterType::query()->find($this->input('letter_type_id'));

        return LetterValidationRules::rulesForType($type);
    }
}
