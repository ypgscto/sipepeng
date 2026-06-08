@include('admin.master.forms._base-fields')

<div>
    <label for="applies_to" class="sipeng-label">Berlaku Untuk <span class="text-red-600">*</span></label>
    <select id="applies_to" name="applies_to" class="sipeng-input" required>
        @foreach (['research' => 'Penelitian', 'community_service' => 'Pengabdian', 'both' => 'Keduanya'] as $value => $label)
            <option value="{{ $value }}" @selected(old('applies_to', $record?->applies_to ?? 'both') === $value)>{{ $label }}</option>
        @endforeach
    </select>
</div>
<div>
    <label for="unit_label" class="sipeng-label">Label Satuan</label>
    <input type="text" id="unit_label" name="unit_label" value="{{ old('unit_label', $record?->unit_label) }}" class="sipeng-input" placeholder="artikel, buku, dll.">
</div>
<label class="inline-flex items-center gap-2 text-sm text-slate-700">
    <input type="hidden" name="is_measurable" value="0">
    <input type="checkbox" name="is_measurable" value="1" @checked(old('is_measurable', $record?->is_measurable ?? true)) class="rounded border-slate-300 text-emerald-600">
    Dapat diukur
</label>
