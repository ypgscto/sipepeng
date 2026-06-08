<x-app-layout>
    <x-slot name="header">Antrian Verifikasi Publikasi</x-slot>
    <div class="sipeng-page">
        <x-sipeng.page-header title="Antrian Verifikasi Publikasi" />
        <div class="sipeng-card"><div class="sipeng-card-body p-0">
            <table class="min-w-full text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Nomor</th><th class="px-4 py-3 text-left">Judul</th><th class="px-4 py-3 text-right">Aksi</th></tr></thead>
            <tbody class="divide-y">@forelse($records as $r)<tr><td class="px-4 py-3 font-mono text-xs">{{ $r->registration_number }}</td><td class="px-4 py-3">{{ Str::limit($r->judul,50) }}</td><td class="px-4 py-3 text-right"><a href="{{ route('admin.publications.show',$r) }}" class="text-emerald-700 text-xs">Verifikasi</a></td></tr>@empty<tr><td colspan="3" class="px-4 py-8 text-center text-slate-500">Kosong</td></tr>@endforelse</tbody></table>
        </div></div>
    </div>
</x-app-layout>
