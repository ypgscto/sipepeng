<?php

namespace App\Models\CommunityService;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PkmAdminVerification extends Model
{
    protected $table = 'lppm_pkm_admin_verifications';

    protected $fillable = [
        'community_service_proposal_id', 'verifier_user_id', 'decision',
        'is_document_complete', 'is_partner_verified', 'notes', 'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'is_document_complete' => 'boolean',
            'is_partner_verified' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(CommunityServiceProposal::class, 'community_service_proposal_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifier_user_id');
    }
}
