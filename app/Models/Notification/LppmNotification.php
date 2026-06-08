<?php

namespace App\Models\Notification;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LppmNotification extends Model
{
    protected $table = 'lppm_notifications';

    protected $fillable = [
        'user_id',
        'category',
        'type',
        'severity',
        'title',
        'body',
        'action_url',
        'action_label',
        'notifiable_type',
        'notifiable_id',
        'payload',
        'dedupe_key',
        'read_at',
        'dismissed_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'read_at' => 'datetime',
            'dismissed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function isDismissed(): bool
    {
        return $this->dismissed_at !== null;
    }

    public function markAsRead(): void
    {
        if (! $this->isRead()) {
            $this->update(['read_at' => now()]);
        }
    }

    public function markAsDismissed(): void
    {
        $this->update(['dismissed_at' => now()]);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at')->whereNull('dismissed_at');
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     */
    public function scopeInbox($query)
    {
        return $query->whereNull('dismissed_at')->orderByDesc('created_at');
    }
}
