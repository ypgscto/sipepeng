@include('admin.master.forms._base-fields')

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label for="parent_id" class="sipeng-label">Induk Bidang</label>
        <select id="parent_id" name="parent_id" class="sipeng-input">
            <option value="">— Tidak ada —</option>
            @foreach ($focusAreas as $area)
                @if (($record?->id ?? null) !== $area->id)
                    <option value="{{ $area->id }}" @selected((string) old('parent_id', $record?->parent_id) === (string) $area->id)>{{ $area->name }}</option>
                @endif
            @endforeach
        </select>
    </div>
    <div>
        <label for="color" class="sipeng-label">Warna</label>
        <input type="color" id="color" name="color" value="{{ old('color', $record?->color ?? '#0f766e') }}" class="sipeng-input h-10">
    </div>
    <div>
        <label for="year_start" class="sipeng-label">Tahun Mulai</label>
        <input type="number" id="year_start" name="year_start" min="2000" max="2100" value="{{ old('year_start', $record?->year_start) }}" class="sipeng-input">
    </div>
    <div>
        <label for="year_end" class="sipeng-label">Tahun Selesai</label>
        <input type="number" id="year_end" name="year_end" min="2000" max="2100" value="{{ old('year_end', $record?->year_end) }}" class="sipeng-input">
    </div>
</div>
