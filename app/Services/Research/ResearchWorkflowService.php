<?php

namespace App\Services\Research;

use App\Models\Research\ResearchAdminVerification;
use App\Models\Research\ResearchProposal;
use App\Models\Research\ResearchProposalStatusHistory;
use App\Models\Research\ResearchReview;
use App\Models\Lppm\Reviewer;
use App\Services\ActivityLogger;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ResearchWorkflowService
{
    public function __construct(
        protected ActivityLogger $logger,
        protected NotificationService $notifications,
    ) {}

    public function transition(ResearchProposal $proposal, string $action, ?string $notes = null, ?array $metadata = null): ResearchProposal
    {
        $transitions = config('sipepeng_research.transitions.'.$proposal->status, []);
        if (! isset($transitions[$action])) {
            throw new InvalidArgumentException("Transisi '{$action}' tidak valid untuk status '{$proposal->status}'.");
        }

        $toStatus = $transitions[$action];
        $fromStatus = $proposal->status;
        $stage = config('sipepeng_research.statuses.'.$toStatus.'.stage', $proposal->current_stage);

        return DB::transaction(function () use ($proposal, $action, $fromStatus, $toStatus, $stage, $notes, $metadata): ResearchProposal {
            $proposal->update([
                'status' => $toStatus,
                'current_stage' => $stage,
                'updated_by' => auth()->id(),
            ]);

            ResearchProposalStatusHistory::query()->create([
                'research_proposal_id' => $proposal->id,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'transition' => $action,
                'notes' => $notes,
                'acted_by' => auth()->id(),
                'acted_at' => now(),
                'metadata' => $metadata,
            ]);

            $this->logger->logStatusChange($proposal, $fromStatus, $toStatus, $action, $notes, 'lppm_research');

            return $proposal->fresh();
        });
    }

    public function submit(ResearchProposal $proposal): ResearchProposal
    {
        $proposal->update([
            'submitted_at' => now(),
            'updated_by' => auth()->id(),
        ]);

        $proposal = $this->transition($proposal, 'submit', 'Proposal diajukan oleh ketua peneliti.');
        $this->logger->log('submitted', $proposal, 'Proposal penelitian diajukan.', logName: 'lppm_research');

        $proposal = $this->transition($proposal, 'start_admin_review', 'Masuk antrian verifikasi administrasi.');
        $this->notifications->notifyResearchSubmitted($proposal);

        return $proposal;
    }

    public function recordAdminVerification(
        ResearchProposal $proposal,
        string $decision,
        bool $isDocumentComplete,
        ?string $notes,
    ): ResearchProposal {
        ResearchAdminVerification::query()->create([
            'research_proposal_id' => $proposal->id,
            'verifier_user_id' => auth()->id(),
            'decision' => $decision,
            'is_document_complete' => $isDocumentComplete,
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

        $this->logger->log('admin_verification', $proposal, 'Verifikasi administrasi dicatat.', [
            'decision' => $decision,
        ], logName: 'lppm_research');

        $proposal = $this->transition($proposal, $action, $notes);

        if ($decision === 'revision_required') {
            $this->notifications->notifyResearchRevision($proposal, $notes);
        } elseif ($decision === 'rejected') {
            $this->notifications->notifyResearchDecision($proposal, 'reject', $notes);
        }

        return $proposal;
    }

    public function assignReviewer(ResearchProposal $proposal, int $reviewerId): ResearchReview
    {
        $reviewer = Reviewer::query()->where('is_active', true)->with('user')->findOrFail($reviewerId);

        $review = ResearchReview::query()->create([
            'research_proposal_id' => $proposal->id,
            'reviewer_id' => $reviewer->id,
            'assigned_by' => auth()->id(),
            'assigned_at' => now(),
            'status' => 'assigned',
        ]);

        $this->transition($proposal, 'assign_review', 'Reviewer ditugaskan.');

        $this->logger->log('reviewer_assigned', $proposal, 'Reviewer ditugaskan.', [
            'reviewer_id' => $reviewer->id,
            'user_id' => $reviewer->user_id,
        ], logName: 'lppm_research');

        $this->notifications->notifyResearchReviewerAssigned($proposal, $reviewer);

        return $review;
    }

    public function submitReview(ResearchReview $review, string $recommendation, float $score, ?string $summary): ResearchProposal
    {
        $review->update([
            'status' => 'submitted',
            'recommendation' => $recommendation,
            'overall_score' => $score,
            'summary' => $summary,
            'submitted_at' => now(),
        ]);

        $proposal = $review->proposal;

        $this->logger->log('review_submitted', $proposal, 'Review proposal disubmit.', [
            'review_id' => $review->id,
            'recommendation' => $recommendation,
            'score' => $score,
        ], logName: 'lppm_research');

        if ($proposal->reviews()->where('status', '!=', 'submitted')->doesntExist()) {
            return $this->transition($proposal, 'complete_review', 'Semua review selesai.');
        }

        return $proposal->fresh();
    }

    public function decide(ResearchProposal $proposal, string $decision, ?string $notes = null): ResearchProposal
    {
        $action = $decision === 'approve' ? 'approve' : 'reject';

        $this->logger->log('funding_decided', $proposal, 'Keputusan penetapan penelitian.', [
            'decision' => $decision,
        ], logName: 'lppm_research');

        $proposal = $this->transition($proposal, $action, $notes);
        $this->notifications->notifyResearchDecision($proposal, $decision, $notes);

        return $proposal;
    }

    public static function generateProposalNumber(): string
    {
        $year = now()->format('Y');
        $count = ResearchProposal::query()->withTrashed()->whereYear('created_at', $year)->count() + 1;

        return sprintf('PNL/%s/%04d', $year, $count);
    }
}
