<x-app-layout>
    <x-slot name="header">{{ $label }}</x-slot>

    <div class="sipeng-page">
        <x-sipeng.page-header :title="$label" :description="'Kelola data master '.strtolower($label).'.'">
            <x-slot name="actions">
                <a href="{{ route('admin.master.index') }}" class="sipeng-btn-secondary text-sm">Kembali</a>
                @if ($canManage)
                    <a href="{{ route('admin.master.'.$entityKey.'.create') }}" class="sipeng-btn-primary text-sm">Tambah</a>
                @endif
            </x-slot>
        </x-sipeng.page-header>

        @include('admin.master.partials.filters')

        <div class="sipeng-card overflow-hidden">
            <div class="sipeng-card-body p-0">
                @if ($records->isEmpty())
                    <div class="p-10 text-center text-slate-600 text-sm">Belum ada data.</div>
                @else
                    <div class="sipeng-table-wrap">
                        <table class="sipeng-table">
                            <thead>
                                <tr>
                                    @if ($hasCode)
                                        <th>Kode</th>
                                    @endif
                                    <th>Nama</th>
                                    <th>Status</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach ($records as $record)
                                    <tr @class(['bg-rose-50/30' => $record->trashed()])>
                                        @if ($hasCode)
                                            <td class="font-mono text-xs text-slate-600">
                                                {{ $record->{$codeColumn} ?? '—' }}
                                            </td>
                                        @endif
                                        <td>
                                            @if ($entityKey === 'reviewers')
                                                <span class="font-medium text-slate-900">{{ $record->user?->name ?? '—' }}</span>
                                                <div class="text-xs text-slate-500">{{ $record->user?->email }}</div>
                                            @else
                                                <span class="font-medium text-slate-900">{{ $record->name }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @include('admin.master.partials.status-badge', ['active' => $record->is_active])
                                            @if ($record->trashed())
                                                <x-sipeng.status-badge label="Terhapus" variant="rejected" class="ml-1" />
                                            @endif
                                        </td>
                                        <td class="text-right whitespace-nowrap space-x-2">
                                            <x-sipeng.table-action :href="route('admin.master.'.$entityKey.'.show', $record)" />
                                            @if ($canManage)
                                                <a href="{{ route('admin.master.'.$entityKey.'.edit', $record) }}" class="sipeng-btn-secondary text-xs min-h-[36px] py-1.5 px-2.5 inline-flex">Edit</a>
                                            @endif
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
