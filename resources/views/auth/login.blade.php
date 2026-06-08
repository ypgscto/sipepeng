@extends('layouts.guest')

@section('content')
    <h2 class="text-xl sm:text-2xl font-bold text-slate-900 mb-1">Masuk ke SiPepeng</h2>
    <p class="text-sm text-slate-500 mb-4">
        Gunakan akun institusi SIAKAD-GS Anda
    </p>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <div>
            <label for="login" class="sipeng-label">Email atau username Siakad</label>
            <input id="login" type="text" name="login" value="{{ old('login') }}" required autofocus
                class="sipeng-input" autocomplete="username" />
            <x-input-error :messages="$errors->get('login')" class="mt-2" />
        </div>
        <div>
            <label for="password" class="sipeng-label">Kata sandi</label>
            <input id="password" type="password" name="password" required
                class="sipeng-input" autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>
        <div class="flex items-center">
            <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-slate-600">
                <input id="remember_me" type="checkbox" name="remember" class="rounded border-slate-300 text-pink-600 focus:ring-pink-500">
                Ingat saya
            </label>
        </div>
        <p class="text-xs text-slate-500">
            Reset kata sandi dilakukan melalui SIAKAD-GS. Hubungi administrator LPPM jika akun belum diaktifkan.
        </p>
        <button type="submit" class="sipeng-btn-primary w-full py-2.5">Masuk</button>
    </form>

    <p class="mt-4 text-center">
        <a href="{{ route('public.landing') }}" class="text-sm font-medium text-pink-700 hover:text-pink-900 hover:underline">
            Kembali ke Beranda
        </a>
    </p>
@endsection
