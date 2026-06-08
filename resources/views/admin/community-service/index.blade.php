<x-app-layout>
    <x-slot name="header">Pengabdian Masyarakat</x-slot>

    <div class="sipeng-page">
        <x-sipeng.page-header title="Proposal PkM" description="Pengajuan dan monitoring proposal pengabdian kepada masyarakat.">
            <x-slot name="actions">
                @if ($canCreate)
                    <a href="{{ route('admin.community-service.create') }}" class="sipeng-btn-primary text-sm">Buat Proposal</a>
                @endif
            </x-slot>
        </x-sipeng.page-header>

        <form method="GET">
            <x-sipeng.filter-panel title="Filter Proposal PkM">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label class="sipeng-label">Cari</label>
                        <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Judul, nomor, ketua, mitra..." class="sipeng-input">
                    </div>
                    <div>
                        <label class="sipeng-label">Status</label>
                        <select name="status" class="sipeng-input">
                            <option value="">Semua status</option>
                            @foreach ($statusOptions as $code => $meta)
                                <option value="{{ $code }}" @selected($filters['status'] === $code)>{{ $meta['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 mt-4">
                    <button type="submit" class="sipeng-btn-primary text-sm min-h-[40px] py-2">Terapkan Filter</button>
                    <a href="{{ route('admin.community-service.index') }}" class="sipeng-btn-secondary text-sm min-h-[40px] py-2">Reset</a>
                </div>
            </x-sipeng.filter-panel>
        </form>

        <div class="sipeng-card overflow-hidden">
            <div class="sipeng-card-body p-0">
                @if ($proposals->isEmpty())
                    <div class="p-10 text-center">
                        <p class="text-slate-600 text-sm">Belum ada proposal PkM.</p>
                        @if ($canCreate)
                            <a href="{{ route('admin.community-service.create') }}" class="sipeng-btn-primary text-sm mt-4 inline-flex">Buat Proposal Pertama</a>
                        @endif
                    </div>
                @else
                    <div class="sm:hidden divide-y divide-slate-100">
                        @foreach ($proposals as $proposal)
                            <div class="p-4 space-y-2">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="font-semibold text-slate-900 text-sm">{{ Str::limit($proposal->judul, 80) }}</p>
                                    @include('admin.community-service.partials.status-badge', ['proposal' => $proposal])
                                </div>
                                <p class="text-xs font-mono text-slate-500">{{ $proposal->proposal_number }}</p>
                                <p class="text-xs text-slate-600">{{ $proposal->ketua_dosen_nama_snapshot }} · {{ $proposal->mitra_nama_snapshot }}</p>
                                <x-sipeng.table-action :href="route('admin.community-service.show', $proposal)" class="mt-2" />
                            </div>
                        @endforeach
                    </div>

                    <div class="hidden sm:block sipeng-table-wrap">
                        <table class="sipeng-table">
                            <thead>
                                <tr>
                                    <th>Nomor</th>
                                    <th>Judul</th>
                                    <th>Ketua</th>
                                    <th>Mitra</th>
                                    <th>Status</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach ($proposals as $proposal)
                                    <tr>
                                        <td class="font-mono text-xs text-slate-600">{{ $proposal->proposal_number }}</td>
                                        <td class="max-w-xs"><span class="line-clamp-2" title="{{ $proposal->judul }}">{{ $proposal->judul }}</span></td>
                                        <td>{{ $proposal->ketua_dosen_nama_snapshot }}</td>
                                        <td class="text-slate-600">{{ $proposal->mitra_nama_snapshot }}</td>
                                        <td>@include('admin.community-service.partials.status-badge', ['proposal' => $proposal])</td>
                                        <td class="text-right">
                                            <x-sipeng.table-action :href="route('admin.community-service.show', $proposal)" />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($proposals->hasPages())
                        <div class="px-4 py-3 border-t border-slate-100">{{ $proposals->links() }}</div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
