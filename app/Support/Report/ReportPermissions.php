<?php

namespace App\Support\Report;

use App\Models\User;

class ReportPermissions
{
    public static function canViewAny(User $user): bool
    {
        foreach (array_keys(config('sipepeng_reports.types', [])) as $type) {
            if (self::canViewType($user, $type)) {
                return true;
            }
        }

        return false;
    }

    public static function canViewType(User $user, string $type): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($type === 'accreditation') {
            return self::canViewAccreditation($user);
        }

        $roles = config('sipepeng_reports.type_roles.'.$type, []);

        return $roles !== [] && $user->hasAnyRole($roles);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function viewableTypes(User $user): array
    {
        return collect(config('sipepeng_reports.types', []))
            ->filter(fn (array $meta, string $type): bool => self::canViewType($user, $type))
            ->all();
    }

    public static function canExport(User $user): bool
    {
        return $user->hasAnyRole(config('sipepeng_reports.export_roles', []));
    }

    public static function canExportType(User $user, string $type): bool
    {
        return self::canExport($user) && self::canViewType($user, $type);
    }

    public static function canViewAccreditation(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin_lppm', 'ketua_lppm', 'pimpinan']);
    }
}
