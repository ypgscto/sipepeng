@php
    $codeValue = $record->{$entityKey === 'document-templates' ? 'template_code' : ($entityKey === 'reviewers' ? 'id' : 'code')} ?? '—';
@endphp

<x-app-layout>
    <x-slot name="header">Detail {{ $label }}</x-slot>

    <div class="sipeng-page space-y-6">
        <x-sipeng.page-header :title="$label" :description="'Detail data master.'">
            <x-slot name="actions">
                <a href="{{ route('admin.master.'.$entityKey.'.index') }}" class="sipeng-btn-secondary text-sm">Daftar</a>
                @if ($canManage && ! $record->trashed())
                    <a href="{{ route('admin.master.'.$entityKey.'.edit', $record) }}" class="sipeng-btn-primary text-sm">Edit</a>
                @endif
            </x-slot>
        </x-sipeng.page-header>

        @if ($record->trashed())
            <div class="sipeng-card border-amber-200 bg-amber-50">
                <div class="sipeng-card-body flex flex-wrap items-center justify-between gap-3 text-sm text-amber-900">
                    <span>Data ini telah dihapus (soft delete).</span>
                    @if ($canManage)
                        <form method="POST" action="{{ route('admin.master.'.$entityKey.'.restore', $record) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="sipeng-btn-primary text-sm">Pulihkan</button>
                        </form>
                    @endif
                </div>
            </div>
        @endif

        <div class="sipeng-card">
            <div class="sipeng-card-body">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    @if ($entityKey === 'reviewers')
                        <div>
                            <dt class="text-slate-500">Reviewer</dt>
                            <dd class="font-medium text-slate-900">{{ $record->user?->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Email</dt>
                            <dd class="text-slate-900">{{ $record->user?->email }}</dd>
                        </div>
                    @else
                        @if ($entityKey !== 'reviewers')
                            <div>
                                <dt class="text-slate-500">Kode</dt>
                                <dd class="font-mono text-slate-900">{{ $codeValue }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-slate-500">Nama</dt>
                            <dd class="font-medium text-slate-900">{{ $record->name ?? '—' }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-slate-500">Status</dt>
                        <dd>@include('admin.master.partials.status-badge', ['active' => $record->is_active])</dd>
                    </div>
                    @if ($record->description)
                        <div class="sm:col-span-2">
                            <dt class="text-slate-500">Deskripsi</dt>
                            <dd class="text-slate-900 whitespace-pre-line">{{ $record->description }}</dd>
                        </div>
                    @endif
                    @if ($entityKey === 'document-templates' && $record->file_name)
                        <div class="sm:col-span-2">
                            <dt class="text-slate-500">Berkas</dt>
                            <dd>
                                <a href="{{ route('admin.master.document-templates.download', $record) }}" class="text-emerald-700 hover:underline">
                                    {{ $record->file_name }} ({{ number_format($record->file_size / 1024, 1) }} KB)
                                </a>
                            </dd>
                        </div>
                    @endif
                    @if ($record instanceof \App\Models\Lppm\ResearchScheme || $record instanceof \App\Models\Lppm\CommunityServiceScheme)
                        <div class="sm:col-span-2">
                            <dt class="text-slate-500">Sumber Dana</dt>
                            <dd class="text-slate-900">
                                {{ $record->fundingSources->pluck('name')->join(', ') ?: '—' }}
                            </dd>
                        </div>
                    @endif
                    @if ($record instanceof \App\Models\Lppm\Reviewer)
                        <div>
                            <dt class="text-slate-500">Rumpun Ilmu</dt>
                            <dd class="text-slate-900">{{ $record->scienceCluster?->name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Bidang Fokus</dt>
                            <dd class="text-slate-900">{{ $record->focusArea?->name ?? '—' }}</dd>
                        </div>
                    @endif
                </dl>

                @if ($canManage && ! $record->trashed())
                    <div class="mt-6 pt-4 border-t border-slate-200 flex flex-wrap gap-2">
                        <form method="POST" action="{{ route('admin.master.'.$entityKey.'.toggle-active', $record) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="sipeng-btn-secondary text-sm">
                                {{ $record->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.master.'.$entityKey.'.destroy', $record) }}"
                            onsubmit="return confirm('Hapus data ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg bg-red-50 text-red-700 hover:bg-red-100 border border-red-200">
                                Hapus
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        <div class="sipeng-card">
            <div class="sipeng-card-body">
                <h3 class="font-semibold text-slate-900 mb-4">Log Aktivitas</h3>
                @if ($logs->isEmpty())
                    <p class="text-sm text-slate-500">Belum ada aktivitas tercatat.</p>
                @else
                    <ul class="divide-y divide-slate-100 text-sm">
                        @foreach ($logs as $log)
                            <li class="py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                <div>
                                    <span class="font-medium text-slate-800">{{ $log->event }}</span>
                                    @if ($log->description)
                                        <span class="text-slate-600"> — {{ $log->description }}</span>
                                    @endif
                                </div>
                                <time class="text-xs text-slate-500">{{ $log->created_at?->format('d M Y H:i') }}</time>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
