<x-app-layout>
    <x-slot name="header">Antrian Persetujuan Surat</x-slot>
    <div class="sipeng-page">
        <x-sipeng.page-header title="Antrian Persetujuan Surat" description="Surat menunggu persetujuan Ketua LPPM." />
        <div class="sipeng-card overflow-hidden">
            <div class="sipeng-card-body p-0">
                @if($records->isEmpty())<p class="p-8 text-center text-slate-500 text-sm">Tidak ada antrian.</p>
                @else
                <table class="min-w-full text-sm divide-y">
                    <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Nomor</th><th class="px-4 py-3 text-left">Jenis</th><th class="px-4 py-3 text-left">Perihal</th><th class="px-4 py-3 text-left">Diajukan</th><th class="px-4 py-3 text-right">Aksi</th></tr></thead>
                    <tbody class="divide-y">@foreach($records as $record)<tr>
                        <td class="px-4 py-3 font-mono text-xs">{{ $record->internal_number }}</td>
                        <td class="px-4 py-3">{{ $record->letterType?->name }}</td>
                        <td class="px-4 py-3">{{ Str::limit($record->perihal, 45) }}</td>
                        <td class="px-4 py-3">{{ $record->submitted_at?->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 text-right"><a href="{{ route('admin.letters.show', $record) }}" class="text-emerald-700 text-xs hover:underline">Proses</a></td>
                    </tr>@endforeach</tbody>
                </table>
                @if($records->hasPages())<div class="px-4 py-3">{{ $records->links() }}</div>@endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
