@include('admin.master.forms._base-fields')

<div>
    <label for="module_type" class="sipeng-label">Modul <span class="text-red-600">*</span></label>
    <select id="module_type" name="module_type" class="sipeng-input" required>
        @foreach (['research' => 'Penelitian', 'community_service' => 'Pengabdian', 'general' => 'Umum'] as $value => $label)
            <option value="{{ $value }}" @selected(old('module_type', $record?->module_type ?? 'general') === $value)>{{ $label }}</option>
        @endforeach
    </select>
</div>
<label class="inline-flex items-center gap-2 text-sm text-slate-700">
    <input type="hidden" name="is_required" value="0">
    <input type="checkbox" name="is_required" value="1" @checked(old('is_required', $record?->is_required ?? false)) class="rounded border-slate-300 text-emerald-600">
    Wajib diunggah
</label>
