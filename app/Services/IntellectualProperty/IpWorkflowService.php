<?php

namespace App\Services\IntellectualProperty;

use App\Models\IntellectualProperty\IpRegistration;
use App\Models\IntellectualProperty\IpStatusHistory;
use App\Models\IntellectualProperty\IpVerification;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class IpWorkflowService
{
    public function __construct(protected ActivityLogger $logger) {}

    public function transition(IpRegistration $registration, string $action, ?string $notes = null): IpRegistration
    {
        $transitions = config('sipepeng_hki.transitions.'.$registration->status, []);
        if (! isset($transitions[$action])) {
            throw new InvalidArgumentException("Transisi '{$action}' tidak valid untuk status '{$registration->status}'.");
        }

        $toStatus = $transitions[$action];
        $fromStatus = $registration->status;
        $stage = config('sipepeng_hki.statuses.'.$toStatus.'.stage', $registration->current_stage);

        return DB::transaction(function () use ($registration, $action, $fromStatus, $toStatus, $stage, $notes): IpRegistration {
            $updates = ['status' => $toStatus, 'current_stage' => $stage, 'updated_by' => auth()->id()];
            if ($toStatus === 'verified') {
                $updates['verified_at'] = now();
            }
            if ($toStatus === 'registered') {
                $updates['registration_date'] = $registration->registration_date ?? now();
            }
            $registration->update($updates);

            IpStatusHistory::query()->create([
                'ip_registration_id' => $registration->id,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'transition' => $action,
                'notes' => $notes,
                'acted_by' => auth()->id(),
                'acted_at' => now(),
            ]);

            $this->logger->logStatusChange($registration, $fromStatus, $toStatus, $action, $notes, 'lppm_hki');

            return $registration->fresh();
        });
    }

    public function submit(IpRegistration $registration): IpRegistration
    {
        $registration->update(['submitted_at' => now(), 'updated_by' => auth()->id()]);
        $registration = $this->transition($registration, 'submit', 'Pendaftaran HKI diajukan.');
        $this->logger->log('submitted', $registration, 'HKI diajukan.', logName: 'lppm_hki');

        return $this->transition($registration, 'start_admin_review', 'Masuk antrian verifikasi.');
    }

    public function recordVerification(IpRegistration $registration, string $decision, bool $isDocumentComplete, ?string $notes): IpRegistration
    {
        IpVerification::query()->create([
            'ip_registration_id' => $registration->id,
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
            $registration->increment('revision_count');
        }

        $this->logger->log('admin_verification', $registration, 'Verifikasi HKI dicatat.', ['decision' => $decision], logName: 'lppm_hki');

        $registration = $this->transition($registration, $action, $notes);

        if ($decision === 'verified') {
            return $this->transition($registration, 'confirm_registered', 'HKI terdaftar.');
        }

        return $registration;
    }

    public static function generateRegistrationNumber(): string
    {
        $year = now()->format('Y');
        $count = IpRegistration::query()->withTrashed()->whereYear('created_at', $year)->count() + 1;

        return sprintf('HKI/%s/%04d', $year, $count);
    }
}
