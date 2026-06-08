<x-app-layout>
    <x-slot name="header">Surat LPPM</x-slot>
    <div class="sipeng-page">
        <x-sipeng.page-header title="Surat LPPM" description="Manajemen surat resmi penelitian, pengabdian, dan administrasi LPPM.">
            <x-slot name="actions">
                @if ($canCreate)<a href="{{ route('admin.letters.create') }}" class="sipeng-btn-primary text-sm">Buat Surat</a>@endif
            </x-slot>
        </x-sipeng.page-header>
        <form method="GET" class="sipeng-card mb-4">
            <div class="sipeng-card-body grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <div class="sm:col-span-2"><label class="sipeng-label">Cari</label><input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="sipeng-input" placeholder="Perihal, nomor..."></div>
                <div><label class="sipeng-label">Status</label><select name="status" class="sipeng-input"><option value="">Semua</option>@foreach($statusOptions as $c=>$m)<option value="{{ $c }}" @selected(($filters['status']??'')===$c)>{{ $m['label'] }}</option>@endforeach</select></div>
                <div><label class="sipeng-label">Jenis</label><select name="letter_type_id" class="sipeng-input"><option value="">Semua</option>@foreach($letterTypes as $t)<option value="{{ $t->id }}" @selected(($filters['letter_type_id']??'')==$t->id)>{{ $t->name }}</option>@endforeach</select></div>
                <div><label class="sipeng-label">Tahun</label><input type="number" name="tahun" value="{{ $filters['tahun'] ?? '' }}" class="sipeng-input"></div>
                <div class="sm:col-span-2 flex gap-2"><button class="sipeng-btn-primary text-sm">Filter</button><a href="{{ route('admin.letters.index') }}" class="sipeng-btn-secondary text-sm">Reset</a></div>
            </div>
        </form>
        <div class="sipeng-card overflow-hidden">
            <div class="sipeng-card-body p-0">
                @if($records->isEmpty())<p class="p-8 text-center text-slate-500 text-sm">Belum ada surat.</p>
                @else
                <table class="min-w-full text-sm divide-y">
                    <thead class="bg-slate-50"><tr>
                        <th class="px-4 py-3 text-left">Nomor</th><th class="px-4 py-3 text-left">Jenis</th><th class="px-4 py-3 text-left">Perihal</th><th class="px-4 py-3 text-left">Tanggal</th><th class="px-4 py-3 text-left">Status</th><th class="px-4 py-3 text-right">Aksi</th>
                    </tr></thead>
                    <tbody class="divide-y">
                        @foreach($records as $record)
                        <tr>
                            <td class="px-4 py-3 font-mono text-xs">{{ $record->displayNumber() }}</td>
                            <td class="px-4 py-3">{{ $record->letterType?->name }}</td>
                            <td class="px-4 py-3">{{ Str::limit($record->perihal, 45) }}</td>
                            <td class="px-4 py-3">{{ $record->letter_date?->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">@include('admin.letters.partials.status-badge', ['record'=>$record])</td>
                            <td class="px-4 py-3 text-right"><a href="{{ route('admin.letters.show', $record) }}" class="text-emerald-700 text-xs hover:underline">Detail</a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @if($records->hasPages())<div class="px-4 py-3">{{ $records->links() }}</div>@endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
