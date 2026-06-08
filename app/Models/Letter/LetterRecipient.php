<?php

namespace App\Models\Letter;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LetterRecipient extends Model
{
    protected $table = 'lppm_letter_recipients';

    protected $fillable = [
        'letter_id', 'recipient_type', 'name', 'email', 'institution',
        'dosen_id', 'dosen_nama_snapshot', 'user_id', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['sort_order' => 'integer'];
    }

    public function letter(): BelongsTo
    {
        return $this->belongsTo(Letter::class, 'letter_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
