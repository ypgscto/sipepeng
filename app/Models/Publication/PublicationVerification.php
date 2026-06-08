<?php

namespace App\Models\Publication;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicationVerification extends Model
{
    protected $table = 'lppm_publication_verifications';

    protected $fillable = [
        'publication_id', 'verifier_user_id', 'decision', 'is_document_complete', 'notes', 'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'is_document_complete' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class, 'publication_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifier_user_id');
    }
}
