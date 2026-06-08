<?php

namespace App\Services\Siakad;

use App\Models\SipepengRole;
use App\Models\SipepengUserRoleMapping;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use InvalidArgumentException;

class SiakadUserProvisionService
{
    public function __construct(
        protected SiakadRoleMapper $roleMapper,
    ) {}

    /**
     * @param  array<string, mixed>  $profile
     */
    public function provisionFromLoginProfile(array $profile): User
    {
        $login = trim((string) ($profile['login'] ?? ''));
        if ($login === '') {
            throw new InvalidArgumentException('Profil Siakad tanpa login.');
        }

        $jenisUser = (string) ($profile['jenis_user'] ?? '');
        $roleCodes = $this->roleMapper->resolveRoleCodes($profile);
        $existingUser = $this->findExistingUser($profile, $login);

        if ($roleCodes === []) {
            if ($existingUser !== null && $existingUser->activeRoles()->exists()) {
                $existingUser->update(array_merge(
                    $this->identityAttributes($profile, $login, $jenisUser),
                    ['is_allowed_login' => $existingUser->is_allowed_login || $this->shouldAllowLogin([])],
                ));

                return $existingUser->fresh();
            }

            throw new InvalidArgumentException('Akun tidak memiliki peran SiPepeng yang valid.');
        }

        if (in_array($jenisUser, config('sipepeng_siakad_auth.denied_jenis_user', []), true)
            && ! $this->hasBypassJenisUserRestriction($roleCodes)) {
            throw new InvalidArgumentException('Jenis akun Siakad tidak diizinkan masuk SiPepeng.');
        }

        $identity = $this->identityAttributes($profile, $login, $jenisUser);

        $user = $existingUser;

        if ($user) {
            $user->update(array_merge($identity, [
                'is_allowed_login' => $user->is_allowed_login || $this->shouldAllowLogin($roleCodes),
            ]));

            if (config('sipepeng_siakad_auth.apply_siakad_roles_on_update', false)) {
                $this->syncRoleMappings($user, $roleCodes);
            } elseif ($this->shouldAllowLogin($roleCodes)) {
                $this->syncRoleMappings($user, $roleCodes);
            } elseif ($user->activeRoles()->count() === 0) {
                $this->syncRoleMappings($user, $roleCodes);
            }

            return $user->fresh();
        }

        $user = User::query()->create(array_merge($identity, [
            'password' => Hash::make(Str::random(48)),
            'email_verified_at' => now(),
            'is_allowed_login' => $this->shouldAllowLogin($roleCodes),
        ]));

        $this->syncRoleMappings($user, $roleCodes);

        return $user->fresh();
    }

    /**
     * @param  array<string, mixed>  $profile
     * @return array<string, mixed>
     */
    protected function identityAttributes(array $profile, string $login, string $jenisUser): array
    {
        return [
            'siakad_user_id' => (string) ($profile['siakad_user_id'] ?? $login),
            'siakad_login' => $login,
            'name' => (string) ($profile['nama'] ?? $profile['name'] ?? $login),
            'email' => $this->resolveEmail($profile, $login),
            'user_category' => $this->categoryFromProfile($profile, $jenisUser),
            'jenis_user' => $jenisUser,
            'is_active' => true,
            'synced_at' => now(),
        ];
    }

    /**
     * @param  array<string, mixed>  $profile
     */
    protected function findExistingUser(array $profile, string $login): ?User
    {
        $email = $this->resolveEmail($profile, $login);
        $siakadUserId = (string) ($profile['siakad_user_id'] ?? $login);
        $formLogin = strtolower(trim((string) ($profile['_form_login'] ?? '')));

        return User::query()
            ->where('siakad_user_id', $siakadUserId)
            ->orWhere('siakad_login', $login)
            ->when($formLogin !== '', fn ($query) => $query->orWhere('siakad_login', $formLogin)->orWhere('email', $formLogin))
            ->orWhere('email', $email)
            ->first();
    }

    protected function defaultAllowedLoginOnFirstLogin(): bool
    {
        return (bool) config('sipepeng_siakad_auth.auto_allow_login_on_first_login', false);
    }

    /**
     * @param  list<string>  $roleCodes
     */
    protected function shouldAllowLogin(array $roleCodes): bool
    {
        if ($this->defaultAllowedLoginOnFirstLogin()) {
            return true;
        }

        $autoRoles = config('sipepeng_siakad_auth.auto_allow_login_roles', ['super_admin']);

        return $roleCodes !== [] && collect($roleCodes)->intersect($autoRoles)->isNotEmpty();
    }

    /**
     * @param  list<string>  $roleCodes
     */
    protected function hasBypassJenisUserRestriction(array $roleCodes): bool
    {
        $bypassRoles = config('sipepeng_siakad_auth.bypass_denied_jenis_user_roles', ['super_admin']);

        return collect($roleCodes)->intersect($bypassRoles)->isNotEmpty();
    }

    /**
     * @param  array<string, mixed>  $profile
     */
    protected function categoryFromProfile(array $profile, string $jenisUser): string
    {
        if (! empty($profile['user_category'])) {
            return (string) $profile['user_category'];
        }

        return match ($jenisUser) {
            '7' => 'dosen',
            '100', 'mahasiswa' => 'mahasiswa',
            default => 'pegawai',
        };
    }

    /**
     * @param  array<string, mixed>  $profile
     */
    protected function resolveEmail(array $profile, string $login): string
    {
        $fromApi = strtolower(trim((string) ($profile['email'] ?? '')));
        if ($fromApi !== '' && filter_var($fromApi, FILTER_VALIDATE_EMAIL)) {
            return $fromApi;
        }

        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            return strtolower($login);
        }

        return strtolower($login).'@'.config('sipepeng_siakad_auth.email_domain', 'stikesgunungsari.ac.id');
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
                    'notes' => 'Auto-assign dari login Siakad',
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
}
