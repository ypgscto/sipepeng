@props([
    'variant' => 'corner',
])

@php
    $mascotPath = config('sipepeng_settings.mascot.image', 'images/sipepeng-mascot.png');
    $src = file_exists(public_path($mascotPath))
        ? asset($mascotPath)
        : asset('images/sipepeng-mascot.png');
    $alt = 'Maskot '.($sipengBranding['app_name'] ?? 'SiPepeng');
@endphp

@if ($variant === 'login')
    <span {{ $attributes->merge(['class' => 'sipeng-mascot-login']) }}>
        <span class="sipeng-mascot-bounce block w-full h-full">
            <img
                src="{{ $src }}"
                alt="{{ $alt }}"
                class="block w-full h-full object-contain pointer-events-none select-none"
                draggable="false"
            >
        </span>
    </span>
@else
    <span
        {{ $attributes->merge([
            'class' => 'sipeng-mascot-bounce inline-block',
        ]) }}
    >
        <img
            src="{{ $src }}"
            alt="{{ $alt }}"
            class="block w-full h-full object-contain pointer-events-none select-none"
            draggable="false"
        >
    </span>
@endif
