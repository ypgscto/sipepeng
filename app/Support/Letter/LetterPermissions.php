<?php

namespace App\Support\Letter;

use App\Models\Letter\Letter;
use App\Models\User;

class LetterPermissions
{
    public static function canViewAny(User $user): bool
    {
        return $user->hasAnyRole(config('sipepeng_letters.view_all_roles', []));
    }

    public static function canView(User $user, Letter $letter): bool
    {
        if (self::canViewAny($user)) {
            return true;
        }

        if ($user->hasRole('dosen')) {
            return Letter::query()->visibleTo($user)->whereKey($letter->id)->exists();
        }

        return false;
    }

    public static function canCreate(User $user): bool
    {
        return $user->hasAnyRole(config('sipepeng_letters.proposer_roles', []));
    }

    public static function canCreateType(User $user, ?string $typeCode): bool
    {
        if (! self::canCreate($user)) {
            return false;
        }

        if ($user->hasAnyRole(config('sipepeng_letters.manage_roles', []))) {
            return true;
        }

        if (! $typeCode) {
            return true;
        }

        $rules = config('sipepeng_letters.letter_type_rules.'.$typeCode, []);

        return (bool) ($rules['allow_dosen_create'] ?? true);
    }

    public static function canEdit(User $user, Letter $letter): bool
    {
        if (! $letter->isEditable()) {
            return false;
        }

        if ($user->hasAnyRole(config('sipepeng_letters.manage_roles', []))) {
            return true;
        }

        return self::canView($user, $letter) && $user->hasRole('dosen');
    }

    public static function canSubmit(User $user, Letter $letter): bool
    {
        return self::canEdit($user, $letter)
            && in_array($letter->status, ['draft', 'revision_required'], true);
    }

    public static function canApprove(User $user): bool
    {
        return $user->hasAnyRole(config('sipepeng_letters.approve_roles', []));
    }

    public static function canIssue(User $user, Letter $letter): bool
    {
        return $user->hasAnyRole(config('sipepeng_letters.issue_roles', []))
            && $letter->status === 'approved';
    }

    public static function canUploadSigned(User $user, Letter $letter): bool
    {
        return $user->hasAnyRole(config('sipepeng_letters.issue_roles', []))
            && $letter->isIssued();
    }
}
