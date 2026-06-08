@extends('layouts.guest')

@section('title', 'Kesalahan Server — '.$sipengBranding['app_name'])

@section('content')
    <div class="text-center space-y-4 py-4">
        <p class="text-5xl font-bold text-sipeng-navy-900">500</p>
        <h2 class="text-lg font-bold text-slate-900">Terjadi kesalahan sistem</h2>
        <p class="text-sm text-slate-600">Tim IT sedang menangani gangguan ini. Silakan coba beberapa saat lagi.</p>
        <a href="{{ url('/') }}" class="sipeng-btn-primary w-full">Kembali ke Beranda</a>
    </div>
@endsection
