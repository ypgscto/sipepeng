@props([
    'yearOptions' => [],
    'filter' => null,
    'action' => route('public.dashboard'),
])

<form method="GET" action="{{ $action }}" class="flex flex-wrap items-end gap-3">
    <div>
        <label for="year" class="sipeng-label text-xs">Tahun</label>
        <select id="year" name="year" class="sipeng-input min-w-[8rem] py-2 text-sm">
            @foreach ($yearOptions as $year)
                <option value="{{ $year }}" @selected(($filter->year ?? (int) now()->format('Y')) === $year)>
                    {{ $year }}
                </option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="sipeng-btn-primary text-sm py-2 px-4">Terapkan</button>
</form>
