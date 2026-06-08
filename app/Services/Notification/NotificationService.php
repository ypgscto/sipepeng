<?php

namespace App\Services\Notification;

use App\Models\CommunityService\CommunityServiceProposal;
use App\Models\Lppm\Reviewer;
use App\Models\Notification\LppmNotification;
use App\Models\Research\ResearchProposal;
use App\Models\ResearchEthics\ResearchEthicsApplication;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

class NotificationService
{
    public function __construct(protected ActivityLogger $activityLogger) {}

    /**
     * @param  User|iterable<User>  $recipients
     */
    public function send(
        User|iterable $recipients,
        string $type,
        string $body,
        ?Model $notifiable = null,
        ?string $actionUrl = null,
        ?string $actionLabel = null,
        ?string $dedupeKey = null,
        ?array $payload = null,
    ): void {
        if (! isset(config('sipepeng_notifications.types')[$type])) {
            return;
        }

        $meta = config('sipepeng_notifications.types.'.$type);
        $users = $recipients instanceof User ? collect([$recipients]) : collect($recipients);

        foreach ($users->filter()->unique('id') as $user) {
            if ($dedupeKey && LppmNotification::query()->where('dedupe_key', $dedupeKey.'|'.$user->id)->exists()) {
                continue;
            }

            $notification = LppmNotification::query()->create([
                'user_id' => $user->id,
                'category' => $meta['category'],
                'type' => $type,
                'severity' => $meta['severity'] ?? 'info',
                'title' => $meta['title'],
                'body' => $body,
                'action_url' => $actionUrl,
                'action_label' => $actionLabel,
                'notifiable_type' => $notifiable?->getMorphClass(),
                'notifiable_id' => $notifiable?->getKey(),
                'payload' => $payload,
                'dedupe_key' => $dedupeKey ? $dedupeKey.'|'.$user->id : null,
            ]);

            if ($notifiable) {
                $this->activityLogger->logAudit(
                    'notification_sent',
                    $notifiable,
                    'Notifikasi dikirim.',
                    ['notification_id' => $notification->id, 'type' => $type, 'recipient_id' => $user->id],
                    logName: 'lppm_notification',
                );
            }
        }
    }

    /**
     * @return Collection<int, User>
     */
    public function usersWithRoles(array $roleCodes): Collection
    {
        if ($roleCodes === []) {
            return collect();
        }

        return User::query()
            ->where('is_active', true)
            ->whereHas('activeRoles', fn ($q) => $q->whereIn('code', $roleCodes))
            ->get();
    }

