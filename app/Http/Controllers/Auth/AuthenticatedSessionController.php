<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\ActivityLogger;
use App\Services\Auth\LoginRedirectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request, LoginRedirectService $redirectService, ActivityLogger $logger): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $logger->logAudit(
            'login_success',
            $request->user(),
            'Login berhasil.',
            ['login' => $request->input('login')],
        );

        return redirect()->intended($redirectService->resolve($request->user()));
    }

    public function destroy(Request $request, ActivityLogger $logger): RedirectResponse
    {
        if ($user = $request->user()) {
            $logger->logAudit('logout', $user, 'Logout.', [], $request);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('public.landing');
    }
}
