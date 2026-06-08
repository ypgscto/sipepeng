@extends('layouts.guest')

@section('title', 'Akses Ditolak — '.$sipengBranding['app_name'])

@section('content')
    <div class="text-center space-y-4 py-4">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-rose-100 text-rose-700">
            <x-sipeng.icon name="shield" class="h-7 w-7" />
        </div>
        <h2 class="text-lg font-bold text-slate-900">Akses ditolak</h2>
        <p class="text-sm text-slate-600">Anda tidak memiliki izin untuk mengakses halaman ini.</p>
        @auth
            <a href="{{ route('dashboard') }}" class="sipeng-btn-primary w-full">Kembali ke Dashboard</a>
        @else
            <a href="{{ route('login') }}" class="sipeng-btn-primary w-full">Masuk</a>
        @endauth
    </div>
@endsection
