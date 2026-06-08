@include('admin.master.forms._base-fields')

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label for="academic_year_label" class="sipeng-label">Tahun Akademik</label>
        <input type="text" id="academic_year_label" name="academic_year_label" value="{{ old('academic_year_label', $record?->academic_year_label) }}" class="sipeng-input" placeholder="2025/2026">
    </div>
    <div>
        <label for="max_budget" class="sipeng-label">Anggaran Maksimum</label>
        <input type="number" id="max_budget" name="max_budget" min="0" step="0.01" value="{{ old('max_budget', $record?->max_budget) }}" class="sipeng-input">
    </div>
    <div>
        <label for="min_team_members" class="sipeng-label">Min. Anggota Tim</label>
        <input type="number" id="min_team_members" name="min_team_members" min="1" max="50" value="{{ old('min_team_members', $record?->min_team_members) }}" class="sipeng-input">
    </div>
    <div>
        <label for="max_team_members" class="sipeng-label">Maks. Anggota Tim</label>
        <input type="number" id="max_team_members" name="max_team_members" min="1" max="50" value="{{ old('max_team_members', $record?->max_team_members) }}" class="sipeng-input">
    </div>
    <div>
        <label for="submission_deadline" class="sipeng-label">Batas Pengajuan</label>
        <input type="date" id="submission_deadline" name="submission_deadline" value="{{ old('submission_deadline', $record?->submission_deadline?->format('Y-m-d')) }}" class="sipeng-input">
    </div>
    <div>
        <label for="guideline_url" class="sipeng-label">URL Panduan</label>
        <input type="url" id="guideline_url" name="guideline_url" value="{{ old('guideline_url', $record?->guideline_url) }}" class="sipeng-input">
    </div>
</div>
<label class="inline-flex items-center gap-2 text-sm text-slate-700">
    <input type="hidden" name="requires_ethics_approval" value="0">
    <input type="checkbox" name="requires_ethics_approval" value="1" @checked(old('requires_ethics_approval', $record?->requires_ethics_approval ?? false)) class="rounded border-slate-300 text-emerald-600">
    Memerlukan persetujuan etik
</label>
<div>
    <label for="funding_source_ids" class="sipeng-label">Sumber Dana</label>
    <select id="funding_source_ids" name="funding_source_ids[]" class="sipeng-input" multiple size="4">
        @php $selected = collect(old('funding_source_ids', $record?->fundingSources?->pluck('id')->all() ?? []))->map(fn ($id) => (string) $id); @endphp
        @foreach ($fundingSources as $source)
            <option value="{{ $source->id }}" @selected($selected->contains((string) $source->id))>{{ $source->name }}</option>
        @endforeach
    </select>
    <p class="mt-1 text-xs text-slate-500">Tahan Ctrl untuk memilih lebih dari satu.</p>
</div>
