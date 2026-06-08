@extends('layouts.public')

@section('title', 'Beranda — '.$sipengBranding['app_name'])

@section('content')
    <section class="bg-gradient-to-br from-pink-700 via-pink-600 to-sipeng-navy-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
            <div class="max-w-3xl">
                <p class="text-xs font-bold uppercase tracking-widest text-pink-200 mb-3">Portal Transparansi LPPM</p>
                <h1 class="text-3xl sm:text-4xl font-bold leading-tight">{{ $sipengBranding['app_name'] }}</h1>
                <p class="mt-3 text-base sm:text-lg text-pink-50/90">{{ $sipengBranding['app_subtitle'] }}</p>
                <p class="mt-2 text-sm text-pink-100/80">{{ $sipengBranding['institution_name'] }} — Ringkasan kinerja penelitian, pengabdian, publikasi, dan HKI yang telah divalidasi.</p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('public.dashboard') }}" class="inline-flex items-center justify-center rounded-lg bg-white px-5 py-2.5 text-sm font-bold text-pink-800 shadow hover:bg-pink-50 transition">
                        Lihat Dashboard Umum
                    </a>
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-lg border border-white/40 px-5 py-2.5 text-sm font-semibold text-white hover:bg-white/10 transition">
                        Login SiPepeng
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 sm:-mt-10 pb-12">
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            @foreach ($stats as $stat)
                <x-sipeng.stat-card
                    :label="$stat['label']"
                    :value="$stat['value']"
                    :icon="$stat['icon']"
                    :tone="$stat['tone']"
                />
            @endforeach
        </div>

        <div class="mt-10 sipeng-card border-l-4 border-l-pink-500 mb-4">
            <div class="sipeng-card-body py-6 sm:py-8 text-center sm:text-left">
                <h2 class="text-lg font-bold text-sipeng-navy-900">Ingin melihat detail kinerja LPPM?</h2>
                <p class="mt-2 text-sm text-slate-600 max-w-2xl">Dashboard Umum menampilkan statistik agregat, grafik tren tahunan, dan informasi fokus LPPM tanpa data pribadi dosen atau rincian anggaran.</p>
                <div class="mt-5 flex flex-wrap gap-3 justify-center sm:justify-start">
                    <a href="{{ route('public.dashboard', ['year' => $filter->year]) }}" class="sipeng-btn-primary text-sm">Buka Dashboard Umum</a>
                    <a href="{{ route('login') }}" class="sipeng-btn-secondary text-sm">Masuk untuk Akses Internal</a>
                </div>
            </div>
        </div>
    </section>
@endsection
