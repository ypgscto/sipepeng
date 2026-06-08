@if (session('success'))
    <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        {{ session('error') }}
    </div>
@endif

@if (!empty($error))
    <div @class([
        'rounded-lg border px-4 py-3 text-sm',
        'border-amber-200 bg-amber-50 text-amber-900' => ($meta['is_stale'] ?? false) && count($records) > 0,
        'border-red-200 bg-red-50 text-red-800' => !($meta['is_stale'] ?? false) || count($records) === 0,
    ])>
        @if (($meta['is_stale'] ?? false) && count($records) > 0)
            <p class="font-medium">Menampilkan data cache terakhir. API tidak terjangkau.</p>
            <p class="mt-1">{{ $error }}</p>
        @else
            <p class="font-medium">Gagal memuat data dari SIAKAD-API</p>
            <p class="mt-1">{{ $error }}</p>
            <p class="mt-2 text-xs opacity-80">Pastikan SIAKAD-API berjalan dan token di .env valid. Klik Muat Ulang untuk mencoba lagi.</p>
        @endif
    </div>
@endif
