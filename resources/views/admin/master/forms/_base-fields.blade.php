@php
    $record = $record ?? null;
    $isEdit = $record !== null;
    $codeField = $codeField ?? 'code';
    $showCode = $showCode ?? true;
    $lockCode = $lockCode ?? false;
@endphp

@if ($showCode)
    <div>
        <label for="{{ $codeField }}" class="sipeng-label">Kode <span class="text-red-600">*</span></label>
        @if ($lockCode)
            <input type="text" id="{{ $codeField }}" value="{{ $record->{$codeField} }}" class="sipeng-input bg-slate-50" disabled>
        @else
            <input type="text" id="{{ $codeField }}" name="{{ $codeField }}"
                value="{{ old($codeField, $record?->{$codeField}) }}"
                class="sipeng-input" pattern="[a-z0-9_]+" placeholder="contoh: internal_gs" required>
        @endif
        <x-input-error :messages="$errors->get($codeField)" class="mt-1" />
    </div>
@endif

<div>
    <label for="name" class="sipeng-label">Nama <span class="text-red-600">*</span></label>
    <input type="text" id="name" name="name" value="{{ old('name', $record?->name) }}" class="sipeng-input" required>
    <x-input-error :messages="$errors->get('name')" class="mt-1" />
</div>

<div>
    <label for="description" class="sipeng-label">Deskripsi</label>
    <textarea id="description" name="description" rows="3" class="sipeng-input">{{ old('description', $record?->description) }}</textarea>
    <x-input-error :messages="$errors->get('description')" class="mt-1" />
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label for="sort_order" class="sipeng-label">Urutan</label>
        <input type="number" id="sort_order" name="sort_order" min="0" max="9999"
            value="{{ old('sort_order', $record?->sort_order ?? 0) }}" class="sipeng-input">
        <x-input-error :messages="$errors->get('sort_order')" class="mt-1" />
    </div>
    <div class="flex items-end pb-2">
        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1"
                @checked(old('is_active', $record?->is_active ?? true)) class="rounded border-slate-300 text-emerald-600">
            Aktif
        </label>
    </div>
</div>
