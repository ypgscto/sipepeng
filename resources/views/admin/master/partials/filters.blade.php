<form method="GET" action="{{ route('admin.master.'.$entityKey.'.index') }}" class="sipeng-card">
    <div class="sipeng-card-body">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="lg:col-span-2">
                <label for="q" class="sipeng-label">Cari</label>
                <input type="text" id="q" name="q" value="{{ $filters['q'] }}"
                    placeholder="Kode, nama, atau kata kunci..."
                    class="sipeng-input" />
            </div>
            <div>
                <label for="status" class="sipeng-label">Status</label>
                <select id="status" name="status" class="sipeng-input">
                    <option value="all" @selected($filters['status'] === 'all')>Semua</option>
                    <option value="active" @selected($filters['status'] === 'active')>Aktif</option>
                    <option value="inactive" @selected($filters['status'] === 'inactive')>Nonaktif</option>
                </select>
            </div>
            <div>
                <label for="trashed" class="sipeng-label">Data Terhapus</label>
                <select id="trashed" name="trashed" class="sipeng-input">
                    <option value="" @selected($filters['trashed'] === '')>Sembunyikan</option>
                    <option value="with" @selected($filters['trashed'] === 'with')>Sertakan</option>
                    <option value="only" @selected($filters['trashed'] === 'only')>Hanya terhapus</option>
                </select>
            </div>
        </div>
        <div class="mt-4 flex gap-2">
            <button type="submit" class="sipeng-btn-primary text-sm">Terapkan Filter</button>
            <a href="{{ route('admin.master.'.$entityKey.'.index') }}" class="sipeng-btn-secondary text-sm">Reset</a>
        </div>
    </div>
</form>
