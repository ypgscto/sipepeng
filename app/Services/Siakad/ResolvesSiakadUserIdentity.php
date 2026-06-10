<?php

namespace App\Services\Siakad;

use App\Models\User;

trait ResolvesSiakadUserIdentity
{
    protected function findExistingSiakadUser(
        string $login,
        string $email,
        string $siakadUserId,
        string $formLogin = '',
    ): ?User {
        foreach ($this->emailLookupCandidates($login, $email, $formLogin) as $candidate) {
            $user = User::query()->whereRaw('LOWER(email) = ?', [$candidate])->first();
            if ($user !== null) {
                return $user;
            }
        }

        foreach ($this->loginLookupCandidates($login, $formLogin) as $candidate) {
            $user = User::query()->whereRaw('LOWER(siakad_login) = ?', [$candidate])->first();
            if ($user !== null) {
                return $user;
            }
        }

        if ($siakadUserId !== '' && $siakadUserId !== '0') {
            return User::query()->where('siakad_user_id', $siakadUserId)->first();
        }

        return null;
    }

    /**
     * @return list<string>
     */
    protected function emailLookupCandidates(string $login, string $email, string $formLogin): array
    {
        return array_values(array_unique(array_filter([
            strtolower(trim($email)),
            strtolower(trim($formLogin)),
            filter_var($login, FILTER_VALIDATE_EMAIL) ? strtolower(trim($login)) : '',
        ])));
    }

    /**
     * @return list<string>
     */
    protected function loginLookupCandidates(string $login, string $formLogin): array
    {
        return array_values(array_unique(array_filter([
            strtolower(trim($login)),
            strtolower(trim($formLogin)),
        ])));
    }

    /**
     * @param  array<string, mixed>  $identity
     */
    protected function releaseConflictingSiakadIdentity(User $target, array $identity): void
    {
        $siakadUserId = trim((string) ($identity['siakad_user_id'] ?? ''));
        if ($siakadUserId !== '' && $siakadUserId !== '0') {
            User::query()
                ->where('id', '!=', $target->id)
                ->where('siakad_user_id', $siakadUserId)
                ->update(['siakad_user_id' => null]);
        }

        $siakadLogin = trim((string) ($identity['siakad_login'] ?? ''));
        if ($siakadLogin !== '') {
            User::query()
                ->where('id', '!=', $target->id)
                ->whereRaw('LOWER(siakad_login) = ?', [strtolower($siakadLogin)])
                ->update(['siakad_login' => null]);
        }
    }

    /**
     * @param  array<string, mixed>  $identity
     * @return array<string, mixed>
     */
    protected function identityWithoutEmailConflict(User $user, array $identity): array
    {
        $email = strtolower(trim((string) ($identity['email'] ?? '')));
        if ($email === '') {
            return $identity;
        }

        $conflicts = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->where('id', '!=', $user->id)
            ->exists();

        if ($conflicts) {
            unset($identity['email']);
        }

        return $identity;
    }

    /**
     * @param  array<string, mixed>  $identity
     * @param  array<string, mixed>  $extra
     */
    protected function applySiakadIdentity(User $user, array $identity, array $extra = []): void
    {
        $this->releaseConflictingSiakadIdentity($user, $identity);
        $user->update(array_merge(
            $this->identityWithoutEmailConflict($user, $identity),
            $extra,
        ));
    }
}
