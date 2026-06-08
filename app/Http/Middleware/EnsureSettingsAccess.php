<?php

namespace App\Http\Middleware;

use App\Support\Settings\SettingsPermissions;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSettingsAccess
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string $level = 'view'): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        $allowed = match ($level) {
            'manage' => SettingsPermissions::canManage($user),
            'backup' => SettingsPermissions::canBackup($user),
            default => SettingsPermissions::canView($user),
        };

        if ($allowed) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(403, 'Anda tidak memiliki akses ke pengaturan aplikasi.');
        }

        return redirect()
            ->route('access.denied')
            ->with('access_denied_message', 'Anda tidak memiliki akses ke pengaturan aplikasi.');
    }
}
