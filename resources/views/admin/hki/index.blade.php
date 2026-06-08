<x-app-layout>
    <x-slot name="header">HKI dan Paten</x-slot>

    <div class="sipeng-page">
        <x-sipeng.page-header title="HKI dan Paten" description="Pendaftaran dan monitoring HKI serta paten LPPM.">
            <x-slot name="actions">
                @if ($canCreate)
                    <a href="{{ route('admin.hki.create') }}" class="sipeng-btn-primary text-sm">Tambah HKI</a>
                @endif
            </x-slot>
        </x-sipeng.page-header>

        <form method="GET" class="sipeng-card mb-4">
            <div class="sipeng-card-body grid sm:grid-cols-4 gap-3">
                <input
                    type="text"
                    name="q"
                    value="{{ $filters['q'] ?? '' }}"
                    placeholder="Cari nomor, judul..."
                    class="sipeng-input sm:col-span-2"
                >
                <select name="status" class="sipeng-input">
                    <option value="">Semua status</option>
                    @foreach ($statusOptions as $code => $meta)
                        <option value="{{ $code }}" @selected(($filters['status'] ?? '') === $code)>{{ $meta['label'] }}</option>
                    @endforeach
                </select>
                <input
                    type="number"
                    name="tahun"
                    value="{{ $filters['tahun'] ?? '' }}"
                    placeholder="Tahun"
                    class="sipeng-input"
                >
                <button type="submit" class="sipeng-btn-primary text-sm sm:col-span-4 sm:w-auto">Filter</button>
            </div>
        </form>

        <div class="sipeng-card overflow-hidden">
            <div class="sipeng-card-body p-0">
                @if ($records->isEmpty())
                    <div class="p-10 text-center">
                        <p class="text-slate-600 text-sm">Belum ada HKI.</p>
                        @if ($canCreate)
                            <a href="{{ route('admin.hki.create') }}" class="sipeng-btn-primary text-sm mt-4 inline-flex">Tambah HKI Pertama</a>
                        @endif
                    </div>
                @else
                    <div class="sipeng-table-wrap">
                        <table class="sipeng-table">
                            <thead>
                                <tr>
                                    <th>Nomor</th>
                                    <th>Judul</th>
                                    <th>Status</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach ($records as $record)
                                    <tr>
                                        <td class="font-mono text-xs text-slate-600">{{ $record->registration_number }}</td>
                                        <td class="max-w-xs">
                                            <span class="line-clamp-2" title="{{ $record->judul }}">
                                                {{ Str::limit($record->judul, 50) }}
                                            </span>
                                        </td>
                                        <td>@include('admin.hki.partials.status-badge', ['record' => $record])</td>
                                        <td class="text-right">
                                            <x-sipeng.table-action :href="route('admin.hki.show', $record)" />
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
