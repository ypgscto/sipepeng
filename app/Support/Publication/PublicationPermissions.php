<?php

namespace App\Support\Publication;

use App\Models\Publication\Publication;
use App\Models\User;

class PublicationPermissions
{
    public static function canViewAny(User $user): bool
    {
        return $user->hasAnyRole(config('sipepeng_publication.view_all_roles', []));
    }

    public static function canView(User $user, Publication $publication): bool
    {
        if (self::canViewAny($user)) {
            return true;
        }

        if ($user->hasRole('dosen')) {
            return $publication->created_by === $user->id
                || $publication->authors()->where(function ($q) use ($user) {
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
        return $user->hasAnyRole(config('sipepeng_publication.proposer_roles', []));
    }

    public static function canEdit(User $user, Publication $publication): bool
    {
        if (! $publication->isEditable()) {
            return false;
        }

        if ($user->hasAnyRole(config('sipepeng_publication.manage_roles', []))) {
            return true;
        }

        return self::canView($user, $publication) && $user->hasRole('dosen');
    }

    public static function canSubmit(User $user, Publication $publication): bool
    {
        return self::canEdit($user, $publication)
            && in_array($publication->status, ['draft', 'revision_required'], true);
    }

    public static function canVerify(User $user): bool
    {
        return $user->hasAnyRole(config('sipepeng_publication.manage_roles', []));
    }
}
