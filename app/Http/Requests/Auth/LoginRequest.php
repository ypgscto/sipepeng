<?php

namespace App\Http\Requests\Auth;

use App\Exceptions\Siakad\SiakadConnectionException;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\Siakad\SiakadAuthApiService;
use App\Services\Siakad\SiakadUserProvisionService;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login' => ['required_without:email', 'nullable', 'string', 'max:150'],
            'email' => ['required_without:login', 'nullable', 'string', 'max:150'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'login' => 'email atau username Siakad',
            'email' => 'email atau username Siakad',
        ];
    }

    /**
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $identifier = trim((string) ($this->input('login') ?? $this->input('email')));
        $password = (string) $this->input('password');

        $user = $this->attemptSiakadLogin($identifier, $password)
            ?? $this->attemptLocalFallback($identifier, $password);

        if (! $user) {
            RateLimiter::hit($this->throttleKey());

            app(ActivityLogger::class)->logAudit(
                'login_failed',
                null,
                'Login gagal.',
                ['login' => $identifier],
                $this,
            );

            throw ValidationException::withMessages([
                'login' => trans('auth.failed'),
            ]);
        }

        if (! $user->canLoginToSipepeng()) {
            throw ValidationException::withMessages([
                'login' => $user->isSiakadSourced()
                    ? 'Akun Siakad Anda sudah dikenali, tetapi belum diaktifkan untuk SiPepeng. Hubungi administrator LPPM untuk mengaktifkan login dan peran.'
                    : 'Akun tidak aktif atau tidak diizinkan login. Hubungi administrator LPPM.',
            ]);
        }

        Auth::login($user, $this->boolean('remember'));
        RateLimiter::clear($this->throttleKey());
    }

    protected function attemptSiakadLogin(string $login, string $password): ?User
    {
        try {
            $profile = app(SiakadAuthApiService::class)->attemptLogin($login, $password);
        } catch (SiakadConnectionException $e) {
            if (! config('sipepeng_siakad_auth.allow_local_fallback', false)) {
                throw ValidationException::withMessages([
                    'login' => $e->getMessage(),
                ]);
            }

            return null;
        }

        if ($profile === null) {
            return null;
        }

        $profile = $this->enrichSiakadProfile($profile, $login);

        try {
            return app(SiakadUserProvisionService::class)->provisionFromLoginProfile($profile);
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'login' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $profile
     * @return array<string, mixed>
     */
    protected function enrichSiakadProfile(array $profile, string $login): array
    {
        $login = trim($login);
        $normalized = strtolower($login);

        if (trim((string) ($profile['login'] ?? '')) === '') {
            $profile['login'] = $login;
        }

        if (trim((string) ($profile['email'] ?? '')) === '' && filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $profile['email'] = $normalized;
        }

        if (trim((string) ($profile['siakad_user_id'] ?? '')) === '') {
            $profile['siakad_user_id'] = (string) ($profile['login'] ?? $login);
        }

        $profile['_form_login'] = $normalized;

        return $profile;
    }

    protected function attemptLocalFallback(string $login, string $password): ?User
    {
        if (! config('sipepeng_siakad_auth.allow_local_fallback', false)) {
            return null;
        }

        $normalizedLogin = strtolower(trim($login));
        $email = filter_var($normalizedLogin, FILTER_VALIDATE_EMAIL)
            ? $normalizedLogin
            : $normalizedLogin.'@'.config('sipepeng_siakad_auth.email_domain');

        $user = User::query()
            ->where(function ($query) use ($normalizedLogin, $email, $login): void {
                $query->where('email', $email)
                    ->orWhere('email', $normalizedLogin)
                    ->orWhere('siakad_login', $login)
                    ->orWhere('siakad_login', $normalizedLogin);
            })
            ->first();

        if (! $user || ! Auth::getProvider()->validateCredentials($user, ['password' => $password])) {
            return null;
        }

        return $user;
    }

    /**
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        $id = Str::lower((string) ($this->input('login') ?? $this->input('email')));

        return Str::transliterate($id.'|'.$this->ip());
    }
}
