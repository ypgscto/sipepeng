@php
    $exportQuery = http_build_query(array_filter($filters ?? []));
@endphp
<x-app-layout>
    <x-slot name="header">Laporan {{ $meta['label'] ?? $type }}</x-slot>
    <div class="sipeng-page space-y-4">
        <x-sipeng.page-header :title="$meta['label'] ?? $type" description="Data transaksi SiPepeng · filter referensi SIAKAD">
            <x-slot name="actions">
                <a href="{{ route('admin.reports.index') }}" class="sipeng-btn-secondary text-sm">Daftar Laporan</a>
                @if($canExport ?? false)
                    <a href="{{ route('admin.reports.export.excel', $type).'?'.$exportQuery }}" class="sipeng-btn-primary text-sm">Export Excel</a>
                    <a href="{{ route('admin.reports.export.pdf', $type).'?'.$exportQuery }}" class="sipeng-btn-secondary text-sm" target="_blank">Export PDF</a>
                @endif
            </x-slot>
        </x-sipeng.page-header>

        @include('admin.reports.partials.filter-bar')

        <div class="sipeng-card overflow-hidden">
            <div class="sipeng-card-body p-0 overflow-x-auto">
                @if($type === 'research')
                    <table class="min-w-full text-sm divide-y">
                        <thead class="bg-slate-50"><tr>
                            <th class="px-4 py-3 text-left">Nomor</th><th class="px-4 py-3 text-left">Prodi</th><th class="px-4 py-3 text-left">Judul</th><th class="px-4 py-3 text-left">Ketua</th><th class="px-4 py-3 text-left">Status</th><th class="px-4 py-3 text-right">RAB</th>
                        </tr></thead>
                        <tbody class="divide-y">
                            @forelse($records as $r)
                            <tr>
                                <td class="px-4 py-3 font-mono text-xs">{{ $r->proposal_number }}</td>
                                <td class="px-4 py-3">{{ $r->prodi_nama_snapshot }}</td>
                                <td class="px-4 py-3">{{ Str::limit($r->judul, 50) }}</td>
                                <td class="px-4 py-3">{{ $r->ketua_dosen_nama_snapshot }}</td>
                                <td class="px-4 py-3">{{ $r->status }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($r->total_rab, 0, ',', '.') }}</td>
                            </tr>
                            @empty<tr><td colspan="6" class="p-8 text-center text-slate-500">Tidak ada data.</td></tr>@endforelse
                        </tbody>
                    </table>
                    @if(method_exists($records, 'links'))<div class="px-4 py-3">{{ $records->links() }}</div>@endif
                @elseif($type === 'pkm')
                    <table class="min-w-full text-sm divide-y">
                        <thead class="bg-slate-50"><tr>
                            <th class="px-4 py-3 text-left">Nomor</th><th class="px-4 py-3 text-left">Mitra</th><th class="px-4 py-3 text-left">Judul</th><th class="px-4 py-3 text-left">Ketua</th><th class="px-4 py-3 text-left">Status</th><th class="px-4 py-3 text-right">RAB</th>
                        </tr></thead>
                        <tbody class="divide-y">
                            @forelse($records as $r)
                            <tr>
                                <td class="px-4 py-3 font-mono text-xs">{{ $r->proposal_number }}</td>
                                <td class="px-4 py-3">{{ $r->mitra_nama_snapshot }}</td>
                                <td class="px-4 py-3">{{ Str::limit($r->judul, 50) }}</td>
                                <td class="px-4 py-3">{{ $r->ketua_dosen_nama_snapshot }}</td>
                                <td class="px-4 py-3">{{ $r->status }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($r->total_rab, 0, ',', '.') }}</td>
                            </tr>
                            @empty<tr><td colspan="6" class="p-8 text-center text-slate-500">Tidak ada data.</td></tr>@endforelse
                        </tbody>
                    </table>
                    @if(method_exists($records, 'links'))<div class="px-4 py-3">{{ $records->links() }}</div>@endif
                @elseif($type === 'publications')
                    <table class="min-w-full text-sm divide-y">
                        <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Nomor</th><th class="px-4 py-3 text-left">Judul</th><th class="px-4 py-3 text-left">Jenis</th><th class="px-4 py-3 text-left">Tahun</th><th class="px-4 py-3 text-left">Status</th></tr></thead>
                        <tbody class="divide-y">@forelse($records as $r)<tr><td class="px-4 py-3 font-mono text-xs">{{ $r->registration_number }}</td><td class="px-4 py-3">{{ Str::limit($r->judul, 50) }}</td><td class="px-4 py-3">{{ $r->publicationType?->name }}</td><td class="px-4 py-3">{{ $r->publication_year }}</td><td class="px-4 py-3">{{ $r->status }}</td></tr>@empty<tr><td colspan="5" class="p-8 text-center text-slate-500">Tidak ada data.</td></tr>@endforelse</tbody>
                    </table>
                    @if(method_exists($records, 'links'))<div class="px-4 py-3">{{ $records->links() }}</div>@endif
                @elseif($type === 'hki')
                    <table class="min-w-full text-sm divide-y">
                        <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Nomor</th><th class="px-4 py-3 text-left">Judul</th><th class="px-4 py-3 text-left">Jenis</th><th class="px-4 py-3 text-left">Prodi</th><th class="px-4 py-3 text-left">Status</th></tr></thead>
                        <tbody class="divide-y">@forelse($records as $r)<tr><td class="px-4 py-3 font-mono text-xs">{{ $r->registration_number }}</td><td class="px-4 py-3">{{ Str::limit($r->judul, 50) }}</td><td class="px-4 py-3">{{ $r->ipType?->name }}</td><td class="px-4 py-3">{{ $r->prodi_nama_snapshot }}</td><td class="px-4 py-3">{{ $r->status }}</td></tr>@empty<tr><td colspan="5" class="p-8 text-center text-slate-500">Tidak ada data.</td></tr>@endforelse</tbody>
                    </table>
                    @if(method_exists($records, 'links'))<div class="px-4 py-3">{{ $records->links() }}</div>@endif
                @elseif($type === 'ethics')
                    <table class="min-w-full text-sm divide-y">
                        <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Nomor</th><th class="px-4 py-3 text-left">Proposal</th><th class="px-4 py-3 text-left">Ketua</th><th class="px-4 py-3 text-left">Risiko</th><th class="px-4 py-3 text-left">Status</th></tr></thead>
                        <tbody class="divide-y">@forelse($records as $r)<tr><td class="px-4 py-3 font-mono text-xs">{{ $r->application_number }}</td><td class="px-4 py-3">{{ Str::limit($r->proposal_judul_snapshot, 45) }}</td><td class="px-4 py-3">{{ $r->ketua_dosen_nama_snapshot }}</td><td class="px-4 py-3">{{ $r->risk_level }}</td><td class="px-4 py-3">{{ $r->status }}</td></tr>@empty<tr><td colspan="5" class="p-8 text-center text-slate-500">Tidak ada data.</td></tr>@endforelse</tbody>
                    </table>
                    @if(method_exists($records, 'links'))<div class="px-4 py-3">{{ $records->links() }}</div>@endif
                @elseif($type === 'partners')
                    <table class="min-w-full text-sm divide-y">
                        <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Kode</th><th class="px-4 py-3 text-left">Nama</th><th class="px-4 py-3 text-left">Jenis</th><th class="px-4 py-3 text-left">Kota</th><th class="px-4 py-3 text-right">PkM</th></tr></thead>
                        <tbody class="divide-y">@forelse($records as $r)<tr><td class="px-4 py-3">{{ $r->partner_code }}</td><td class="px-4 py-3">{{ $r->name }}</td><td class="px-4 py-3">{{ $r->partnerType?->name }}</td><td class="px-4 py-3">{{ $r->city }}</td><td class="px-4 py-3 text-right">{{ $r->pkm_count ?? 0 }}</td></tr>@empty<tr><td colspan="5" class="p-8 text-center text-slate-500">Tidak ada data.</td></tr>@endforelse</tbody>
                    </table>
                    @if(method_exists($records, 'links'))<div class="px-4 py-3">{{ $records->links() }}</div>@endif
                @elseif($type === 'funding')
                    <table class="min-w-full text-sm divide-y">
                        <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Modul</th><th class="px-4 py-3 text-left">Nomor</th><th class="px-4 py-3 text-left">Judul</th><th class="px-4 py-3 text-left">Prodi</th><th class="px-4 py-3 text-right">RAB</th><th class="px-4 py-3 text-left">Status</th></tr></thead>
                        <tbody class="divide-y">@forelse($records as $r)<tr><td class="px-4 py-3">{{ $r->modul }}</td><td class="px-4 py-3 font-mono text-xs">{{ $r->nomor }}</td><td class="px-4 py-3">{{ Str::limit($r->judul, 40) }}</td><td class="px-4 py-3">{{ $r->prodi }}</td><td class="px-4 py-3 text-right">{{ number_format($r->total_rab, 0, ',', '.') }}</td><td class="px-4 py-3">{{ $r->status }}</td></tr>@empty<tr><td colspan="6" class="p-8 text-center text-slate-500">Tidak ada data.</td></tr>@endforelse</tbody>
                    </table>
                @elseif($type === 'lecturer-performance')
                    <table class="min-w-full text-sm divide-y">
                        <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Dosen ID</th><th class="px-4 py-3 text-left">Nama</th><th class="px-4 py-3 text-right">Penelitian</th><th class="px-4 py-3 text-right">PkM</th><th class="px-4 py-3 text-right">Publikasi</th><th class="px-4 py-3 text-right">HKI</th></tr></thead>
                        <tbody class="divide-y">@forelse($records as $r)<tr><td class="px-4 py-3 font-mono text-xs">{{ $r['dosen_id'] }}</td><td class="px-4 py-3">{{ $r['nama'] }}</td><td class="px-4 py-3 text-right">{{ $r['penelitian'] }}</td><td class="px-4 py-3 text-right">{{ $r['pkm'] }}</td><td class="px-4 py-3 text-right">{{ $r['publikasi'] }}</td><td class="px-4 py-3 text-right">{{ $r['hki'] }}</td></tr>@empty<tr><td colspan="6" class="p-8 text-center text-slate-500">Tidak ada data.</td></tr>@endforelse</tbody>
                    </table>
                @elseif($type === 'prodi-performance')
                    <table class="min-w-full text-sm divide-y">
                        <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Prodi</th><th class="px-4 py-3 text-right">Penelitian</th><th class="px-4 py-3 text-right">PkM</th><th class="px-4 py-3 text-right">Publikasi</th><th class="px-4 py-3 text-right">HKI</th><th class="px-4 py-3 text-right">Total RAB</th></tr></thead>
                        <tbody class="divide-y">@forelse($records as $r)<tr><td class="px-4 py-3">{{ $r['prodi_nama'] }}</td><td class="px-4 py-3 text-right">{{ $r['penelitian'] }}</td><td class="px-4 py-3 text-right">{{ $r['pkm'] }}</td><td class="px-4 py-3 text-right">{{ $r['publikasi'] }}</td><td class="px-4 py-3 text-right">{{ $r['hki'] }}</td><td class="px-4 py-3 text-right">{{ number_format($r['rab'], 0, ',', '.') }}</td></tr>@empty<tr><td colspan="6" class="p-8 text-center text-slate-500">Tidak ada data.</td></tr>@endforelse</tbody>
                    </table>
                @elseif($type === 'accreditation')
                    <table class="min-w-full text-sm divide-y">
                        <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Kode</th><th class="px-4 py-3 text-left">Indikator Akreditasi</th><th class="px-4 py-3 text-left">Modul</th><th class="px-4 py-3 text-right">Nilai</th><th class="px-4 py-3 text-left">Satuan</th></tr></thead>
                        <tbody class="divide-y">@forelse($records as $r)<tr><td class="px-4 py-3 font-mono text-xs">{{ $r['code'] }}</td><td class="px-4 py-3">{{ $r['label'] }}</td><td class="px-4 py-3">{{ $r['module'] }}</td><td class="px-4 py-3 text-right font-semibold">{{ is_float($r['value']) ? number_format($r['value'], 0, ',', '.') : $r['value'] }}</td><td class="px-4 py-3">{{ $r['unit'] }}</td></tr>@empty<tr><td colspan="5" class="p-8 text-center text-slate-500">Tidak ada data.</td></tr>@endforelse</tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
