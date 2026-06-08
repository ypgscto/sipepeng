<?php

namespace App\Models\Research;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResearchAdminVerification extends Model
{
    protected $table = 'lppm_research_admin_verifications';

    protected $fillable = [
        'research_proposal_id', 'verifier_user_id', 'decision',
        'is_document_complete', 'notes', 'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'is_document_complete' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(ResearchProposal::class, 'research_proposal_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifier_user_id');
    }
}
