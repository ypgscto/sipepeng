<?php

namespace App\Services\CommunityService;

use App\Models\CommunityService\CommunityServiceProposal;
use App\Models\CommunityService\PkmAdminVerification;
use App\Models\CommunityService\PkmReview;
use App\Models\CommunityService\PkmStatusHistory;
use App\Models\Lppm\Reviewer;
use App\Services\ActivityLogger;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PkmWorkflowService
{
    public function __construct(
        protected ActivityLogger $logger,
        protected NotificationService $notifications,
    ) {}

    public function transition(CommunityServiceProposal $proposal, string $action, ?string $notes = null, ?array $metadata = null): CommunityServiceProposal
    {
        $transitions = config('sipepeng_community_service.transitions.'.$proposal->status, []);
        if (! isset($transitions[$action])) {
            throw new InvalidArgumentException("Transisi '{$action}' tidak valid untuk status '{$proposal->status}'.");
        }

        $toStatus = $transitions[$action];
        $fromStatus = $proposal->status;
        $stage = config('sipepeng_community_service.statuses.'.$toStatus.'.stage', $proposal->current_stage);

        return DB::transaction(function () use ($proposal, $action, $fromStatus, $toStatus, $stage, $notes, $metadata): CommunityServiceProposal {
            $proposal->update([
                'status' => $toStatus,
                'current_stage' => $stage,
                'updated_by' => auth()->id(),
            ]);

            PkmStatusHistory::query()->create([
                'community_service_proposal_id' => $proposal->id,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'transition' => $action,
                'notes' => $notes,
                'acted_by' => auth()->id(),
                'acted_at' => now(),
                'metadata' => $metadata,
            ]);

            $this->logger->logStatusChange($proposal, $fromStatus, $toStatus, $action, $notes, 'lppm_pkm');

            return $proposal->fresh();
        });
    }

    public function submit(CommunityServiceProposal $proposal): CommunityServiceProposal
    {
        $proposal->update([
            'submitted_at' => now(),
            'updated_by' => auth()->id(),
        ]);

        $proposal = $this->transition($proposal, 'submit', 'Proposal diajukan oleh ketua pelaksana.');
        $this->logger->log('submitted', $proposal, 'Proposal PkM diajukan.', logName: 'lppm_pkm');

        $proposal = $this->transition($proposal, 'start_admin_review', 'Masuk antrian verifikasi administrasi.');
        $this->notifications->notifyPkmSubmitted($proposal);

        return $proposal;
    }

    public function recordAdminVerification(
        CommunityServiceProposal $proposal,
        string $decision,
        bool $isDocumentComplete,
        bool $isPartnerVerified,
        ?string $notes,
    ): CommunityServiceProposal {
        PkmAdminVerification::query()->create([
            'community_service_proposal_id' => $proposal->id,
            'verifier_user_id' => auth()->id(),
            'decision' => $decision,
            'is_document_complete' => $isDocumentComplete,
            'is_partner_verified' => $isPartnerVerified,
            'notes' => $notes,
            'verified_at' => now(),
        ]);

        $action = match ($decision) {
            'verified' => 'verify',
            'rejected' => 'reject',
            'revision_required' => 'request_revision',
            default => throw new InvalidArgumentException('Keputusan verifikasi tidak valid.'),
        };

        if ($decision === 'revision_required') {
            $proposal->increment('revision_count');
        }

        $this->logger->log('admin_verification', $proposal, 'Verifikasi administrasi PkM dicatat.', [
            'decision' => $decision,
            'is_partner_verified' => $isPartnerVerified,
        ], logName: 'lppm_pkm');

        $proposal = $this->transition($proposal, $action, $notes);

        if ($decision === 'revision_required') {
            $this->notifications->notifyPkmRevision($proposal, $notes);
        } elseif ($decision === 'rejected') {
            $this->notifications->notifyPkmDecision($proposal, 'reject', $notes);
        }

        return $proposal;
    }

    public function assignReviewer(CommunityServiceProposal $proposal, int $reviewerId): PkmReview
    {
        $reviewer = Reviewer::query()->where('is_active', true)->with('user')->findOrFail($reviewerId);

        $review = PkmReview::query()->create([
            'community_service_proposal_id' => $proposal->id,
            'reviewer_id' => $reviewer->id,
            'assigned_by' => auth()->id(),
            'assigned_at' => now(),
            'status' => 'assigned',
        ]);

        $this->transition($proposal, 'assign_review', 'Reviewer ditugaskan.');

        $this->logger->log('reviewer_assigned', $proposal, 'Reviewer PkM ditugaskan.', [
            'reviewer_id' => $reviewer->id,
            'user_id' => $reviewer->user_id,
        ], logName: 'lppm_pkm');

        $this->notifications->notifyPkmReviewerAssigned($proposal, $reviewer);

        return $review;
    }

    public function submitReview(PkmReview $review, string $recommendation, float $score, ?string $summary): CommunityServiceProposal
    {
        $review->update([
            'status' => 'submitted',
            'recommendation' => $recommendation,
            'overall_score' => $score,
            'summary' => $summary,
            'submitted_at' => now(),
        ]);

        $proposal = $review->proposal;

        $this->logger->log('review_submitted', $proposal, 'Review proposal PkM disubmit.', [
            'review_id' => $review->id,
            'recommendation' => $recommendation,
            'score' => $score,
        ], logName: 'lppm_pkm');

        if ($proposal->reviews()->where('status', '!=', 'submitted')->doesntExist()) {
            return $this->transition($proposal, 'complete_review', 'Semua review selesai.');
        }

        return $proposal->fresh();
    }

    public function decide(CommunityServiceProposal $proposal, string $decision, ?string $notes = null): CommunityServiceProposal
    {
        $action = $decision === 'approve' ? 'approve' : 'reject';

        $this->logger->log('funding_decided', $proposal, 'Keputusan penetapan PkM.', [
            'decision' => $decision,
        ], logName: 'lppm_pkm');

        $proposal = $this->transition($proposal, $action, $notes);
        $this->notifications->notifyPkmDecision($proposal, $decision, $notes);

        return $proposal;
    }

    public static function generateProposalNumber(): string
    {
        $year = now()->format('Y');
        $count = CommunityServiceProposal::query()->withTrashed()->whereYear('created_at', $year)->count() + 1;

        return sprintf('PKM/%s/%04d', $year, $count);
    }
}
