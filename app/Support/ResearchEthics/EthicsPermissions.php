<?php

namespace App\Support\ResearchEthics;

use App\Models\ResearchEthics\ResearchEthicsApplication;
use App\Models\User;

class EthicsPermissions
{
    public static function canViewAny(User $user): bool
    {
        return $user->hasAnyRole(config('sipepeng_ethics.view_all_roles', []));
    }

    public static function canView(User $user, ResearchEthicsApplication $application): bool
    {
        if (self::canViewAny($user)) {
            return true;
        }

        if ($user->hasRole('reviewer')) {
            return $application->reviews()->where('reviewer_user_id', $user->id)->exists();
        }

        if ($user->hasRole('dosen')) {
            return $application->ketua_user_id === $user->id
                || $application->created_by === $user->id
                || in_array($application->ketua_dosen_id, array_filter([$user->siakad_login, $user->siakad_user_id]), true);
        }

        return false;
    }

    public static function canCreate(User $user): bool
    {
        return $user->hasAnyRole(config('sipepeng_ethics.proposer_roles', []));
    }

    public static function canEdit(User $user, ResearchEthicsApplication $application): bool
    {
        if (! $application->isEditable()) {
            return false;
        }

        if ($user->hasAnyRole(config('sipepeng_ethics.manage_roles', []))) {
            return true;
        }

        return self::canView($user, $application) && $user->hasRole('dosen');
    }

    public static function canSubmit(User $user, ResearchEthicsApplication $application): bool
    {
        return self::canEdit($user, $application)
            && in_array($application->status, ['draft', 'revision_required'], true);
    }

    public static function canDecide(User $user): bool
    {
        return $user->hasAnyRole(config('sipepeng_ethics.decision_roles', []));
    }
}
