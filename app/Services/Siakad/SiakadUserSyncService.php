<?php

namespace App\Services\Siakad;

use App\Models\SipepengRole;
use App\Models\SipepengUserRoleMapping;
use App\Models\User;
use App\Support\Siakad\SiakadResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use InvalidArgumentException;

class SiakadUserSyncService
{
    use ResolvesSiakadUserIdentity;

    public function __construct(
        protected SiakadApiService $api,
        protected SiakadRoleMapper $roleMapper,
    ) {}

    /**
     * @return array{created: int, updated: int, skipped: int, errors: list<string>}
     */
    public function syncAll(): array
    {
        $loaded = $this->api->fetchAll(SiakadResource::LOGIN_USERS);
        $stats = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];

        foreach ($loaded['records'] as $row) {
            try {
                $result = $this->upsertFromPayload($row);
                $stats[$result]++;
            } catch (\Throwable $e) {
                $stats['skipped']++;
                $stats['errors'][] = ($row['siakad_login'] ?? '?').': '.$e->getMessage();
            }
        }

        return $stats;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function upsertFromPayload(array $row): string
    {
        $siakadUserId = trim((string) ($row['siakad_user_id'] ?? ''));
        $login = trim((string) ($row['siakad_login'] ?? ''));
        if ($siakadUserId === '' || $siakadUserId === '0' || $login === '') {
            throw new InvalidArgumentException('Data user tanpa siakad_user_id/login.');
        }

        $roles = $this->resolveRolesFromRow($row);
        if ($roles === []) {
            throw new InvalidArgumentException('Tidak ada peran SiPepeng.');
        }

        $email = strtolower(trim((string) ($row['email'] ?? '')));
        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Email tidak valid.');
        }

        $identity = [
            'siakad_user_id' => $siakadUserId,
            'siakad_login' => $login,
            'name' => (string) ($row['name'] ?? $login),
            'email' => $email,
            'user_category' => (string) ($row['user_category'] ?? 'pegawai'),
            'jenis_user' => (string) ($row['jenis_user'] ?? ''),
            'is_active' => (bool) ($row['is_active'] ?? true),
            'synced_at' => now(),
        ];

        $user = $this->findExistingSiakadUser($login, $email, $siakadUserId);

        if ($user) {
            $this->applySiakadIdentity($user, $identity);

            if (config('sipepeng_siakad_auth.apply_siakad_roles_on_sync_update', false)) {
                $this->syncRoleMappings($user, $roles);
            }

            return 'updated';
        }

        $user = User::query()->create(array_merge($identity, [
            'password' => Hash::make(Str::random(48)),
            'email_verified_at' => now(),
            'is_allowed_login' => $this->defaultAllowedLoginOnSync(),
        ]));

        $this->syncRoleMappings($user, $roles);

        return 'created';
    }

    /**
     * @param  array<string, mixed>  $row
     * @return list<string>
     */
    protected function resolveRolesFromRow(array $row): array
    {
        if (isset($row['sipepeng_roles']) && is_array($row['sipepeng_roles'])) {
            $fromApi = $this->filterValidRoles((array) $row['sipepeng_roles']);
            if ($fromApi !== []) {
                return $fromApi;
            }
        }

        return $this->roleMapper->resolveRoleCodes([
            'jenis_user' => $row['jenis_user'] ?? '',
            'level_id' => $row['level_id'] ?? '',
            'login' => $row['siakad_login'] ?? '',
            'email' => $row['email'] ?? '',
        ]);
    }

    protected function defaultAllowedLoginOnSync(): bool
    {
        return (bool) config('sipepeng_siakad_auth.auto_allow_login_on_sync', false);
    }

    /**
     * @param  list<string>  $roleCodes
     */
    protected function syncRoleMappings(User $user, array $roleCodes): void
    {
        $roles = SipepengRole::query()
            ->active()
            ->whereIn('code', $roleCodes)
            ->orderBy('sort_order')
            ->get();

        if ($roles->isEmpty()) {
            return;
        }

        $primaryCode = $this->resolvePrimaryRoleCode($roleCodes);

        foreach ($roles as $role) {
            SipepengUserRoleMapping::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'role_id' => $role->id,
                ],
                [
                    'is_primary' => $role->code === $primaryCode,
                    'is_active' => true,
                    'assigned_at' => now(),
                    'notes' => 'Auto-assign dari sinkronisasi Siakad',
                ],
            );
        }
    }

    /**
     * @param  list<string>  $roleCodes
     */
    protected function resolvePrimaryRoleCode(array $roleCodes): string
    {
        $priority = [
            'super_admin',
            'admin_lppm',
            'ketua_lppm',
            'pimpinan',
            'ketua_prodi',
            'reviewer',
            'dosen',
            'mahasiswa',
        ];

        foreach ($priority as $code) {
            if (in_array($code, $roleCodes, true)) {
                return $code;
            }
        }

        return $roleCodes[0];
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
}
