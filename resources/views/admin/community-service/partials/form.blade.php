@php
    $proposal = $proposal ?? null;
    $budgetItems = old('budget_items', $proposal?->budgetItems?->map(fn ($i) => $i->only(['item_name','category','quantity','unit','unit_price']))->all() ?? [['item_name'=>'','category'=>'other','quantity'=>1,'unit'=>'','unit_price'=>0]]);
    if (count($budgetItems) < 3) {
        $budgetItems = array_pad($budgetItems, 3, ['item_name'=>'','category'=>'other','quantity'=>1,'unit'=>'','unit_price'=>0]);
    }
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="sipeng-label">Tahun Akademik <span class="text-red-600">*</span></label>
        <select name="tahun_akademik_id" id="tahun_akademik_id" class="sipeng-input" required onchange="syncTahunAkademikLabel(this)">
            <option value="">— Pilih —</option>
            @foreach ($tahunAkademikOptions as $opt)
                <option value="{{ $opt['value'] }}" data-label="{{ $opt['label'] }}"
                    @selected(old('tahun_akademik_id', $proposal?->tahun_akademik_id) === $opt['value'])>{{ $opt['label'] }}</option>
            @endforeach
        </select>
        <input type="hidden" name="tahun_akademik_nama_snapshot" id="tahun_akademik_nama_snapshot"
            value="{{ old('tahun_akademik_nama_snapshot', $proposal?->tahun_akademik_nama_snapshot) }}">
        <x-input-error :messages="$errors->get('tahun_akademik_id')" class="mt-1" />
    </div>
    <div>
        <label class="sipeng-label">Semester <span class="text-red-600">*</span></label>
        <select name="semester_id" id="semester_id" class="sipeng-input" required onchange="syncSemesterLabel(this)">
            <option value="">— Pilih —</option>
            @foreach ($semesterOptions as $opt)
                <option value="{{ $opt['value'] }}" data-label="{{ $opt['label'] }}"
                    @selected(old('semester_id', $proposal?->semester_id) === $opt['value'])>{{ $opt['label'] }}</option>
            @endforeach
        </select>
        <input type="hidden" name="semester_nama_snapshot" id="semester_nama_snapshot"
            value="{{ old('semester_nama_snapshot', $proposal?->semester_nama_snapshot) }}">
    </div>
    <div>
        <label class="sipeng-label">Program Studi <span class="text-red-600">*</span></label>
        <select name="prodi_id" id="prodi_id" class="sipeng-input" required onchange="syncProdiLabel(this)">
            <option value="">— Pilih —</option>
            @foreach ($prodiOptions as $opt)
                <option value="{{ $opt['value'] }}" data-label="{{ $opt['label'] }}"
                    @selected(old('prodi_id', $proposal?->prodi_id) === $opt['value'])>{{ $opt['label'] }}</option>
            @endforeach
        </select>
        <input type="hidden" name="prodi_nama_snapshot" id="prodi_nama_snapshot"
            value="{{ old('prodi_nama_snapshot', $proposal?->prodi_nama_snapshot) }}">
    </div>
    <div>
        <label class="sipeng-label">Skema PkM <span class="text-red-600">*</span></label>
        <select name="skema_id" class="sipeng-input" required>
            <option value="">— Pilih —</option>
            @foreach ($schemes as $scheme)
                <option value="{{ $scheme->id }}" @selected((string) old('skema_id', $proposal?->skema_id) === (string) $scheme->id)>{{ $scheme->name }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="mt-4">
    <label class="sipeng-label">Judul Pengabdian <span class="text-red-600">*</span></label>
    <input type="text" name="judul" value="{{ old('judul', $proposal?->judul) }}" class="sipeng-input" required>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
    <div>
        <label class="sipeng-label">Ketua Pelaksana (SIAKAD ID)</label>
        <input type="text" name="ketua_dosen_id" value="{{ old('ketua_dosen_id', $proposal?->ketua_dosen_id ?? $defaultKetua['id']) }}" class="sipeng-input bg-slate-50" readonly>
    </div>
    <div>
        <label class="sipeng-label">Nama Ketua (snapshot)</label>
        <input type="text" name="ketua_dosen_nama_snapshot" value="{{ old('ketua_dosen_nama_snapshot', $proposal?->ketua_dosen_nama_snapshot ?? $defaultKetua['nama']) }}" class="sipeng-input bg-slate-50" readonly>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
    <div>
        <label class="sipeng-label">Mitra <span class="text-red-600">*</span></label>
        <select name="mitra_id" id="mitra_id" class="sipeng-input" required onchange="syncMitra(this)">
            <option value="">— Pilih Mitra —</option>
            @foreach ($partners as $partner)
                <option value="{{ $partner->id }}"
                    data-name="{{ $partner->name }}"
                    data-type-id="{{ $partner->partner_type_id }}"
                    data-type-name="{{ $partner->partnerType?->name }}"
                    @selected((string) old('mitra_id', $proposal?->mitra_id) === (string) $partner->id)>
                    {{ $partner->name }} ({{ $partner->partnerType?->name }})
                </option>
            @endforeach
        </select>
        <input type="hidden" name="mitra_nama_snapshot" id="mitra_nama_snapshot"
            value="{{ old('mitra_nama_snapshot', $proposal?->mitra_nama_snapshot) }}">
        <x-input-error :messages="$errors->get('mitra_id')" class="mt-1" />
    </div>
    <div>
        <label class="sipeng-label">Jenis Mitra (snapshot)</label>
        <input type="hidden" name="jenis_mitra_id" id="jenis_mitra_id" value="{{ old('jenis_mitra_id', $proposal?->jenis_mitra_id) }}">
        <input type="text" name="jenis_mitra_nama_snapshot" id="jenis_mitra_nama_snapshot"
            value="{{ old('jenis_mitra_nama_snapshot', $proposal?->jenis_mitra_nama_snapshot) }}"
            class="sipeng-input bg-slate-50" readonly>
    </div>
</div>

<div class="mt-4 space-y-4">
    @foreach (['masalah_mitra' => 'Masalah Mitra', 'solusi_ditawarkan' => 'Solusi Ditawarkan', 'target_capaian' => 'Target Capaian', 'metode_pelaksanaan' => 'Metode Pelaksanaan', 'target_luaran' => 'Target Luaran'] as $field => $label)
        <div>
            <label class="sipeng-label">{{ $label }}</label>
            <textarea name="{{ $field }}" rows="3" class="sipeng-input">{{ old($field, $proposal?->{$field}) }}</textarea>
        </div>
    @endforeach
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
    <div>
        <label class="sipeng-label">Lokasi Kegiatan</label>
        <input type="text" name="lokasi_kegiatan" value="{{ old('lokasi_kegiatan', $proposal?->lokasi_kegiatan) }}" class="sipeng-input">
    </div>
    <div>
        <label class="sipeng-label">Jadwal Mulai</label>
        <input type="date" name="jadwal_mulai" value="{{ old('jadwal_mulai', $proposal?->jadwal_mulai?->format('Y-m-d')) }}" class="sipeng-input">
    </div>
    <div>
        <label class="sipeng-label">Jadwal Selesai</label>
        <input type="date" name="jadwal_selesai" value="{{ old('jadwal_selesai', $proposal?->jadwal_selesai?->format('Y-m-d')) }}" class="sipeng-input">
    </div>
</div>

<div class="mt-6">
    <h3 class="font-semibold text-slate-900 mb-3">RAB PkM</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-2 py-2 text-left">Komponen</th>
                    <th class="px-2 py-2 text-left">Kategori</th>
                    <th class="px-2 py-2 text-left">Qty</th>
                    <th class="px-2 py-2 text-left">Satuan</th>
                    <th class="px-2 py-2 text-left">Harga</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($budgetItems as $idx => $item)
                    <tr>
                        <td class="px-2 py-1"><input type="text" name="budget_items[{{ $idx }}][item_name]" value="{{ $item['item_name'] ?? '' }}" class="sipeng-input"></td>
                        <td class="px-2 py-1">
                            <select name="budget_items[{{ $idx }}][category]" class="sipeng-input">
                                @foreach (['honorarium','material','travel','publication','other'] as $cat)
                                    <option value="{{ $cat }}" @selected(($item['category'] ?? 'other') === $cat)>{{ ucfirst($cat) }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-2 py-1"><input type="number" step="0.01" min="0" name="budget_items[{{ $idx }}][quantity]" value="{{ $item['quantity'] ?? 1 }}" class="sipeng-input"></td>
                        <td class="px-2 py-1"><input type="text" name="budget_items[{{ $idx }}][unit]" value="{{ $item['unit'] ?? '' }}" class="sipeng-input"></td>
                        <td class="px-2 py-1"><input type="number" step="0.01" min="0" name="budget_items[{{ $idx }}][unit_price]" value="{{ $item['unit_price'] ?? 0 }}" class="sipeng-input"></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
    @foreach (['file_proposal' => 'File Proposal (PDF)', 'file_surat_mitra' => 'Surat Mitra (PDF)', 'file_pengesahan' => 'File Pengesahan (PDF)'] as $field => $label)
        <div>
            <label class="sipeng-label">{{ $label }} @if (! $proposal)<span class="text-red-600">*</span>@endif</label>
            <input type="file" name="{{ $field }}" accept=".pdf" class="sipeng-input" @if (! $proposal) required @endif>
            @if ($proposal?->{$field})
                <p class="mt-1 text-xs text-slate-500">Berkas saat ini: {{ $proposal->{$field.'_name'} }}</p>
            @endif
            <x-input-error :messages="$errors->get($field)" class="mt-1" />
        </div>
    @endforeach
</div>

@push('scripts')
<script>
function syncLabel(selectEl, hiddenId) {
    const opt = selectEl.options[selectEl.selectedIndex];
    document.getElementById(hiddenId).value = opt?.dataset?.label || opt?.text || '';
}
function syncTahunAkademikLabel(el) { syncLabel(el, 'tahun_akademik_nama_snapshot'); }
function syncSemesterLabel(el) { syncLabel(el, 'semester_nama_snapshot'); }
function syncProdiLabel(el) { syncLabel(el, 'prodi_nama_snapshot'); }
function syncMitra(el) {
    const opt = el.options[el.selectedIndex];
    document.getElementById('mitra_nama_snapshot').value = opt?.dataset?.name || '';
    document.getElementById('jenis_mitra_id').value = opt?.dataset?.typeId || '';
    document.getElementById('jenis_mitra_nama_snapshot').value = opt?.dataset?.typeName || '';
}
document.addEventListener('DOMContentLoaded', () => {
    ['tahun_akademik_id','semester_id','prodi_id','mitra_id'].forEach(id => {
        const el = document.getElementById(id);
        if (el?.value) el.dispatchEvent(new Event('change'));
    });
});
</script>
@endpush
