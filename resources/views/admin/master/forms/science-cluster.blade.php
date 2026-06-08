@include('admin.master.forms._base-fields')

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label for="feeder_code" class="sipeng-label">Kode Feeder</label>
        <input type="text" id="feeder_code" name="feeder_code" value="{{ old('feeder_code', $record?->feeder_code) }}" class="sipeng-input">
    </div>
    <div>
        <label for="level" class="sipeng-label">Level</label>
        <input type="number" id="level" name="level" min="1" max="5" value="{{ old('level', $record?->level ?? 1) }}" class="sipeng-input">
    </div>
    <div class="sm:col-span-2">
        <label for="parent_id" class="sipeng-label">Induk Rumpun</label>
        <select id="parent_id" name="parent_id" class="sipeng-input">
            <option value="">— Tidak ada —</option>
            @foreach ($scienceClusters as $cluster)
                @if (($record?->id ?? null) !== $cluster->id)
                    <option value="{{ $cluster->id }}" @selected((string) old('parent_id', $record?->parent_id) === (string) $cluster->id)>{{ $cluster->name }}</option>
                @endif
            @endforeach
        </select>
    </div>
</div>
