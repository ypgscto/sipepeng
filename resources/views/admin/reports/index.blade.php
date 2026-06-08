<x-app-layout>
    <x-slot name="header">Laporan LPPM</x-slot>
    <div class="sipeng-page">
        <x-sipeng.page-header title="Laporan & Kinerja LPPM" description="Laporan transaksi lokal SiPepeng dengan referensi SIAKAD. Siap untuk kebutuhan akreditasi." />
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($types as $key => $type)
                <a href="{{ route('admin.reports.show', $key) }}" class="sipeng-card group hover:border-emerald-300 transition">
                    <div class="sipeng-card-body flex items-center gap-3">
                        <x-sipeng.icon :name="$type['icon'] ?? 'document'" class="h-8 w-8 text-emerald-700" />
                        <div>
                            <p class="font-semibold text-slate-900 group-hover:text-emerald-800">{{ $type['label'] }}</p>
                            <p class="text-xs text-slate-500">Lihat & export</p>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</x-app-layout>
