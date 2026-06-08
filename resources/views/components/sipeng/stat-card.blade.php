@props([
    'label',
    'value',
    'icon' => 'link',
    'tone' => 'emerald',
])

@php
    $tones = [
        'emerald' => 'bg-pink-50 text-pink-800 border-pink-200',
        'teal' => 'bg-pink-50 text-pink-800 border-pink-200',
        'pink' => 'bg-pink-50 text-pink-800 border-pink-200',
        'amber' => 'bg-amber-50 text-amber-800 border-amber-200',
        'sky' => 'bg-sky-50 text-sky-800 border-sky-200',
        'indigo' => 'bg-sipeng-navy-800/10 text-sipeng-navy-900 border-sipeng-navy-800/20',
        'slate' => 'bg-slate-50 text-slate-700 border-slate-200',
    ];
    $toneClass = $tones[$tone] ?? $tones['pink'];
@endphp

<div class="sipeng-card border-l-4 border-l-pink-500">
    <div class="sipeng-card-body flex items-start gap-4 py-4">
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border {{ $toneClass }}">
            <x-sipeng.icon :name="$icon" class="h-5 w-5" />
        </div>
        <div class="min-w-0 flex-1">
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ $label }}</p>
            <p class="mt-1 text-xl sm:text-2xl font-bold text-sipeng-navy-900 tabular-nums break-words">{{ $value }}</p>
        </div>
    </div>
</div>
