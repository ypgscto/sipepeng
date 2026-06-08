<?php

namespace App\Support\IntellectualProperty;

use App\Models\IntellectualProperty\IpRegistration;
use App\Models\User;

class IpPermissions
{
    public static function canViewAny(User $user): bool
    {
        return $user->hasAnyRole(config('sipepeng_hki.view_all_roles', []));
    }

    public static function canView(User $user, IpRegistration $registration): bool
    {
        if (self::canViewAny($user)) {
            return true;
        }

        if ($user->hasRole('dosen')) {
            return $registration->created_by === $user->id
                || $registration->inventors()->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                    if (filled($user->siakad_login)) {
                        $q->orWhere('dosen_id', $user->siakad_login);
                    }
                    if (filled($user->siakad_user_id)) {
                        $q->orWhere('dosen_id', $user->siakad_user_id);
                    }
                })->exists();
        }

        return false;
    }

    public static function canCreate(User $user): bool
    {
        return $user->hasAnyRole(config('sipepeng_hki.proposer_roles', []));
    }

    public static function canEdit(User $user, IpRegistration $registration): bool
    {
        if (! $registration->isEditable()) {
            return false;
        }

        if ($user->hasAnyRole(config('sipepeng_hki.manage_roles', []))) {
            return true;
        }

        return self::canView($user, $registration) && $user->hasRole('dosen');
    }

    public static function canSubmit(User $user, IpRegistration $registration): bool
    {
        return self::canEdit($user, $registration)
            && in_array($registration->status, ['draft', 'revision_required'], true);
    }

    public static function canVerify(User $user): bool
    {
        return $user->hasAnyRole(config('sipepeng_hki.manage_roles', []));
    }
}
