<?php

namespace App\Models\ResearchEthics;

use App\Models\Lppm\Reviewer;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResearchEthicsReview extends Model
{
    protected $table = 'lppm_research_ethics_reviews';

    protected $fillable = [
        'ethics_application_id', 'reviewer_id', 'reviewer_user_id', 'assigned_by',
        'assigned_at', 'decision', 'notes', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(ResearchEthicsApplication::class, 'ethics_application_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Reviewer::class, 'reviewer_id');
    }

    public function reviewerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_user_id');
    }
}
