@include('admin.master.forms._base-fields')

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label for="letter_prefix" class="sipeng-label">Prefix Nomor Surat</label>
        <input type="text" id="letter_prefix" name="letter_prefix" value="{{ old('letter_prefix', $record?->letter_prefix) }}" class="sipeng-input" placeholder="LPPM">
    </div>
    <div>
        <label for="document_template_id" class="sipeng-label">Template Dokumen</label>
        <select id="document_template_id" name="document_template_id" class="sipeng-input">
            <option value="">— Pilih —</option>
            @foreach ($documentTemplates as $template)
                <option value="{{ $template->id }}" @selected((string) old('document_template_id', $record?->document_template_id) === (string) $template->id)>{{ $template->name }}</option>
            @endforeach
        </select>
    </div>
</div>
<label class="inline-flex items-center gap-2 text-sm text-slate-700">
    <input type="hidden" name="requires_approval" value="0">
    <input type="checkbox" name="requires_approval" value="1" @checked(old('requires_approval', $record?->requires_approval ?? true)) class="rounded border-slate-300 text-emerald-600">
    Memerlukan persetujuan
</label>
