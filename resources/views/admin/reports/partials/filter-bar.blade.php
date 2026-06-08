<form method="GET" class="sipeng-card mb-4">
    <div class="sipeng-card-body grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-3">
        <div>
            <label class="sipeng-label">Tahun Kalender</label>
            <select name="calendar_year" class="sipeng-input">
                @foreach($filterOptions['calendarYears'] ?? [] as $y)
                    <option value="{{ $y }}" @selected(($filters['calendar_year'] ?? $filter->calendarYear ?? now()->year) == $y)>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="sipeng-label">Tahun Akademik</label>
            <select name="tahun_akademik_id" class="sipeng-input">
                <option value="">Semua</option>
                @foreach($filterOptions['tahunAkademikOptions'] ?? [] as $o)
                    <option value="{{ $o['value'] }}" @selected(($filters['tahun_akademik_id'] ?? '') === $o['value'])>{{ $o['label'] }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="sipeng-label">Semester</label>
            <select name="semester_id" class="sipeng-input">
                <option value="">Semua</option>
                @foreach($filterOptions['semesterOptions'] ?? [] as $o)
                    <option value="{{ $o['value'] }}" @selected(($filters['semester_id'] ?? '') === $o['value'])>{{ $o['label'] }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="sipeng-label">Prodi</label>
            <select name="prodi_id" class="sipeng-input">
                <option value="">Semua</option>
                @foreach($filterOptions['prodiOptions'] ?? [] as $o)
                    <option value="{{ $o['value'] }}" @selected(($filters['prodi_id'] ?? '') === $o['value'])>{{ $o['label'] }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="sipeng-label">Dosen</label>
            <select name="dosen_id" class="sipeng-input">
                <option value="">Semua</option>
                @foreach($filterOptions['dosenOptions'] ?? [] as $o)
                    <option value="{{ $o['value'] }}" @selected(($filters['dosen_id'] ?? '') === $o['value'])>{{ $o['label'] }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="sipeng-label">Status</label>
            <input type="text" name="status" value="{{ $filters['status'] ?? '' }}" class="sipeng-input" placeholder="approved, verified...">
        </div>
        <div>
            <label class="sipeng-label">Dari Tanggal</label>
            <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="sipeng-input">
        </div>
        <div>
            <label class="sipeng-label">Sampai Tanggal</label>
            <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="sipeng-input">
        </div>
        <div class="sm:col-span-2 lg:col-span-4 xl:col-span-6 flex flex-wrap gap-2">
            <button type="submit" class="sipeng-btn-primary text-sm">Terapkan Filter</button>
            <a href="{{ url()->current() }}" class="sipeng-btn-secondary text-sm">Reset</a>
        </div>
    </div>
</form>
