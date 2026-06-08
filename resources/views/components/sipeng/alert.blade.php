@props(['type' => 'success', 'title' => null])

@php
    $styles = match ($type) {
        'error' => 'border-rose-200 bg-rose-50 text-rose-900',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-900',
        'info' => 'border-sky-200 bg-sky-50 text-sky-900',
        default => 'border-emerald-200 bg-emerald-50 text-emerald-900',
    };
@endphp

<div {{ $attributes->merge(['class' => "rounded-lg border px-4 py-3 text-sm {$styles}"]) }} role="alert">
    @if ($title)
        <p class="font-semibold mb-0.5">{{ $title }}</p>
    @endif
    <div>{{ $slot }}</div>
</div>
