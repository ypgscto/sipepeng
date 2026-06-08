<nav class="flex flex-wrap gap-2 border-b border-slate-200 pb-3" aria-label="Tab referensi SIAKAD">
    @foreach ($tabs as $key => $label)
        <a href="{{ route('admin.siakad-reference.index', array_merge(request()->except('page'), ['tab' => $key])) }}"
            @class([
                'px-4 py-2 text-sm font-medium rounded-lg transition',
                'bg-emerald-700 text-white shadow-sm' => $activeTab === $key,
                'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' => $activeTab !== $key,
            ])>
            {{ $label }}
        </a>
    @endforeach
</nav>
