<?php

namespace App\Support\Proposal;

use App\Models\User;

class ProposalLeaderAttributes
{
    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public static function applyKetuaForUser(array $attributes, User $user): array
    {
        if ($user->hasAnyRole(['super_admin', 'admin_lppm'])) {
            return $attributes;
        }

        if ($user->hasRole('dosen')) {
            $attributes['ketua_dosen_id'] = (string) ($user->siakad_login ?: $user->siakad_user_id ?: '');
            $attributes['ketua_dosen_nama_snapshot'] = $user->name;
        }

        return $attributes;
    }
}
