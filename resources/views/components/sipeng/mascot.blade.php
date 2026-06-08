@props([
    'variant' => 'corner',
])

@php
    $src = asset('images/sipepengmaskot_bg.png');
    $alt = 'Maskot '.($sipengBranding['app_name'] ?? 'SiPepeng');
@endphp

@if ($variant === 'login')
    <span {{ $attributes->merge(['class' => 'sipeng-mascot-login']) }}>
        <span class="sipeng-mascot-bounce block w-full h-full">
            <img
                src="{{ $src }}"
                alt="{{ $alt }}"
                class="block w-full h-full object-contain pointer-events-none select-none drop-shadow-2xl"
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
            class="block w-full h-full object-contain pointer-events-none select-none drop-shadow-xl"
            draggable="false"
        >
    </span>
@endif
