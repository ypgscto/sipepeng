<x-app-layout>
    <x-slot name="header">Publikasi Ilmiah</x-slot>
    <div class="sipeng-page">
        <x-sipeng.page-header title="Publikasi Ilmiah" description="Repositori publikasi dosen STIKES Gunung Sari.">
            <x-slot name="actions">
                @if ($canCreate)<a href="{{ route('admin.publications.create') }}" class="sipeng-btn-primary text-sm">Tambah Publikasi</a>@endif
            </x-slot>
        </x-sipeng.page-header>
        <form method="GET" class="sipeng-card mb-4">
            <div class="sipeng-card-body grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                <div class="sm:col-span-2"><label class="sipeng-label">Cari</label><input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="sipeng-input" placeholder="Judul, nomor..."></div>
                <div><label class="sipeng-label">Status</label><select name="status" class="sipeng-input"><option value="">Semua</option>@foreach($statusOptions as $c=>$m)<option value="{{ $c }}" @selected(($filters['status']??'')===$c)>{{ $m['label'] }}</option>@endforeach</select></div>
                <div><label class="sipeng-label">Tahun</label><input type="number" name="tahun" value="{{ $filters['tahun'] ?? '' }}" class="sipeng-input"></div>
                <div><label class="sipeng-label">Prodi</label><select name="prodi_id" class="sipeng-input"><option value="">Semua</option>@foreach($prodiOptions as $o)<option value="{{ $o['value'] }}" @selected(($filters['prodi_id']??'')===$o['value'])>{{ $o['label'] }}</option>@endforeach</select></div>
                <div><label class="sipeng-label">Sumber</label><select name="source_type" class="sipeng-input"><option value="">Semua</option>@foreach(['standalone'=>'Mandiri','research'=>'Penelitian','community_service'=>'PkM'] as $k=>$l)<option value="{{ $k }}" @selected(($filters['source_type']??'')===$k)>{{ $l }}</option>@endforeach</select></div>
                <div class="sm:col-span-3 flex gap-2"><button class="sipeng-btn-primary text-sm">Filter</button><a href="{{ route('admin.publications.index') }}" class="sipeng-btn-secondary text-sm">Reset</a></div>
            </div>
        </form>
        <div class="sipeng-card overflow-hidden">
            <div class="sipeng-card-body p-0">
                @if($records->isEmpty())<p class="p-8 text-center text-slate-500 text-sm">Belum ada publikasi.</p>
                @else
                <table class="min-w-full text-sm divide-y">
                    <thead class="bg-slate-50"><tr>
                        <th class="px-4 py-3 text-left">Nomor</th><th class="px-4 py-3 text-left">Judul</th><th class="px-4 py-3 text-left">Tahun</th><th class="px-4 py-3 text-left">Status</th><th class="px-4 py-3 text-right">Aksi</th>
                    </tr></thead>
                    <tbody class="divide-y">
                        @foreach($records as $record)
                        <tr><td class="px-4 py-3 font-mono text-xs">{{ $record->registration_number }}</td>
                            <td class="px-4 py-3">{{ Str::limit($record->judul, 50) }}</td>
                            <td class="px-4 py-3">{{ $record->publication_year }}</td>
                            <td class="px-4 py-3">@include('admin.publications.partials.status-badge', ['record'=>$record])</td>
                            <td class="px-4 py-3 text-right"><a href="{{ route('admin.publications.show', $record) }}" class="text-emerald-700 text-xs hover:underline">Detail</a></td></tr>
                        @endforeach
                    </tbody>
                </table>
                @if($records->hasPages())<div class="px-4 py-3">{{ $records->links() }}</div>@endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
