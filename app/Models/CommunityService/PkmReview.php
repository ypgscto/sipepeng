<?php

namespace App\Models\CommunityService;

use App\Models\Lppm\Reviewer;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PkmReview extends Model
{
    protected $table = 'lppm_pkm_reviews';

    protected $fillable = [
        'community_service_proposal_id', 'reviewer_id', 'assigned_by', 'assigned_at',
        'status', 'recommendation', 'overall_score', 'summary', 'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'submitted_at' => 'datetime',
            'overall_score' => 'decimal:2',
        ];
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(CommunityServiceProposal::class, 'community_service_proposal_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Reviewer::class, 'reviewer_id');
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
