<?php

namespace App\Services\Siakad;

use App\Models\SipepengRole;

class SiakadRoleMapper
{
    /**
     * @param  array<string, mixed>  $profile
     * @return list<string>
     */
    public function resolveRoleCodes(array $profile): array
    {
        $fromOverride = $this->rolesFromLoginOverride($profile);
        if ($fromOverride !== []) {
            return $fromOverride;
        }

        if (isset($profile['sipepeng_roles']) && is_array($profile['sipepeng_roles'])) {
            return $this->filterValidRoles($this->normalizeRoleList($profile['sipepeng_roles']));
        }

        if (isset($profile['simawa_roles']) && is_array($profile['simawa_roles'])) {
            return $this->mapSimawaRolesToSipepeng($profile['simawa_roles']);
        }

        $roleField = $profile['role'] ?? $profile['jenis_role'] ?? $profile['user_role'] ?? null;
        if (is_string($roleField) && $roleField !== '') {
            $normalized = $this->normalizeRoleList([$roleField]);
            if ($normalized !== []) {
                return $this->filterValidRoles($normalized);
            }
        }

        $fromDb = $this->rolesFromDatabaseMaps($profile);
        if ($fromDb !== []) {
            return $fromDb;
        }

        $jenisUser = (string) ($profile['jenis_user'] ?? '');
        $fromJenis = config('sipepeng_siakad_auth.role_map')[$jenisUser] ?? [];
        if ($fromJenis !== []) {
            return $this->filterValidRoles($fromJenis);
        }

        $levelId = (string) ($profile['level_id'] ?? '');
        $fromLevel = config('sipepeng_siakad_auth.level_id_role_map')[$levelId] ?? [];

        return $this->filterValidRoles($fromLevel);
    }

    /**
     * @param  array<string, mixed>  $profile
     * @return list<string>
     */
    protected function rolesFromDatabaseMaps(array $profile): array
    {
        $codes = [];
        $jenisUser = trim((string) ($profile['jenis_user'] ?? ''));
        $levelId = trim((string) ($profile['level_id'] ?? ''));

        if ($jenisUser !== '') {
            $codes = array_merge($codes, $this->codesForMap('jenis_user', $jenisUser));
        }

        if ($levelId !== '') {
            $codes = array_merge($codes, $this->codesForMap('level_id', $levelId));
        }

        return $this->filterValidRoles($codes);
    }

    /**
     * @return list<string>
     */
    protected function codesForMap(string $mapType, string $mapKey): array
    {
        return SipepengRole::query()
            ->active()
            ->where('siakad_map_type', $mapType)
            ->where('siakad_map_key', $mapKey)
            ->orderBy('sort_order')
            ->pluck('code')
            ->all();
    }

    /**
     * @param  list<string>  $simawaRoles
     * @return list<string>
     */
    protected function mapSimawaRolesToSipepeng(array $simawaRoles): array
    {
        $map = [
            'super_admin' => ['super_admin'],
            'superadmin' => ['super_admin'],
            'admin_kemahasiswaan' => ['admin_lppm'],
            'pimpinan' => ['pimpinan'],
            'prodi' => ['ketua_prodi'],
            'dosen' => ['dosen'],
            'mahasiswa' => ['mahasiswa'],
            'pegawai' => [],
            'alumni' => [],
        ];

        $codes = [];
        foreach ($simawaRoles as $role) {
            $normalized = $this->normalizeRoleList([(string) $role]);
            foreach ($normalized as $code) {
                if (isset($map[$code])) {
                    $codes = array_merge($codes, $map[$code]);
                }
            }
            $roleKey = strtolower(trim((string) $role));
            if (isset($map[$roleKey])) {
                $codes = array_merge($codes, $map[$roleKey]);
            }
        }

        return $this->filterValidRoles($codes);
    }

    /**
     * @param  list<string>  $roles
     * @return list<string>
     */
    protected function filterValidRoles(array $roles): array
    {
        $allowed = SipepengRole::query()
            ->active()
            ->pluck('code')
            ->flip();

        return collect($roles)
            ->map(fn ($role) => (string) $role)
            ->filter(fn (string $role) => $role !== '' && $allowed->has($role))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $profile
     * @return list<string>
     */
    protected function rolesFromLoginOverride(array $profile): array
    {
        $overrides = config('sipepeng_siakad_auth.login_role_overrides', []);
        if ($overrides === []) {
            return [];
        }

        $candidates = array_filter([
            strtolower(trim((string) ($profile['_form_login'] ?? ''))),
            strtolower(trim((string) ($profile['login'] ?? ''))),
            strtolower(trim((string) ($profile['email'] ?? ''))),
            strtolower(trim((string) ($profile['username'] ?? ''))),
            strtolower(trim((string) ($profile['siakad_user_id'] ?? ''))),
        ]);

        foreach ($candidates as $key) {
            if ($key !== '' && isset($overrides[$key])) {
                return $this->filterValidRoles((array) $overrides[$key]);
            }
        }

        return [];
    }

    /**
     * @param  list<mixed>  $roles
     * @return list<string>
     */
    protected function normalizeRoleList(array $roles): array
    {
        return collect($roles)
            ->map(function ($role): string {
                $role = strtolower(trim((string) $role));

                return match ($role) {
                    'superadmin', 'super admin' => 'super_admin',
                    default => str_replace(' ', '_', $role),
                };
            })
            ->filter(fn (string $role): bool => $role !== '')
            ->unique()
            ->values()
            ->all();
    }
}
