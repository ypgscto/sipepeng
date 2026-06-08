@props([
    'size' => 'md',
    'forceExpanded' => false,
    'class' => '',
])

@inject('branding', 'App\Services\BrandingService')

@php
    $sizes = [
        'sm' => 'h-10 w-10',
        'md' => 'h-12 w-12',
        'lg' => 'h-14 w-14',
        'xl' => 'h-16 w-16',
    ];
    $sizeClass = $sizes[$size] ?? $sizes['md'];
    $hasLogo = $branding->hasLogo();
    $logoUrl = $branding->logoUrl();
    $institutionName = $branding->get('institution_name', config('sipeng_branding.institution_name'));
@endphp

@if ($hasLogo && $logoUrl)
    <img
        src="{{ $logoUrl }}"
        alt="Logo {{ $institutionName }}"
        {{ $attributes->merge(['class' => "{$sizeClass} shrink-0 object-contain rounded-full bg-white ring-2 ring-white/30 shadow-lg transition-all duration-300 {$class}"]) }}
        @if (! $forceExpanded)
            :class="$store.sidebar.collapsed ? 'h-11 w-11' : '{{ $sizeClass }}'"
        @endif
    />
@else
    <span
        {{ $attributes->merge(['class' => "{$sizeClass} shrink-0 inline-flex items-center justify-center rounded-full bg-white text-emerald-800 font-bold text-sm ring-2 ring-white/30 shadow-lg {$class}"]) }}
        title="{{ $institutionName }}"
    >
        SG
    </span>
@endif
