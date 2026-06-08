<?php

namespace App\Support\Settings;

use App\Models\User;

class SettingsPermissions
{
    public static function canView(User $user): bool
    {
        return $user->hasAnyRole(config('sipepeng_settings.view_roles', []));
    }

    public static function canManage(User $user): bool
    {
        return $user->hasAnyRole(config('sipepeng_settings.manage_roles', []));
    }

    public static function canBackup(User $user): bool
    {
        return $user->hasAnyRole(config('sipepeng_settings.backup_roles', []));
    }
}
