<?php

namespace App\Models\Letter;

use Illuminate\Database\Eloquent\Model;

class LetterNumberSequence extends Model
{
    protected $table = 'lppm_letter_number_sequences';

    protected $fillable = ['letter_prefix', 'year', 'last_sequence'];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'last_sequence' => 'integer',
        ];
    }
}
