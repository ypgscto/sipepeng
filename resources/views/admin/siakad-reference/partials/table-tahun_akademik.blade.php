<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-4 py-3 text-left font-semibold text-slate-700">Tahun ID</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-700">Nama Tahun Akademik</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-700">Semester</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-700">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white">
            @foreach ($records as $row)
                <tr class="hover:bg-slate-50/80">
                    <td class="px-4 py-3 font-mono text-xs text-slate-600">{{ $row['siakad_id'] ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-900">{{ $row['nama_tahun_akademik'] ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-600 capitalize">{{ $row['semester'] ?? '—' }}</td>
                    <td class="px-4 py-3">
                        @if ($row['is_active'] ?? false)
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-800">Aktif</span>
                        @else
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">Nonaktif</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
