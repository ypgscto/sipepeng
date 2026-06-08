@include('admin.master.forms._base-fields')

<div>
    <label for="icon" class="sipeng-label">Ikon (opsional)</label>
    <input type="text" id="icon" name="icon" value="{{ old('icon', $record?->icon) }}" class="sipeng-input" placeholder="building">
</div>
<label class="inline-flex items-center gap-2 text-sm text-slate-700">
    <input type="hidden" name="requires_legal_document" value="0">
    <input type="checkbox" name="requires_legal_document" value="1" @checked(old('requires_legal_document', $record?->requires_legal_document ?? false)) class="rounded border-slate-300 text-emerald-600">
    Memerlukan dokumen legal
</label>
