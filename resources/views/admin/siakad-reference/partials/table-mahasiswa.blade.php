<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-4 py-3 text-left font-semibold text-slate-700">NIM</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-700">Nama</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-700">Prodi</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-700">Angkatan</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-700">Status</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-700">Email</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-700">HP</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white">
            @foreach ($records as $row)
                <tr class="hover:bg-slate-50/80">
                    <td class="px-4 py-3 font-mono text-xs text-slate-600">{{ $row['nim'] ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-900">{{ $row['nama'] ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-600">
                        <span class="block text-xs text-slate-500">{{ $row['prodi_siakad_id'] ?? '—' }}</span>
                        <span>{{ $row['nama_prodi'] ?? '' }}</span>
                    </td>
                    <td class="px-4 py-3 text-slate-600">{{ $row['angkatan'] ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $row['status_mahasiswa_nama'] ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $row['email'] ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $row['nomor_hp'] ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
