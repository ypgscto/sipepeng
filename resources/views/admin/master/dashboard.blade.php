<x-app-layout>
    <x-slot name="header">Data Master LPPM</x-slot>

    <div class="sipeng-page">
        <x-sipeng.page-header
            title="Data Master LPPM"
            description="Kelola referensi lokal modul penelitian, pengabdian, publikasi, HKI, dan dokumen LPPM."
        />

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach ($entities as $entity)
                <a href="{{ $entity['route'] }}"
                    class="sipeng-card hover:border-emerald-300 hover:shadow-md transition group">
                    <div class="sipeng-card-body">
                        <h3 class="font-semibold text-slate-900 group-hover:text-emerald-800">{{ $entity['label'] }}</h3>
                        <p class="mt-2 text-2xl font-bold text-emerald-700">{{ $entity['count'] }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $entity['active_count'] }} aktif</p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</x-app-layout>
