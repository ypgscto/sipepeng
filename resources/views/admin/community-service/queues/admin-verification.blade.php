<x-app-layout>
    <x-slot name="header">Antrian Verifikasi Admin PkM</x-slot>
    <div class="sipeng-page">
        <x-sipeng.page-header title="Antrian Verifikasi Administrasi PkM" />
        <div class="sipeng-card overflow-hidden">
            <div class="sipeng-card-body p-0">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50"><tr>
                        <th class="px-4 py-3 text-left">Nomor</th><th class="px-4 py-3 text-left">Judul</th>
                        <th class="px-4 py-3 text-left">Ketua</th><th class="px-4 py-3 text-left">Mitra</th><th class="px-4 py-3 text-right">Aksi</th>
                    </tr></thead>
                    <tbody class="divide-y">
                        @forelse ($proposals as $proposal)
                            <tr>
                                <td class="px-4 py-3 font-mono text-xs">{{ $proposal->proposal_number }}</td>
                                <td class="px-4 py-3">{{ Str::limit($proposal->judul, 50) }}</td>
                                <td class="px-4 py-3">{{ $proposal->ketua_dosen_nama_snapshot }}</td>
                                <td class="px-4 py-3">{{ $proposal->mitra_nama_snapshot }}</td>
                                <td class="px-4 py-3 text-right"><a href="{{ route('admin.community-service.show', $proposal) }}" class="text-emerald-700 text-xs hover:underline">Verifikasi</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Antrian kosong.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                @if ($proposals->hasPages())<div class="px-4 py-3">{{ $proposals->links() }}</div>@endif
            </div>
        </div>
    </div>
</x-app-layout>
