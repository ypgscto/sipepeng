@props([
    'title' => 'Filter',
    'collapsible' => true,
])

<div {{ $attributes->merge(['class' => 'sipeng-card overflow-hidden']) }} x-data="{ open: false }">
    @if ($collapsible)
        <button type="button" class="sipeng-filter-toggle" @click="open = !open" :aria-expanded="open">
            <span class="flex items-center gap-2">
                <x-sipeng.icon name="filter" class="h-4 w-4 text-pink-700" />
                {{ $title }}
            </span>
            <svg class="h-5 w-5 text-slate-500 transition-transform" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
    @endif

    <div @class(['sipeng-card-body bg-slate-50/40', 'hidden sm:block' => $collapsible]) @if ($collapsible) :class="open && '!block'" @endif>
        {{ $slot }}
    </div>
</div>
