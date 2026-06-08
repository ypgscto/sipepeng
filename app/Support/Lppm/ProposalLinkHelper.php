<?php

namespace App\Support\Lppm;

use App\Models\CommunityService\CommunityServiceProposal;
use App\Models\Research\ResearchProposal;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ProposalLinkHelper
{
    /**
     * @return Builder<ResearchProposal>
     */
    public static function linkableResearchQuery(User $user): Builder
    {
        $statuses = config('sipepeng_publication.linkable_proposal_statuses', ['approved']);

        return ResearchProposal::query()
            ->visibleTo($user)
            ->whereIn('status', $statuses)
            ->orderByDesc('updated_at');
    }

    /**
     * @return Builder<CommunityServiceProposal>
     */
    public static function linkablePkmQuery(User $user): Builder
    {
        $statuses = config('sipepeng_publication.linkable_proposal_statuses', ['approved']);

        return CommunityServiceProposal::query()
            ->visibleTo($user)
            ->whereIn('status', $statuses)
            ->orderByDesc('updated_at');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function resolveSourceFields(array $data): array
    {
        $sourceType = $data['source_type'] ?? 'standalone';

        if ($sourceType === 'research' && ! empty($data['research_proposal_id'])) {
            $proposal = ResearchProposal::query()->find($data['research_proposal_id']);
            if ($proposal) {
                $data['proposal_number_snapshot'] = $proposal->proposal_number;
                $data['proposal_judul_snapshot'] = $proposal->judul;
                $data['community_service_proposal_id'] = null;
            }
        } elseif ($sourceType === 'community_service' && ! empty($data['community_service_proposal_id'])) {
            $proposal = CommunityServiceProposal::query()->find($data['community_service_proposal_id']);
            if ($proposal) {
                $data['proposal_number_snapshot'] = $proposal->proposal_number;
                $data['proposal_judul_snapshot'] = $proposal->judul;
                $data['research_proposal_id'] = null;
            }
        } else {
            $data['source_type'] = 'standalone';
            $data['research_proposal_id'] = null;
            $data['community_service_proposal_id'] = null;
            $data['proposal_number_snapshot'] = null;
            $data['proposal_judul_snapshot'] = null;
        }

        return $data;
    }
}
