<?php

namespace App\Services\Publication;

use App\Models\Publication\Publication;
use App\Models\Publication\PublicationStatusHistory;
use App\Models\Publication\PublicationVerification;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PublicationWorkflowService
{
    public function __construct(protected ActivityLogger $logger) {}

    public function transition(Publication $publication, string $action, ?string $notes = null): Publication
    {
        $transitions = config('sipepeng_publication.transitions.'.$publication->status, []);
        if (! isset($transitions[$action])) {
            throw new InvalidArgumentException("Transisi '{$action}' tidak valid untuk status '{$publication->status}'.");
        }

        $toStatus = $transitions[$action];
        $fromStatus = $publication->status;
        $stage = config('sipepeng_publication.statuses.'.$toStatus.'.stage', $publication->current_stage);

        return DB::transaction(function () use ($publication, $action, $fromStatus, $toStatus, $stage, $notes): Publication {
            $updates = ['status' => $toStatus, 'current_stage' => $stage, 'updated_by' => auth()->id()];
            if ($toStatus === 'verified') {
                $updates['verified_at'] = now();
            }
            $publication->update($updates);

            PublicationStatusHistory::query()->create([
                'publication_id' => $publication->id,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'transition' => $action,
                'notes' => $notes,
                'acted_by' => auth()->id(),
                'acted_at' => now(),
            ]);

            $this->logger->logStatusChange($publication, $fromStatus, $toStatus, $action, $notes, 'lppm_publication');

            return $publication->fresh();
        });
    }

    public function submit(Publication $publication): Publication
    {
        $publication->update(['submitted_at' => now(), 'updated_by' => auth()->id()]);
        $publication = $this->transition($publication, 'submit', 'Publikasi diajukan.');
        $this->logger->log('submitted', $publication, 'Publikasi diajukan.', logName: 'lppm_publication');

        return $this->transition($publication, 'start_admin_review', 'Masuk antrian verifikasi.');
    }

    public function recordVerification(Publication $publication, string $decision, bool $isDocumentComplete, ?string $notes): Publication
    {
        PublicationVerification::query()->create([
            'publication_id' => $publication->id,
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
            $publication->increment('revision_count');
        }

        $this->logger->log('admin_verification', $publication, 'Verifikasi publikasi dicatat.', ['decision' => $decision], logName: 'lppm_publication');

        return $this->transition($publication, $action, $notes);
    }

    public static function generateRegistrationNumber(): string
    {
        $year = now()->format('Y');
        $count = Publication::query()->withTrashed()->whereYear('created_at', $year)->count() + 1;

        return sprintf('PUB/%s/%04d', $year, $count);
    }
}
