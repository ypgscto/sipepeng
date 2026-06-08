@php
    $stats = $stats ?? [];
    $modules = $modules ?? [];
    $charts = $charts ?? [];
    $chartPayload = json_encode($charts, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
@endphp

<x-app-layout>
    <x-slot name="header">Dashboard</x-slot>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const data = {!! $chartPayload !!};
                const palette = ['#db2777','#ec4899','#831843','#f472b6','#be185d','#64748b','#f9a8d4','#9d174d'];
                const bar = (id, items, label) => {
                    const el = document.getElementById(id);
                    if (!el || !items?.length) {
                        if (el) el.parentElement.querySelector('.chart-empty')?.classList.remove('hidden');
                        return;
                    }
                    new Chart(el, {
                        type: 'bar',
                        data: {
                            labels: items.map(i => i.label),
                            datasets: [{ label, data: items.map(i => i.value), backgroundColor: palette, borderRadius: 4 }]
                        },
                        options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
                    });
                };
                bar('chartResearchProdi', data.research_by_prodi, 'Penelitian');
                bar('chartPkmProdi', data.pkm_by_prodi, 'PkM');
                bar('chartPubYear', data.publication_by_year, 'Publikasi');
                bar('chartOutputType', data.output_by_type, 'Luaran');
                bar('chartFunding', data.funding_by_category, 'Dana');
            });
        </script>
    @endpush

    <div class="sipeng-page space-y-6">
        <x-sipeng.page-header
            :title="'Dashboard '.$sipengBranding['app_name']"
            :description="$sipengBranding['app_subtitle'].' — Kinerja LPPM · SIAKAD: '.($siakadStatus ?? '—')"
        >
            <x-slot name="actions">
                @if(Route::has('admin.reports.index'))
                    <a href="{{ route('admin.reports.index') }}" class="sipeng-btn-primary text-sm">Laporan LPPM</a>
                @endif
            </x-slot>
        </x-sipeng.page-header>

        @if (! empty($scopeError))
            <x-sipeng.alert type="warning" :message="$scopeError" />
        @endif

        <div x-data="{ showFilters: false }">
            <button type="button" class="sipeng-btn-secondary text-sm w-full sm:hidden mb-2" @click="showFilters = !showFilters">
                Filter Dashboard
            </button>
            <div class="hidden sm:block" :class="showFilters && '!block'">
                @include('admin.reports.partials.filter-bar', [
                    'filterOptions' => $filterOptions ?? [],
                    'filters' => $filters ?? [],
                    'filter' => $filter ?? null,
                ])
            </div>
        </div>

        <section>
            <h3 class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-3">Ringkasan Kinerja LPPM</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4">
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
            <h3 class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-3">Grafik Kinerja</h3>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                @foreach ([
                    ['id' => 'chartResearchProdi', 'title' => 'Penelitian per Prodi'],
                    ['id' => 'chartPkmProdi', 'title' => 'PkM per Prodi'],
                    ['id' => 'chartPubYear', 'title' => 'Publikasi per Tahun'],
                    ['id' => 'chartOutputType', 'title' => 'Luaran per Jenis'],
                ] as $chart)
                    <div class="sipeng-card">
                        <div class="sipeng-card-body">
                            <p class="text-sm font-bold text-sipeng-navy-900 mb-3">{{ $chart['title'] }}</p>
                            <canvas id="{{ $chart['id'] }}" height="180"></canvas>
                            <p class="chart-empty hidden text-sm text-slate-500 text-center py-6">Belum ada data untuk ditampilkan.</p>
                        </div>
                    </div>
                @endforeach
                <div class="sipeng-card lg:col-span-2">
                    <div class="sipeng-card-body">
                        <p class="text-sm font-bold text-sipeng-navy-900 mb-3">Dana Internal vs Eksternal (RAB)</p>
                        <canvas id="chartFunding" height="120"></canvas>
                        <p class="chart-empty hidden text-sm text-slate-500 text-center py-6">Belum ada data untuk ditampilkan.</p>
                    </div>
                </div>
            </div>
        </section>

        <section>
            <h3 class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-3">Akses Cepat Modul</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach ($modules as $module)
                    @php
                        $routeName = $module['route'] ?? '';
                        $url = $routeName !== '' && Route::has($routeName) ? route($routeName) : '#';
                    @endphp
                    <a href="{{ $url }}" class="sipeng-card group hover:border-pink-300 hover:shadow-md transition border-l-4 border-l-pink-500">
                        <div class="sipeng-card-body flex items-center gap-3 py-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-pink-50 text-pink-800 border border-pink-100">
                                <x-sipeng.icon :name="$module['icon'] ?? 'link'" class="h-5 w-5" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-sipeng-navy-900 group-hover:text-pink-800">{{ $module['label'] }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">Buka modul</p>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    </div>
</x-app-layout>
