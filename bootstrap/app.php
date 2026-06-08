<?php

use App\Support\Http\PageExpiredResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureSipepengRole::class,
            'sipepeng.access' => \App\Http\Middleware\EnsureSipepengAccess::class,
            'settings.access' => \App\Http\Middleware\EnsureSettingsAccess::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (\Throwable $e, Request $request) {
            if (! PageExpiredResponse::matches($e)) {
                return null;
            }

            return PageExpiredResponse::resolve($request, $e);
        });
    })->create();
