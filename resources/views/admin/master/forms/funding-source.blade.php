@include('admin.master.forms._base-fields')

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label for="source_category" class="sipeng-label">Kategori Sumber <span class="text-red-600">*</span></label>
        <select id="source_category" name="source_category" class="sipeng-input" required>
            @foreach (['internal' => 'Internal', 'external' => 'Eksternal', 'mixed' => 'Campuran'] as $value => $label)
                <option value="{{ $value }}" @selected(old('source_category', $record?->source_category ?? 'internal') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="institution_name" class="sipeng-label">Nama Institusi</label>
        <input type="text" id="institution_name" name="institution_name" value="{{ old('institution_name', $record?->institution_name) }}" class="sipeng-input">
    </div>
</div>
<label class="inline-flex items-center gap-2 text-sm text-slate-700">
    <input type="hidden" name="requires_contract" value="0">
    <input type="checkbox" name="requires_contract" value="1" @checked(old('requires_contract', $record?->requires_contract ?? false)) class="rounded border-slate-300 text-emerald-600">
    Memerlukan kontrak
</label>
