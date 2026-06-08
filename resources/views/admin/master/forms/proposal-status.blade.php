@include('admin.master.forms._base-fields', ['lockCode' => ($record ?? null) !== null])

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label for="proposal_type" class="sipeng-label">Jenis Proposal <span class="text-red-600">*</span></label>
        <select id="proposal_type" name="proposal_type" class="sipeng-input" required>
            @foreach (['research' => 'Penelitian', 'community_service' => 'Pengabdian', 'both' => 'Keduanya'] as $value => $label)
                <option value="{{ $value }}" @selected(old('proposal_type', $record?->proposal_type ?? 'both') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="stage" class="sipeng-label">Tahap <span class="text-red-600">*</span></label>
        <input type="text" id="stage" name="stage" value="{{ old('stage', $record?->stage) }}" class="sipeng-input" required placeholder="submission">
    </div>
    <div>
        <label for="color" class="sipeng-label">Warna Label</label>
        <input type="color" id="color" name="color" value="{{ old('color', $record?->color ?? '#0f766e') }}" class="sipeng-input h-10">
    </div>
</div>
<div class="flex flex-wrap gap-4">
    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
        <input type="hidden" name="is_terminal" value="0">
        <input type="checkbox" name="is_terminal" value="1" @checked(old('is_terminal', $record?->is_terminal ?? false)) class="rounded border-slate-300 text-emerald-600">
        Status terminal
    </label>
    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
        <input type="hidden" name="is_editable_by_proposer" value="0">
        <input type="checkbox" name="is_editable_by_proposer" value="1" @checked(old('is_editable_by_proposer', $record?->is_editable_by_proposer ?? false)) class="rounded border-slate-300 text-emerald-600">
        Dapat diedit pengusul
    </label>
</div>
