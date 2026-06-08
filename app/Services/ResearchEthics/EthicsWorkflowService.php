<?php

namespace App\Services\ResearchEthics;

use App\Models\ResearchEthics\ResearchEthicsApplication;
use App\Models\ResearchEthics\ResearchEthicsReview;
use App\Models\ResearchEthics\ResearchEthicsStatusHistory;
use App\Models\Lppm\Reviewer;
use App\Services\ActivityLogger;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class EthicsWorkflowService
{
    public function __construct(
        protected ActivityLogger $logger,
        protected NotificationService $notifications,
    ) {}

    public function transition(ResearchEthicsApplication $application, string $action, ?string $notes = null, ?array $metadata = null): ResearchEthicsApplication
    {
        $transitions = config('sipepeng_ethics.transitions.'.$application->status, []);
        if (! isset($transitions[$action])) {
            throw new InvalidArgumentException("Transisi '{$action}' tidak valid untuk status '{$application->status}'.");
        }

        $toStatus = $transitions[$action];
        $fromStatus = $application->status;
        $stage = config('sipepeng_ethics.statuses.'.$toStatus.'.stage', $application->current_stage);

        return DB::transaction(function () use ($application, $action, $fromStatus, $toStatus, $stage, $notes, $metadata): ResearchEthicsApplication {
            $updates = ['status' => $toStatus, 'current_stage' => $stage, 'updated_by' => auth()->id()];
            if ($toStatus === 'approved') {
                $updates['approved_at'] = now();
                if (! $application->valid_from) {
                    $updates['valid_from'] = now()->toDateString();
                }
                if (! $application->valid_until) {
                    $updates['valid_until'] = now()->addYear()->toDateString();
                }
            }
            $application->update($updates);

            ResearchEthicsStatusHistory::query()->create([
                'ethics_application_id' => $application->id,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'transition' => $action,
                'notes' => $notes,
                'acted_by' => auth()->id(),
                'acted_at' => now(),
                'metadata' => $metadata,
            ]);

            $this->logger->logStatusChange($application, $fromStatus, $toStatus, $action, $notes, 'lppm_ethics');

            return $application->fresh();
        });
    }

    public function submit(ResearchEthicsApplication $application): ResearchEthicsApplication
    {
        $application->update(['submitted_at' => now(), 'updated_by' => auth()->id()]);
        $application = $this->transition($application, 'submit', 'Aplikasi etik diajukan.');
        $this->logger->log('submitted', $application, 'Aplikasi etik diajukan.', logName: 'lppm_ethics');

        return $this->transition($application, 'start_committee_review', 'Masuk review komite etik.');
    }

    public function assignReviewer(ResearchEthicsApplication $application, int $reviewerId): ResearchEthicsReview
    {
        $reviewer = Reviewer::query()->where('is_active', true)->with('user')->findOrFail($reviewerId);

        $review = ResearchEthicsReview::query()->create([
            'ethics_application_id' => $application->id,
            'reviewer_id' => $reviewer->id,
            'reviewer_user_id' => $reviewer->user_id,
            'assigned_by' => auth()->id(),
            'assigned_at' => now(),
        ]);

        $this->logger->log('reviewer_assigned', $application, 'Reviewer etik ditugaskan.', [
            'reviewer_id' => $reviewer->id,
        ], logName: 'lppm_ethics');

        $this->notifications->notifyEthicsReviewerAssigned($application, $reviewer);

        return $review;
    }

    public function decide(ResearchEthicsApplication $application, string $decision, ?string $notes = null, ?string $validUntil = null): ResearchEthicsApplication
    {
        if ($decision === 'approve' && $validUntil) {
            $application->update(['valid_until' => $validUntil]);
        }

        $action = match ($decision) {
            'approve' => 'approve',
            'reject' => 'reject',
            'revision_required' => 'request_revision',
            default => throw new InvalidArgumentException('Keputusan tidak valid.'),
        };

        if ($decision === 'revision_required') {
            $application->increment('revision_count');
        }

        $this->logger->log('decision', $application, 'Keputusan etik penelitian.', ['decision' => $decision], logName: 'lppm_ethics');

        $application = $this->transition($application, $action, $notes);

        if ($decision === 'revision_required') {
            $this->notifications->notifyEthicsRevision($application, $notes);
        } elseif (in_array($decision, ['approve', 'reject'], true)) {
            $this->notifications->notifyEthicsDecision($application, $decision, $notes);
        }

        return $application;
    }

    public static function generateApplicationNumber(): string
    {
        $year = now()->format('Y');
        $count = ResearchEthicsApplication::query()->withTrashed()->whereYear('created_at', $year)->count() + 1;

        return sprintf('ETB/%s/%04d', $year, $count);
    }
}
