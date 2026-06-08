<?php

namespace App\Models\Letter;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LetterStatusHistory extends Model
{
    protected $table = 'lppm_letter_status_histories';

    protected $fillable = [
        'letter_id', 'from_status', 'to_status', 'transition', 'notes',
        'acted_by', 'acted_at', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'acted_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function letter(): BelongsTo
    {
        return $this->belongsTo(Letter::class, 'letter_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acted_by');
    }
}
