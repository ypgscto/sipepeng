<?php

namespace App\Models\Report;

use App\Models\CommunityService\CommunityServiceProposal;
use App\Models\Research\ResearchProposal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProposalTeamMember extends Model
{
    protected $table = 'lppm_proposal_team_members';

    protected $fillable = [
        'activity_type', 'research_proposal_id', 'community_service_proposal_id',
        'member_type', 'mahasiswa_id', 'mahasiswa_nama_snapshot',
        'dosen_id', 'dosen_nama_snapshot', 'prodi_id', 'prodi_nama_snapshot',
        'role_label', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['sort_order' => 'integer'];
    }

    public function researchProposal(): BelongsTo
    {
        return $this->belongsTo(ResearchProposal::class, 'research_proposal_id');
    }

    public function communityServiceProposal(): BelongsTo
    {
        return $this->belongsTo(CommunityServiceProposal::class, 'community_service_proposal_id');
    }
}
