<?php

namespace App\Http\Requests\Letter;

use App\Models\Letter\Letter;
use App\Models\Lppm\LetterType;
use App\Support\Letter\LetterPermissions;
use App\Support\Letter\LetterValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLetterRequest extends FormRequest
{
    public function authorize(): bool
    {
        $letter = $this->route('letter');
        if (! $letter instanceof Letter) {
            return false;
        }

        return LetterPermissions::canEdit($this->user(), $letter);
    }

    public function rules(): array
    {
        $typeId = $this->input('letter_type_id', $this->route('letter')?->letter_type_id);
        $type = LetterType::query()->find($typeId);

        return LetterValidationRules::rulesForType($type);
    }
}
