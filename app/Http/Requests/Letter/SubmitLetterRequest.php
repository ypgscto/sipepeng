<?php

namespace App\Http\Requests\Letter;

use App\Models\Letter\Letter;
use App\Support\Letter\LetterPermissions;
use Illuminate\Foundation\Http\FormRequest;

class SubmitLetterRequest extends FormRequest
{
    public function authorize(): bool
    {
        $letter = $this->route('letter');
        if (! $letter instanceof Letter) {
            return false;
        }

        return LetterPermissions::canSubmit($this->user(), $letter);
    }

    public function rules(): array
    {
        return [];
    }
}
