<?php

namespace App\Support\Http;

use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class PageExpiredResponse
{
    /**
     * @return array<int, string>
     */
    public static function inputExcept(Request $request): array
    {
        return [
            '_token',
            'password',
            'password_confirmation',
            'current_password',
            'file_proposal',
            'file_pengesahan',
            'file_pernyataan',
            'file',
        ];
    }

    public static function matches(Throwable $e): bool
    {
        if ($e instanceof TokenMismatchException || $e instanceof PostTooLargeException) {
            return true;
        }

        return $e instanceof HttpException && in_array($e->getStatusCode(), [413, 419], true);
    }

    public static function message(Throwable $e): string
    {
        if ($e instanceof PostTooLargeException || ($e instanceof HttpException && $e->getStatusCode() === 413)) {
            return 'Ukuran unggahan terlalu besar. Perkecil berkas PDF atau naikkan batas upload PHP (post_max_size / upload_max_filesize), lalu coba lagi.';
        }

        return 'Sesi habis atau halaman kedaluwarsa. Muat ulang halaman, lalu kirim ulang formulir.';
    }

    public static function resolve(Request $request, Throwable $e)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => self::message($e),
            ], $e instanceof HttpException ? $e->getStatusCode() : 419);
        }

        if ($request->is('logout') || $request->routeIs('logout')) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with('status', 'Sesi berakhir. Silakan masuk kembali jika diperlukan.');
        }

        if ($request->hasSession()) {
            $request->session()->regenerateToken();
        }

        if (Auth::check()) {
            $fallback = match (true) {
                $request->routeIs('admin.research.store') => route('admin.research.create'),
                $request->routeIs('admin.research.update') => $request->route('proposal')
                    ? route('admin.research.edit', $request->route('proposal'))
                    : route('admin.research.index'),
                default => route('dashboard'),
            };

            return redirect()
                ->back(fallback: $fallback)
                ->withInput($request->except(self::inputExcept($request)))
                ->withErrors(['form' => self::message($e)]);
        }

        return redirect()
            ->route('login')
            ->withInput($request->except(self::inputExcept($request)))
            ->withErrors([
                'login' => self::message($e),
            ]);
    }
}
