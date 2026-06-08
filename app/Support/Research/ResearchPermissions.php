<?php

namespace App\Support\Research;

use App\Models\Research\ResearchProposal;
use App\Models\User;

class ResearchPermissions
{
    public static function canViewAny(User $user): bool
    {
        return $user->hasAnyRole(config('sipepeng_research.view_all_roles', []));
    }

    public static function canView(User $user, ResearchProposal $proposal): bool
    {
        if (self::canViewAny($user)) {
            return true;
        }

        if ($user->hasRole('reviewer')) {
            return $proposal->reviews()
                ->whereHas('reviewer', fn ($q) => $q->where('user_id', $user->id))
                ->exists();
        }

        if ($user->hasRole('dosen')) {
            return self::isKetua($user, $proposal);
        }

        return false;
    }

    public static function canCreate(User $user): bool
    {
        return $user->hasAnyRole(config('sipepeng_research.proposer_roles', []));
    }

    public static function canEdit(User $user, ResearchProposal $proposal): bool
    {
        if (! $proposal->isEditable()) {
            return false;
        }

        if ($user->hasAnyRole(config('sipepeng_research.manage_roles', []))) {
            return true;
        }

        return $user->hasRole('dosen') && self::isKetua($user, $proposal);
    }

    public static function canSubmit(User $user, ResearchProposal $proposal): bool
    {
        return self::canEdit($user, $proposal)
            && in_array($proposal->status, ['draft', 'revision_required'], true);
    }

    public static function canVerifyAdmin(User $user): bool
    {
        return $user->hasAnyRole(config('sipepeng_research.manage_roles', []));
    }

    public static function canAssignReviewer(User $user): bool
    {
        return $user->hasAnyRole(config('sipepeng_research.manage_roles', []));
    }

    public static function canSubmitReview(User $user, ResearchProposal $proposal): bool
    {
        return $proposal->reviews()
            ->where('status', 'assigned')
            ->whereHas('reviewer', fn ($q) => $q->where('user_id', $user->id))
            ->exists();
    }

    public static function canDecide(User $user): bool
    {
        return $user->hasAnyRole(config('sipepeng_research.decision_roles', []));
    }

    public static function isKetua(User $user, ResearchProposal $proposal): bool
    {
        if ($proposal->ketua_user_id === $user->id) {
            return true;
        }

        $ids = array_filter([$user->siakad_login, $user->siakad_user_id]);

        return in_array($proposal->ketua_dosen_id, $ids, true);
    }
}
