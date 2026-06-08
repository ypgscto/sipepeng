<?php

namespace App\Support\Lppm;

use App\Models\User;

class LppmMasterAccess
{
    public static function canManage(?User $user = null): bool
    {
        $user ??= auth()->user();

        if ($user === null) {
            return false;
        }

        return $user->hasAnyRole(config('sipepeng_master.manage_roles', []));
    }

    public static function canView(?User $user = null): bool
    {
        $user ??= auth()->user();

        if ($user === null) {
            return false;
        }

        return $user->hasAnyRole(config('sipepeng_master.view_roles', []));
    }
}
