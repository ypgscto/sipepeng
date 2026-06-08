<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div class="flex flex-wrap items-center gap-2 text-sm text-slate-600">
        <span>Menampilkan <strong>{{ number_format($filtered_total) }}</strong> dari <strong>{{ number_format($total) }}</strong> data</span>
        @if ($meta['fetched_at'])
            <span class="text-slate-400">•</span>
            <span>Diperbarui: {{ $meta['fetched_at']->timezone(config('app.timezone'))->format('d M Y H:i') }}</span>
        @endif
        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
            @if ($meta['source'] === 'api') bg-blue-50 text-blue-800 border border-blue-200
            @elseif ($meta['source'] === 'stale_cache') bg-amber-50 text-amber-800 border border-amber-200
            @else bg-slate-100 text-slate-700 border border-slate-200 @endif">
            @if ($meta['source'] === 'api') Live API
            @elseif ($meta['source'] === 'stale_cache') Cache Usang
            @elseif ($meta['source'] === 'cache') Cache
            @else Belum dimuat @endif
        </span>
        @if ($meta['cache_enabled'])
            <span class="text-xs text-slate-500">TTL {{ $meta['ttl_minutes'] }} menit</span>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.siakad-reference.refresh') }}" class="flex gap-2">
        @csrf
        <input type="hidden" name="tab" value="{{ $tab }}">
        @foreach ($filters as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach
        <button type="submit" class="sipeng-btn-secondary text-sm">
            Muat Ulang
        </button>
    </form>
</div>
