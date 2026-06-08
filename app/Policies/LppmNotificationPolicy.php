<?php

namespace App\Policies;

use App\Models\Notification\LppmNotification;
use App\Models\User;

class LppmNotificationPolicy
{
    public function view(User $user, LppmNotification $notification): bool
    {
        return $notification->user_id === $user->id;
    }

    public function update(User $user, LppmNotification $notification): bool
    {
        return $notification->user_id === $user->id;
    }
}
