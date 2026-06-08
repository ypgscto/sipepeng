<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSipepengRole
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        $allowedRoles = $this->parseRoles($roles);

        if ($user->hasRole('super_admin')) {
            return $next($request);
        }

        foreach ($allowedRoles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }

        if ($request->expectsJson()) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return redirect()
            ->route('access.denied')
            ->with('access_denied_message', 'Anda tidak memiliki akses ke halaman ini.');
    }

    /**
     * @param  list<string>  $roles
     * @return list<string>
     */
    protected function parseRoles(array $roles): array
    {
        return collect($roles)
            ->flatMap(fn (string $group) => explode('|', $group))
            ->map(fn (string $role) => trim($role))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
