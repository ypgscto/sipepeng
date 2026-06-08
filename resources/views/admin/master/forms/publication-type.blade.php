@include('admin.master.forms._base-fields')

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label for="indexing_type" class="sipeng-label">Jenis Indeksasi</label>
        <select id="indexing_type" name="indexing_type" class="sipeng-input">
            <option value="">— Pilih —</option>
            @foreach (['national' => 'Nasional', 'international' => 'Internasional', 'other' => 'Lainnya'] as $value => $label)
                <option value="{{ $value }}" @selected(old('indexing_type', $record?->indexing_type) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="feeder_code" class="sipeng-label">Kode Feeder</label>
        <input type="text" id="feeder_code" name="feeder_code" value="{{ old('feeder_code', $record?->feeder_code) }}" class="sipeng-input">
    </div>
</div>
<label class="inline-flex items-center gap-2 text-sm text-slate-700">
    <input type="hidden" name="requires_issn_isbn" value="0">
    <input type="checkbox" name="requires_issn_isbn" value="1" @checked(old('requires_issn_isbn', $record?->requires_issn_isbn ?? false)) class="rounded border-slate-300 text-emerald-600">
    Memerlukan ISSN/ISBN
</label>
