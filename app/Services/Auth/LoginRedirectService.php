<?php

namespace App\Services\Auth;

use App\Models\User;

class LoginRedirectService
{
    public function resolve(User $user): string
    {
        return route('dashboard');
    }
}
