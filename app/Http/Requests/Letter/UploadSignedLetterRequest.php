<?php

namespace App\Http\Requests\Letter;

use App\Models\Letter\Letter;
use App\Support\Letter\LetterPermissions;
use App\Support\Letter\LetterValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class UploadSignedLetterRequest extends FormRequest
{
    public function authorize(): bool
    {
        $letter = $this->route('letter');
        if (! $letter instanceof Letter) {
            return false;
        }

        return LetterPermissions::canUploadSigned($this->user(), $letter);
    }

    public function rules(): array
    {
        return LetterValidationRules::signedScanRules();
    }
}
