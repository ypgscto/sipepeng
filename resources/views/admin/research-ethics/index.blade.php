<x-app-layout>
    <x-slot name="header">Etik Penelitian</x-slot>

    <div class="sipeng-page">
        <x-sipeng.page-header title="Etik Penelitian" description="Pengajuan dan monitoring etik penelitian LPPM.">
            <x-slot name="actions">
                @if ($canCreate)
                    <a href="{{ route('admin.research-ethics.create') }}" class="sipeng-btn-primary text-sm">Ajukan Etik</a>
                @endif
            </x-slot>
        </x-sipeng.page-header>

        <form method="GET" class="sipeng-card mb-4">
            <div class="sipeng-card-body grid sm:grid-cols-4 gap-3">
                <input
                    type="text"
                    name="q"
                    value="{{ $filters['q'] ?? '' }}"
                    class="sipeng-input sm:col-span-2"
                    placeholder="Cari nomor, judul proposal..."
                >
                <select name="status" class="sipeng-input">
                    <option value="">Semua status</option>
                    @foreach ($statusOptions as $code => $meta)
                        <option value="{{ $code }}" @selected(($filters['status'] ?? '') === $code)>{{ $meta['label'] }}</option>
                    @endforeach
                </select>
                <button type="submit" class="sipeng-btn-primary text-sm">Filter</button>
            </div>
        </form>

        <div class="sipeng-card overflow-hidden">
            <div class="sipeng-card-body p-0">
                @if ($records->isEmpty())
                    <div class="p-10 text-center">
                        <p class="text-slate-600 text-sm">Belum ada aplikasi etik.</p>
                        @if ($canCreate)
                            <a href="{{ route('admin.research-ethics.create') }}" class="sipeng-btn-primary text-sm mt-4 inline-flex">Ajukan Etik Pertama</a>
                        @endif
                    </div>
                @else
                    <div class="sipeng-table-wrap">
                        <table class="sipeng-table">
                            <thead>
                                <tr>
                                    <th>Nomor</th>
                                    <th>Proposal</th>
                                    <th>Status</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach ($records as $record)
                                    <tr>
                                        <td class="font-mono text-xs text-slate-600">{{ $record->application_number }}</td>
                                        <td class="max-w-xs">
                                            <span class="line-clamp-2" title="{{ $record->proposal_judul_snapshot }}">
                                                {{ Str::limit($record->proposal_judul_snapshot, 45) }}
                                            </span>
                                        </td>
                                        <td>@include('admin.research-ethics.partials.status-badge', ['record' => $record])</td>
                                        <td class="text-right">
                                            <x-sipeng.table-action :href="route('admin.research-ethics.show', $record)" />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($records->hasPages())
                        <div class="px-4 py-3 border-t border-slate-100">{{ $records->links() }}</div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
