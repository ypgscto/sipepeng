<x-app-layout>
    <x-slot name="header">Detail Publikasi</x-slot>
    <div class="sipeng-page space-y-6">
        <x-sipeng.page-header :title="$record->judul" :description="$record->registration_number">
            <x-slot name="actions">
                <a href="{{ route('admin.publications.index') }}" class="sipeng-btn-secondary text-sm">Daftar</a>
                @if($canEdit)<a href="{{ route('admin.publications.edit', $record) }}" class="sipeng-btn-primary text-sm">Edit</a>@endif
                @if($canSubmit)
                <form method="POST" action="{{ route('admin.publications.submit', $record) }}" onsubmit="return confirm('Ajukan publikasi?')">@csrf<button class="sipeng-btn-primary text-sm">Ajukan</button></form>
                @endif
            </x-slot>
        </x-sipeng.page-header>
        <div class="sipeng-card"><div class="sipeng-card-body">
            @include('admin.publications.partials.status-badge', ['record'=>$record])
            <dl class="grid sm:grid-cols-2 gap-3 mt-4 text-sm">
                <div><dt class="text-slate-500">Jenis</dt><dd>{{ $record->publicationType?->name }}</dd></div>
                <div><dt class="text-slate-500">Prodi</dt><dd>{{ $record->prodi_nama_snapshot }}</dd></div>
                <div><dt class="text-slate-500">Jurnal</dt><dd>{{ $record->journal_or_publisher ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">Sumber</dt><dd>{{ $record->source_type }} @if($record->proposal_judul_snapshot)— {{ Str::limit($record->proposal_judul_snapshot,40) }}@endif</dd></div>
            </dl>
            @if($record->authors->isNotEmpty())
            <div class="mt-4"><h4 class="font-semibold text-sm">Penulis</h4>
                <ul class="text-sm mt-1">@foreach($record->authors as $a)<li>{{ $a->dosen_nama_snapshot }} ({{ $a->role }})</li>@endforeach</ul>
            </div>@endif
        </div></div>
        <div class="sipeng-card"><div class="sipeng-card-body">
            <h3 class="font-semibold mb-2">Dokumen</h3>
            <ul class="text-sm space-y-1">
                @foreach(['file_manuscript'=>'Manuskrip','file_acceptance_letter'=>'Accept','file_published'=>'Terbit','file_other'=>'Lainnya'] as $f=>$l)
                <li class="flex justify-between"><span>{{ $l }}</span>@if($record->{$f})<a href="{{ route('admin.publications.download',[$record,$f]) }}" class="text-emerald-700 hover:underline">{{ $record->{$f.'_name'} }}</a>@else<span class="text-slate-400">—</span>@endif</li>
                @endforeach
            </ul>
        </div></div>
        @if($canVerify)
        <div class="sipeng-card border-indigo-200"><div class="sipeng-card-body">
            <h3 class="font-semibold mb-3">Verifikasi Admin</h3>
            <form method="POST" action="{{ route('admin.publications.verification.store', $record) }}" class="space-y-3">@csrf
                <select name="decision" class="sipeng-input" required><option value="verified">Terverifikasi</option><option value="revision_required">Revisi</option><option value="rejected">Tolak</option></select>
                <label class="inline-flex gap-2 text-sm"><input type="hidden" name="is_document_complete" value="0"><input type="checkbox" name="is_document_complete" value="1"> Dokumen lengkap</label>
                <textarea name="notes" rows="2" class="sipeng-input" placeholder="Catatan"></textarea>
                <button class="sipeng-btn-primary text-sm">Simpan Verifikasi</button>
            </form>
        </div></div>@endif
        <div class="sipeng-card"><div class="sipeng-card-body">
            <h3 class="font-semibold mb-2">Log Aktivitas</h3>
            <ul class="text-sm divide-y">@forelse($logs as $log)<li class="py-2">{{ $log->event }} — {{ $log->description }} <time class="block text-xs text-slate-500">{{ $log->created_at?->format('d M Y H:i') }}</time></li>@empty<li class="text-slate-500">Belum ada log.</li>@endforelse</ul>
        </div></div>
    </div>
</x-app-layout>
