<form method="GET" action="{{ route('admin.siakad-reference.index') }}" class="sipeng-card">
    <div class="sipeng-card-body">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="lg:col-span-2">
                <label for="q" class="sipeng-label">Cari</label>
                <input type="text" id="q" name="q" value="{{ $filters['q'] ?? '' }}"
                    placeholder="Nama, kode, NIM, email..."
                    class="sipeng-input" />
            </div>

            @if (in_array($tab, ['dosen', 'mahasiswa'], true))
                <div>
                    <label for="prodi_id" class="sipeng-label">Program Studi</label>
                    <select id="prodi_id" name="prodi_id" class="sipeng-input">
                        <option value="">Semua prodi</option>
                        @foreach ($prodi_options as $option)
                            <option value="{{ $option['value'] }}" @selected(($filters['prodi_id'] ?? '') === $option['value'])>
                                {{ $option['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if ($tab === 'mahasiswa')
                <div>
                    <label for="angkatan" class="sipeng-label">Angkatan</label>
                    <input type="text" id="angkatan" name="angkatan" value="{{ $filters['angkatan'] ?? '' }}"
                        placeholder="2024" maxlength="4" class="sipeng-input" />
                </div>
            @endif

            @if (in_array($tab, ['tahun_akademik', 'semester'], true))
                <div>
                    <label for="status" class="sipeng-label">Status</label>
                    <select id="status" name="status" class="sipeng-input">
                        <option value="">Semua</option>
                        <option value="aktif" @selected(($filters['status'] ?? '') === 'aktif')>Aktif saja</option>
                    </select>
                </div>
            @endif

            @if ($tab === 'semester')
                <div>
                    <label for="semester" class="sipeng-label">Jenis Semester</label>
                    <select id="semester" name="semester" class="sipeng-input">
                        <option value="">Semua</option>
                        <option value="ganjil" @selected(($filters['semester'] ?? '') === 'ganjil')>Ganjil</option>
                        <option value="genap" @selected(($filters['semester'] ?? '') === 'genap')>Genap</option>
                    </select>
                </div>
            @endif
        </div>
        <div class="mt-4 flex gap-2">
            <button type="submit" class="sipeng-btn-primary text-sm">Terapkan Filter</button>
            <a href="{{ route('admin.siakad-reference.index', ['tab' => $tab]) }}" class="sipeng-btn-secondary text-sm">Reset</a>
        </div>
    </div>
</form>
