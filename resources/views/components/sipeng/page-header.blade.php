@props(['title', 'description' => null])

<div class="border-b border-slate-200/80 pb-4 mb-1">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <h2 class="text-xl sm:text-2xl font-bold text-sipeng-navy-900 tracking-tight">{{ $title }}</h2>
            @if ($description)
                <p class="mt-1 text-sm text-slate-600 max-w-3xl leading-relaxed">{{ $description }}</p>
            @endif
        </div>
        @isset($actions)
            <div class="flex flex-wrap items-center gap-2 shrink-0">
                {{ $actions }}
            </div>
        @endisset
    </div>
</div>