    public function unreadCount(User $user): int
    {
        return LppmNotification::query()->forUser($user->id)->unread()->count();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, LppmNotification>
     */
    public function recent(User $user, int $limit = 5)
    {
        return LppmNotification::query()
            ->forUser($user->id)
            ->inbox()
            ->limit($limit)
            ->get();
    }

    public function markRead(LppmNotification $notification): void
    {
        $notification->markAsRead();
    }

    public function markAllRead(User $user): int
    {
        return LppmNotification::query()
            ->forUser($user->id)
            ->unread()
            ->update(['read_at' => now()]);
    }

    public function dismiss(LppmNotification $notification): void
    {
        $notification->markAsDismissed();
    }

    public function notifyResearchSubmitted(ResearchProposal $proposal): void
    {
        $body = sprintf(
            'Proposal %s — "%s" diajukan oleh %s.',
            $proposal->proposal_number,
            $proposal->judul,
            $proposal->ketua_dosen_nama_snapshot,
        );

        $this->send(
            $this->usersWithRoles(config('sipepeng_notifications.admin_notify_roles', [])),
            'proposal_submitted_research',
            $body,
            $proposal,
            Route::has('admin.research.show') ? route('admin.research.show', $proposal) : null,
            'Lihat Proposal',
            'research_submitted:'.$proposal->id,
            ['proposal_number' => $proposal->proposal_number],
        );
    }

    public function notifyPkmSubmitted(CommunityServiceProposal $proposal): void
    {
        $body = sprintf(
            'Proposal %s — "%s" diajukan oleh %s.',
            $proposal->proposal_number,
            $proposal->judul,
            $proposal->ketua_dosen_nama_snapshot,
        );

        $this->send(
            $this->usersWithRoles(config('sipepeng_notifications.admin_notify_roles', [])),
            'proposal_submitted_pkm',
            $body,
            $proposal,
            Route::has('admin.community-service.show') ? route('admin.community-service.show', $proposal) : null,
            'Lihat Proposal',
            'pkm_submitted:'.$proposal->id,
        );
    }

    public function notifyResearchRevision(ResearchProposal $proposal, ?string $notes): void
    {
        $recipient = $proposal->ketuaUser;
        if (! $recipient) {
            return;
        }

        $body = sprintf(
            'Proposal %s perlu revisi.%s',
            $proposal->proposal_number,
            $notes ? ' Catatan: '.$notes : '',
        );

        $this->send(
            $recipient,
            'proposal_revision_research',
            $body,
            $proposal,
            route('admin.research.edit', $proposal),
            'Perbaiki Proposal',
            'research_revision:'.$proposal->id.':'.($proposal->revision_count ?? 0),
            ['notes' => $notes],
        );
    }

    public function notifyPkmRevision(CommunityServiceProposal $proposal, ?string $notes): void
    {
        $recipient = $proposal->ketuaUser;
        if (! $recipient) {
            return;
        }

        $body = sprintf(
            'Proposal %s perlu revisi.%s',
            $proposal->proposal_number,
            $notes ? ' Catatan: '.$notes : '',
        );

        $this->send(
            $recipient,
            'proposal_revision_pkm',
            $body,
            $proposal,
            route('admin.community-service.edit', $proposal),
            'Perbaiki Proposal',
            'pkm_revision:'.$proposal->id.':'.($proposal->revision_count ?? 0),
            ['notes' => $notes],
        );
    }

    public function notifyEthicsRevision(ResearchEthicsApplication $application, ?string $notes): void
    {
        $recipient = $application->ketuaUser;
        if (! $recipient) {
            return;
        }

        $body = sprintf(
            'Aplikasi etik %s perlu revisi.%s',
            $application->application_number,
            $notes ? ' Catatan: '.$notes : '',
        );

        $this->send(
            $recipient,
            'proposal_revision_ethics',
            $body,
            $application,
            route('admin.research-ethics.show', $application),
            'Lihat Aplikasi',
            'ethics_revision:'.$application->id.':'.($application->revision_count ?? 0),
        );
    }

    public function notifyResearchReviewerAssigned(ResearchProposal $proposal, Reviewer $reviewer): void
    {
        if (! $reviewer->user) {
            return;
        }

        $body = sprintf(
            'Anda ditugaskan mereview proposal %s — "%s".',
            $proposal->proposal_number,
            $proposal->judul,
        );

        $this->send(
            $reviewer->user,
            'reviewer_assigned_research',
            $body,
            $proposal,
            route('admin.research.show', $proposal),
            'Review Proposal',
            'research_review:'.$proposal->id.':reviewer:'.$reviewer->id,
        );
    }

    public function notifyPkmReviewerAssigned(CommunityServiceProposal $proposal, Reviewer $reviewer): void
    {
        if (! $reviewer->user) {
            return;
        }

        $body = sprintf(
            'Anda ditugaskan mereview proposal PkM %s — "%s".',
            $proposal->proposal_number,
            $proposal->judul,
        );

        $this->send(
            $reviewer->user,
            'reviewer_assigned_pkm',
            $body,
            $proposal,
            route('admin.community-service.show', $proposal),
            'Review Proposal',
            'pkm_review:'.$proposal->id.':reviewer:'.$reviewer->id,
        );
    }

    public function notifyEthicsReviewerAssigned(ResearchEthicsApplication $application, Reviewer $reviewer): void
    {
        if (! $reviewer->user) {
            return;
        }

        $body = sprintf(
            'Anda ditugaskan mereview aplikasi etik %s — "%s".',
            $application->application_number,
            $application->proposal_judul_snapshot,
        );

        $this->send(
            $reviewer->user,
            'reviewer_assigned_ethics',
            $body,
            $application,
            route('admin.research-ethics.show', $application),
            'Review Etik',
            'ethics_review:'.$application->id.':reviewer:'.$reviewer->id,
        );
    }

    public function notifyResearchDecision(ResearchProposal $proposal, string $decision, ?string $notes): void
    {
        $recipient = $proposal->ketuaUser;
        if (! $recipient) {
            return;
        }

        $label = $decision === 'approve' ? 'disetujui' : 'ditolak';
        $body = sprintf(
            'Proposal %s telah %s.%s',
            $proposal->proposal_number,
            $label,
            $notes ? ' Catatan: '.$notes : '',
        );

        $this->send(
            $recipient,
            'proposal_decision_research',
            $body,
            $proposal,
            route('admin.research.show', $proposal),
            'Lihat Proposal',
            'research_decision:'.$proposal->id.':'.$decision,
            ['decision' => $decision],
        );
    }

    public function notifyPkmDecision(CommunityServiceProposal $proposal, string $decision, ?string $notes): void
    {
        $recipient = $proposal->ketuaUser;
        if (! $recipient) {
            return;
        }

        $label = $decision === 'approve' ? 'disetujui' : 'ditolak';
        $body = sprintf(
            'Proposal PkM %s telah %s.%s',
            $proposal->proposal_number,
            $label,
            $notes ? ' Catatan: '.$notes : '',
        );

        $this->send(
            $recipient,
            'proposal_decision_pkm',
            $body,
            $proposal,
            route('admin.community-service.show', $proposal),
            'Lihat Proposal',
            'pkm_decision:'.$proposal->id.':'.$decision,
        );
    }

    public function notifyEthicsDecision(ResearchEthicsApplication $application, string $decision, ?string $notes): void
    {
        $recipient = $application->ketuaUser;
        if (! $recipient) {
            return;
        }

        $label = match ($decision) {
            'approve' => 'disetujui',
            'reject' => 'ditolak',
            default => 'memerlukan tindakan',
        };

        $body = sprintf(
            'Aplikasi etik %s: keputusan %s.%s',
            $application->application_number,
            $label,
            $notes ? ' Catatan: '.$notes : '',
        );

        $this->send(
            $recipient,
            'ethics_decision',
            $body,
            $application,
            route('admin.research-ethics.show', $application),
            'Lihat Aplikasi',
            'ethics_decision:'.$application->id.':'.$decision,
        );
    }
}
