<?php

namespace App\Models\IntellectualProperty;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IpVerification extends Model
{
    protected $table = 'lppm_ip_verifications';

    protected $fillable = [
        'ip_registration_id', 'verifier_user_id', 'decision', 'is_document_complete', 'notes', 'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'is_document_complete' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    public function ipRegistration(): BelongsTo
    {
        return $this->belongsTo(IpRegistration::class, 'ip_registration_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifier_user_id');
    }
}
