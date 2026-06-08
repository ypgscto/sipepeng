<?php

namespace App\Models\Research;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResearchProposalStatusHistory extends Model
{
    public $timestamps = false;

    protected $table = 'lppm_research_status_histories';

    protected $fillable = [
        'research_proposal_id', 'from_status', 'to_status', 'transition',
        'notes', 'acted_by', 'acted_at', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'acted_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(ResearchProposal::class, 'research_proposal_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acted_by');
    }
}
