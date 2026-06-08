<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ActivityLogger
{
    public function log(
        string $event,
        Model $subject,
        ?string $description = null,
        ?array $properties = null,
        ?Request $request = null,
        string $logName = 'lppm_master',
    ): void {
        $this->write(
            event: $event,
            logName: $logName,
            description: $description,
            subject: $subject,
            properties: $properties,
            request: $request,
        );
    }

    public function logAudit(
        string $event,
        ?Model $subject = null,
        ?string $description = null,
        ?array $properties = null,
        ?Request $request = null,
        string $logName = 'security',
        ?int $causerId = null,
    ): void {
        $this->write(
            event: $event,
            logName: $logName,
            description: $description,
            subject: $subject,
            properties: $properties,
            request: $request,
            causerId: $causerId ?? auth()->id(),
        );
    }

    /**
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     */
    public function logCrud(
        string $event,
        Model $subject,
        ?array $before = null,
        ?array $after = null,
        ?string $description = null,
        string $logName = 'lppm_master',
        ?Request $request = null,
    ): void {
        $properties = [];
        if ($before !== null) {
            $properties['before'] = $this->redact($before);
        }
        if ($after !== null) {
            $properties['after'] = $this->redact($after);
        }

        $this->log($event, $subject, $description, $properties ?: null, $request, $logName);
    }

    public function logStatusChange(
        Model $subject,
        string $from,
        string $to,
        string $action,
        ?string $notes = null,
        string $logName = 'lppm_master',
        ?Request $request = null,
    ): void {
        $this->log('status_changed', $subject, 'Status diubah.', [
            'from' => $from,
            'to' => $to,
            'action' => $action,
            'notes' => $notes,
        ], $request, $logName);

        if ($to === 'revision_required' || $action === 'request_revision') {
            $this->log('revision_requested', $subject, 'Revisi diminta.', [
                'from' => $from,
                'notes' => $notes,
            ], $request, $logName);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function redact(array $data): array
    {
        foreach (config('sipepeng_activity.sensitive_fields', []) as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = '[redacted]';
            }
        }

        return $data;
    }

    protected function write(
        string $event,
        string $logName,
        ?string $description = null,
        ?Model $subject = null,
        ?array $properties = null,
        ?Request $request = null,
        ?int $causerId = null,
    ): void {
        $request ??= request();

        ActivityLog::query()->create([
            'log_name' => $logName,
            'event' => $event,
            'description' => $description,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'causer_id' => $causerId,
            'properties' => $properties !== null ? $this->redact($properties) : null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'created_at' => now(),
        ]);
    }
}
