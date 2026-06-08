@extends('layouts.public')

@php
    $chartPayload = json_encode($charts ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
@endphp

@section('title', 'Dashboard Umum — '.$sipengBranding['app_name'])

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-pink-700 mb-1">Transparansi Publik</p>
                <h1 class="text-2xl sm:text-3xl font-bold text-sipeng-navy-900">Dashboard Umum</h1>
                <p class="mt-1 text-sm text-slate-600">Ringkasan kinerja LPPM tahun {{ $filter->year }} — data agregat tanpa informasi pribadi.</p>
            </div>
            @include('public.partials.year-filter', [
                'yearOptions' => $yearOptions,
                'filter' => $filter,
            ])
        </div>

        <x-sipeng.alert type="info">
            Halaman ini bersifat baca-saja. Statistik hanya mencakup kegiatan dengan status disetujui atau terverifikasi. Data pribadi dan rincian anggaran (RAB) tidak ditampilkan.
        </x-sipeng.alert>

        <section>
            <h2 class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-3">Statistik Tahun {{ $filter->year }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach ($stats as $stat)
                    <x-sipeng.stat-card
                        :label="$stat['label']"
                        :value="$stat['value']"
                        :icon="$stat['icon']"
                        :tone="$stat['tone']"
                    />
                @endforeach
            </div>
        </section>

        <section>
            <h2 class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-3">Grafik Kinerja</h2>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                @foreach ([
                    ['id' => 'chartResearchYear', 'title' => 'Penelitian per Tahun', 'key' => 'research_by_year', 'label' => 'Penelitian'],
                    ['id' => 'chartPkmYear', 'title' => 'PkM per Tahun', 'key' => 'pkm_by_year', 'label' => 'PkM'],
                    ['id' => 'chartPubYear', 'title' => 'Publikasi per Tahun', 'key' => 'publications_by_year', 'label' => 'Publikasi'],
                    ['id' => 'chartOutputType', 'title' => 'Luaran per Jenis', 'key' => 'outputs_by_type', 'label' => 'Luaran'],
                ] as $chart)
                    <div class="sipeng-card">
                        <div class="sipeng-card-body">
                            <p class="text-sm font-bold text-sipeng-navy-900 mb-3">{{ $chart['title'] }}</p>
                            <canvas id="{{ $chart['id'] }}" height="180" data-chart-key="{{ $chart['key'] }}" data-chart-label="{{ $chart['label'] }}"></canvas>
                            <p class="chart-empty hidden text-sm text-slate-500 text-center py-6">Belum ada data untuk ditampilkan.</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="sipeng-card">
                <div class="sipeng-card-body">
                    <h2 class="text-sm font-bold text-sipeng-navy-900 mb-3">{{ $about['title'] ?? 'Tentang SiPepeng' }}</h2>
                    <p class="text-sm text-slate-600 leading-relaxed">{{ $about['body'] ?? '' }}</p>
                </div>
            </div>

            <div class="sipeng-card">
                <div class="sipeng-card-body">
                    <h2 class="text-sm font-bold text-sipeng-navy-900 mb-3">Fokus LPPM</h2>
                    @if (! empty($lppmFocus))
                        <ul class="space-y-2">
                            @foreach ($lppmFocus as $item)
                                <li class="flex gap-2 text-sm text-slate-600">
                                    <span class="text-pink-600 shrink-0">•</span>
                                    <span>{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-slate-500">Belum ada informasi fokus.</p>
                    @endif
                </div>
            </div>
        </section>

        <section>
            <h2 class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-3">Tema Unggulan</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @forelse ($featuredThemes as $theme)
                    <div class="sipeng-card border-t-4 border-t-pink-500">
                        <div class="sipeng-card-body py-4">
                            <p class="text-sm font-bold text-sipeng-navy-900">{{ $theme['title'] }}</p>
                            <p class="mt-1 text-xs text-slate-600">{{ $theme['description'] }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500 col-span-full">Belum ada tema unggulan.</p>
                @endforelse
            </div>
        </section>

        <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="sipeng-card">
                <div class="sipeng-card-body">
                    <h2 class="text-sm font-bold text-sipeng-navy-900 mb-3">Pengumuman</h2>
                    @forelse ($announcements as $announcement)
                        <div class="border-b border-slate-100 last:border-0 py-3 first:pt-0">
                            <p class="text-sm font-semibold text-sipeng-navy-900">{{ $announcement['title'] ?? 'Pengumuman' }}</p>
                            @if (! empty($announcement['date']))
                                <p class="text-xs text-slate-500 mt-0.5">{{ $announcement['date'] }}</p>
                            @endif
                            @if (! empty($announcement['body']))
                                <p class="text-sm text-slate-600 mt-1">{{ $announcement['body'] }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Belum ada pengumuman publik.</p>
                    @endforelse
                </div>
            </div>

            <div class="sipeng-card">
                <div class="sipeng-card-body">
                    <h2 class="text-sm font-bold text-sipeng-navy-900 mb-3">Kalender Kegiatan</h2>
                    @forelse ($calendarEvents as $event)
                        <div class="border-b border-slate-100 last:border-0 py-3 first:pt-0">
                            <p class="text-sm font-semibold text-sipeng-navy-900">{{ $event['title'] ?? 'Kegiatan' }}</p>
                            @if (! empty($event['date']))
                                <p class="text-xs text-pink-700 font-medium mt-0.5">{{ $event['date'] }}</p>
                            @endif
                            @if (! empty($event['description']))
                                <p class="text-sm text-slate-600 mt-1">{{ $event['description'] }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Belum ada kegiatan terjadwal.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const data = {!! $chartPayload !!};
            const palette = ['#db2777', '#ec4899', '#831843', '#f472b6', '#be185d', '#64748b', '#f9a8d4', '#9d174d'];

            document.querySelectorAll('canvas[data-chart-key]').forEach((el) => {
                const key = el.dataset.chartKey;
                const label = el.dataset.chartLabel || 'Data';
                const items = data[key] || [];

                if (!items.length) {
                    el.parentElement.querySelector('.chart-empty')?.classList.remove('hidden');
                    el.classList.add('hidden');
                    return;
                }

                new Chart(el, {
                    type: 'bar',
                    data: {
                        labels: items.map((i) => i.label),
                        datasets: [{
                            label,
                            data: items.map((i) => i.value),
                            backgroundColor: palette,
                            borderRadius: 4,
                        }],
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true } },
                    },
                });
            });
        });
    </script>
@endpush
