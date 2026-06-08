@php $record = $record ?? null; @endphp

<div>
    <label for="user_id" class="sipeng-label">Pengguna <span class="text-red-600">*</span></label>
    <select id="user_id" name="user_id" class="sipeng-input" required>
        <option value="">— Pilih pengguna —</option>
        @foreach ($users as $user)
            <option value="{{ $user->id }}" @selected((string) old('user_id', $record?->user_id) === (string) $user->id)>
                {{ $user->name }} ({{ $user->email }})
            </option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('user_id')" class="mt-1" />
</div>

<div>
    <label for="expertise_notes" class="sipeng-label">Catatan Keahlian</label>
    <textarea id="expertise_notes" name="expertise_notes" rows="3" class="sipeng-input">{{ old('expertise_notes', $record?->expertise_notes) }}</textarea>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label for="science_cluster_id" class="sipeng-label">Rumpun Ilmu</label>
        <select id="science_cluster_id" name="science_cluster_id" class="sipeng-input">
            <option value="">— Pilih —</option>
            @foreach ($scienceClusters as $cluster)
                <option value="{{ $cluster->id }}" @selected((string) old('science_cluster_id', $record?->science_cluster_id) === (string) $cluster->id)>{{ $cluster->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="focus_area_id" class="sipeng-label">Bidang Fokus</label>
        <select id="focus_area_id" name="focus_area_id" class="sipeng-input">
            <option value="">— Pilih —</option>
            @foreach ($focusAreas as $area)
                <option value="{{ $area->id }}" @selected((string) old('focus_area_id', $record?->focus_area_id) === (string) $area->id)>{{ $area->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="max_active_reviews" class="sipeng-label">Maks. Review Aktif</label>
        <input type="number" id="max_active_reviews" name="max_active_reviews" min="1" max="100"
            value="{{ old('max_active_reviews', $record?->max_active_reviews) }}" class="sipeng-input">
    </div>
    <div>
        <label for="appointed_at" class="sipeng-label">Tanggal Penetapan</label>
        <input type="date" id="appointed_at" name="appointed_at" value="{{ old('appointed_at', $record?->appointed_at?->format('Y-m-d') ?? now()->toDateString()) }}" class="sipeng-input">
    </div>
</div>

<div class="flex items-end pb-2">
    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1"
            @checked(old('is_active', $record?->is_active ?? true)) class="rounded border-slate-300 text-emerald-600">
        Aktif
    </label>
</div>
