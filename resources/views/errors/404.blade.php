@extends('layouts.guest')

@section('title', 'Halaman Tidak Ditemukan — '.$sipengBranding['app_name'])

@section('content')
    <div class="text-center space-y-4 py-4">
        <p class="text-5xl font-bold text-sipeng-navy-900">404</p>
        <h2 class="text-lg font-bold text-slate-900">Halaman tidak ditemukan</h2>
        <p class="text-sm text-slate-600">URL yang Anda minta tidak tersedia atau telah dipindahkan.</p>
        <a href="{{ url('/') }}" class="sipeng-btn-primary w-full">Kembali ke Beranda</a>
    </div>
@endsection
