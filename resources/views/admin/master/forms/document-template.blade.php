@php $record = $record ?? null; @endphp

<div>
    <label for="template_code" class="sipeng-label">Kode Template <span class="text-red-600">*</span></label>
    @if ($record)
        <input type="hidden" name="template_code" value="{{ $record->template_code }}">
        <input type="text" id="template_code" value="{{ $record->template_code }}" class="sipeng-input bg-slate-50" disabled>
    @else
        <input type="text" id="template_code" name="template_code" value="{{ old('template_code') }}"
            class="sipeng-input" pattern="[a-z0-9_]+" required>
    @endif
    <x-input-error :messages="$errors->get('template_code')" class="mt-1" />
</div>

<div>
    <label for="name" class="sipeng-label">Nama <span class="text-red-600">*</span></label>
    <input type="text" id="name" name="name" value="{{ old('name', $record?->name) }}" class="sipeng-input" required>
    <x-input-error :messages="$errors->get('name')" class="mt-1" />
</div>

<div>
    <label for="description" class="sipeng-label">Deskripsi</label>
    <textarea id="description" name="description" rows="3" class="sipeng-input">{{ old('description', $record?->description) }}</textarea>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label for="sort_order" class="sipeng-label">Urutan</label>
        <input type="number" id="sort_order" name="sort_order" min="0" max="9999"
            value="{{ old('sort_order', $record?->sort_order ?? 0) }}" class="sipeng-input">
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

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label for="document_category_id" class="sipeng-label">Kategori Dokumen</label>
        <select id="document_category_id" name="document_category_id" class="sipeng-input">
            <option value="">— Pilih —</option>
            @foreach ($documentCategories as $category)
                <option value="{{ $category->id }}" @selected((string) old('document_category_id', $record?->document_category_id) === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        @if ($documentCategories->isEmpty())
            <p class="mt-1 text-xs text-amber-700">
                Belum ada kategori dokumen.
                <a href="{{ route('admin.master.document-categories.create') }}" class="font-semibold underline hover:text-amber-900">Tambah kategori</a>
            </p>
        @endif
    </div>
    <div>
        <label for="module_type" class="sipeng-label">Modul <span class="text-red-600">*</span></label>
        <select id="module_type" name="module_type" class="sipeng-input" required>
            @foreach (['research' => 'Penelitian', 'community_service' => 'Pengabdian', 'general' => 'Umum', 'letter' => 'Surat'] as $value => $label)
                <option value="{{ $value }}" @selected(old('module_type', $record?->module_type ?? 'general') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="version" class="sipeng-label">Versi</label>
        <input type="text" id="version" name="version" value="{{ old('version', $record?->version ?? '1.0') }}" class="sipeng-input">
    </div>
    <div>
        <label for="file" class="sipeng-label">Berkas Template @if (! ($record ?? null))<span class="text-red-600">*</span>@endif</label>
        <input type="file" id="file" name="file" accept=".pdf,.doc,.docx" class="sipeng-input" @if (! ($record ?? null)) required @endif>
        @if ($record?->file_name)
            <p class="mt-1 text-xs text-slate-500">Berkas saat ini: {{ $record->file_name }}</p>
        @endif
        <x-input-error :messages="$errors->get('file')" class="mt-1" />
    </div>
</div>
<label class="inline-flex items-center gap-2 text-sm text-slate-700">
    <input type="hidden" name="is_default" value="0">
    <input type="checkbox" name="is_default" value="1" @checked(old('is_default', $record?->is_default ?? false)) class="rounded border-slate-300 text-emerald-600">
    Template default
</label>
