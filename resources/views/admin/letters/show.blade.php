<x-app-layout>
    <x-slot name="header">Detail Surat</x-slot>
    <div class="sipeng-page space-y-6">
        <x-sipeng.page-header :title="$record->perihal" :description="$record->displayNumber()">
            <x-slot name="actions">
                <a href="{{ route('admin.letters.index') }}" class="sipeng-btn-secondary text-sm">Daftar</a>
                @if($canEdit)<a href="{{ route('admin.letters.edit', $record) }}" class="sipeng-btn-primary text-sm">Edit</a>@endif
                @if($canSubmit)
                <form method="POST" action="{{ route('admin.letters.submit', $record) }}" onsubmit="return confirm('Ajukan surat?')">@csrf<button class="sipeng-btn-primary text-sm">Ajukan</button></form>
                @endif
                <a href="{{ route('admin.letters.preview.pdf', $record) }}" target="_blank" class="sipeng-btn-secondary text-sm">Preview PDF</a>
                @if($record->file_pdf)<a href="{{ route('admin.letters.download.pdf', $record) }}" class="sipeng-btn-secondary text-sm">Unduh PDF</a>@endif
                @if($record->file_signed_scan)<a href="{{ route('admin.letters.download.signed', $record) }}" class="sipeng-btn-secondary text-sm">Unduh Scan TTD</a>@endif
            </x-slot>
        </x-sipeng.page-header>

        <div class="sipeng-card"><div class="sipeng-card-body">
            @include('admin.letters.partials.status-badge', ['record'=>$record])
            <dl class="grid sm:grid-cols-2 gap-3 mt-4 text-sm">
                <div><dt class="text-slate-500">Jenis</dt><dd>{{ $record->letterType?->name }}</dd></div>
                <div><dt class="text-slate-500">Tanggal</dt><dd>{{ $record->letter_date?->format('d M Y') }} — {{ $record->place_of_issue }}</dd></div>
                <div><dt class="text-slate-500">Nomor Internal</dt><dd class="font-mono text-xs">{{ $record->internal_number }}</dd></div>
                <div><dt class="text-slate-500">Nomor Resmi</dt><dd class="font-mono text-xs">{{ $record->letter_number ?? '—' }}</dd></div>
                @if($record->proposal_judul_snapshot)<div class="sm:col-span-2"><dt class="text-slate-500">Proposal Terkait</dt><dd>{{ $record->proposal_number_snapshot }} — {{ $record->proposal_judul_snapshot }}</dd></div>@endif
                @if($record->ketua_dosen_nama_snapshot)<div><dt class="text-slate-500">Ketua</dt><dd>{{ $record->ketua_dosen_nama_snapshot }}</dd></div>@endif
                @if($record->mitra_nama_snapshot)<div><dt class="text-slate-500">Mitra</dt><dd>{{ $record->mitra_nama_snapshot }}</dd></div>@endif
                @if($record->reviewer_nama_snapshot)<div><dt class="text-slate-500">Reviewer</dt><dd>{{ $record->reviewer_nama_snapshot }}</dd></div>@endif
            </dl>
        </div></div>

        @if($canApprove)
        <div class="sipeng-card border-indigo-200"><div class="sipeng-card-body">
            <h3 class="font-semibold mb-3">Persetujuan Ketua LPPM</h3>
            <form method="POST" action="{{ route('admin.letters.approval.store', $record) }}" class="space-y-3">@csrf
                <select name="decision" class="sipeng-input" required><option value="approved">Setujui</option><option value="revision_required">Minta Revisi</option><option value="rejected">Tolak</option></select>
                <textarea name="notes" rows="2" class="sipeng-input" placeholder="Catatan"></textarea>
                <button class="sipeng-btn-primary text-sm">Simpan Keputusan</button>
            </form>
        </div></div>@endif

        @if($canIssue)
        <div class="sipeng-card border-emerald-200"><div class="sipeng-card-body">
            <h3 class="font-semibold mb-2">Terbitkan Surat</h3>
            <p class="text-sm text-slate-600 mb-3">Surat akan diberi nomor resmi otomatis dan PDF di-generate.</p>
            <form method="POST" action="{{ route('admin.letters.issue', $record) }}" onsubmit="return confirm('Terbitkan surat? Nomor resmi akan dibuat.')">@csrf<button class="sipeng-btn-primary text-sm">Terbitkan Surat</button></form>
        </div></div>@endif

        @if($canUploadSigned)
        <div class="sipeng-card"><div class="sipeng-card-body">
            <h3 class="font-semibold mb-3">Upload Scan Surat Bertanda Tangan</h3>
            <form method="POST" action="{{ route('admin.letters.upload.signed', $record) }}" enctype="multipart/form-data" class="space-y-3">@csrf
                <input type="file" name="file_signed_scan" accept=".pdf,.jpg,.jpeg,.png" class="sipeng-input" required>
                <button class="sipeng-btn-primary text-sm">Upload Scan Final</button>
            </form>
        </div></div>@endif

        <div class="sipeng-card"><div class="sipeng-card-body">
            <h3 class="font-semibold mb-2">Log Aktivitas</h3>
            <ul class="text-sm divide-y">@forelse($logs as $log)<li class="py-2">{{ $log->event }} — {{ $log->description }} <time class="block text-xs text-slate-500">{{ $log->created_at?->format('d M Y H:i') }}</time></li>@empty<li class="text-slate-500">Belum ada log.</li>@endforelse</ul>
        </div></div>
    </div>
</x-app-layout>
