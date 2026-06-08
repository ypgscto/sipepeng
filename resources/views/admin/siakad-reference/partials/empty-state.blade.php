<div class="text-center py-16 px-6">
    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400 mb-4">
        <x-sipeng.icon name="database" class="h-7 w-7" />
    </div>
    <h3 class="text-lg font-semibold text-slate-900">Tidak ada data</h3>
    <p class="mt-2 text-sm text-slate-600 max-w-md mx-auto">
        @if (!empty($error))
            Data tidak dapat dimuat dari SIAKAD-API. Periksa koneksi dan konfigurasi token.
        @elseif (!empty($filters))
            Tidak ada data yang cocok dengan filter pencarian.
        @else
            Belum ada data referensi dari SIAKAD-API. Klik Muat Ulang untuk mengambil data.
        @endif
    </p>
</div>
