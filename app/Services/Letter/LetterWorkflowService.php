<?php

namespace App\Services\Letter;

use App\Models\Letter\Letter;
use App\Models\Letter\LetterApproval;
use App\Models\Letter\LetterStatusHistory;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LetterWorkflowService
{
    public function __construct(
        protected ActivityLogger $logger,
        protected LetterNumberService $numberService,
        protected LetterPdfService $pdfService,
    ) {}

    public function transition(Letter $letter, string $action, ?string $notes = null): Letter
    {
        $transitions = config('sipepeng_letters.transitions.'.$letter->status, []);
        if (! isset($transitions[$action])) {
            throw new InvalidArgumentException("Transisi '{$action}' tidak valid untuk status '{$letter->status}'.");
        }

        $toStatus = $transitions[$action];
        $fromStatus = $letter->status;
        $stage = config('sipepeng_letters.statuses.'.$toStatus.'.stage', $letter->current_stage);

        return DB::transaction(function () use ($letter, $action, $fromStatus, $toStatus, $stage, $notes): Letter {
            $updates = ['status' => $toStatus, 'current_stage' => $stage, 'updated_by' => auth()->id()];
            if ($toStatus === 'approved') {
                $updates['approved_at'] = now();
            }
            if ($toStatus === 'issued') {
                $updates['issued_at'] = now();
            }
            $letter->update($updates);

            LetterStatusHistory::query()->create([
                'letter_id' => $letter->id,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'transition' => $action,
                'notes' => $notes,
                'acted_by' => auth()->id(),
                'acted_at' => now(),
            ]);

            $this->logger->logStatusChange($letter, $fromStatus, $toStatus, $action, $notes, 'lppm_letter');

            return $letter->fresh();
        });
    }

    public function submit(Letter $letter): Letter
    {
        $letter->update(['submitted_at' => now(), 'updated_by' => auth()->id()]);
        $letter = $this->transition($letter, 'submit', 'Surat diajukan.');
        $this->logger->log('submitted', $letter, 'Surat diajukan.', logName: 'lppm_letter');

        if ($letter->letterType?->requires_approval) {
            return $this->transition($letter, 'start_approval', 'Masuk antrian persetujuan.');
        }

        return $this->transition($letter, 'auto_approve', 'Disetujui otomatis (tanpa persetujuan).');
    }

    public function recordApproval(Letter $letter, string $decision, ?string $notes): Letter
    {
        LetterApproval::query()->create([
            'letter_id' => $letter->id,
            'approver_user_id' => auth()->id(),
            'decision' => $decision,
            'notes' => $notes,
            'approved_at' => now(),
        ]);

        $action = match ($decision) {
            'approved' => 'approve',
            'rejected' => 'reject',
            'revision_required' => 'request_revision',
            default => throw new InvalidArgumentException('Keputusan persetujuan tidak valid.'),
        };

        if ($decision === 'revision_required') {
            $letter->increment('revision_count');
        }

        $this->logger->log('approval_recorded', $letter, 'Persetujuan surat dicatat.', ['decision' => $decision], logName: 'lppm_letter');

        return $this->transition($letter, $action, $notes);
    }

    public function issue(Letter $letter): Letter
    {
        return DB::transaction(function () use ($letter): Letter {
            $officialNumber = $this->numberService->assignOfficialNumber($letter);
            $letter->update([
                'letter_number' => $officialNumber,
                'updated_by' => auth()->id(),
            ]);

            $letter = $this->transition($letter->fresh(), 'issue', 'Surat diterbitkan.');
            $this->pdfService->generateAndStore($letter, watermark: false);
            $this->logger->log('issued', $letter, 'Surat diterbitkan.', ['letter_number' => $officialNumber], logName: 'lppm_letter');

            return $letter->fresh();
        });
    }

    public static function generateInternalNumber(): string
    {
        $year = now()->format('Y');
        $pattern = config('sipepeng_letters.draft_number_pattern', 'DRAFT/SRT/{year}/{seq:04d}');
        $count = Letter::query()->withTrashed()->whereYear('created_at', $year)->count() + 1;

        return str_replace(
            ['{year}', '{seq:04d}', '{seq}'],
            [$year, str_pad((string) $count, 4, '0', STR_PAD_LEFT), (string) $count],
            $pattern,
        );
    }
}
