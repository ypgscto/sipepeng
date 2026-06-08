<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sesuaikan URL aplikasi & path cookie session saat diakses via subfolder
 * (mis. http://98.142.245.18/sipepeng).
 */
class ConfigureSubfolderSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $basePath = $request->getBasePath();

        if ($basePath !== '' && $basePath !== '/') {
            URL::forceRootUrl(rtrim($request->getSchemeAndHttpHost().$basePath, '/'));
            config(['session.path' => rtrim($basePath, '/').'/']);
        } else {
            $appUrlPath = parse_url((string) config('app.url'), PHP_URL_PATH);
            if (is_string($appUrlPath) && $appUrlPath !== '' && $appUrlPath !== '/') {
                config(['session.path' => rtrim($appUrlPath, '/').'/']);
            }
        }

        if ($request->isSecure() || strtolower((string) $request->header('X-Forwarded-Proto', '')) === 'https') {
            URL::forceScheme('https');
            if (config('session.secure') === null || config('session.secure') === false) {
                config(['session.secure' => true]);
            }
        }

        return $next($request);
    }
}
