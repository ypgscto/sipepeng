@props([
    'variant' => 'corner',
])

@php
    $configured = config('sipepeng_settings.mascot.image_display')
        ?? config('sipepeng_settings.mascot.image', 'images/sipepengmaskot_bg.png');

    if (in_array($configured, ['images/sipepeng-mascot.png', 'images/sipepeng-mascot-source.png'], true)) {
        $configured = 'images/sipepeng-maskot-520w.png';
    }

    $fallback = $configured;
    $displayWidths = [
        400 => 'images/sipepeng-maskot-400w.png',
        520 => 'images/sipepeng-maskot-520w.png',
        780 => 'images/sipepeng-maskot-780w.png',
    ];

    $srcset = [];
    foreach ($displayWidths as $width => $path) {
        if (file_exists(public_path($path))) {
            $srcset[] = asset($path).' '.$width.'w';
        }
    }

    $defaultPath = file_exists(public_path($displayWidths[520] ?? ''))
        ? $displayWidths[520]
        : (file_exists(public_path($fallback)) ? $fallback : 'images/sipepengmaskot_bg.png');

    $src = asset($defaultPath);
    $sizes = match ($variant) {
        'login' => '(min-width: 640px) 11.5rem, 9.5rem',
        default => '(min-width: 640px) 16rem, 13rem',
    };
    $loading = $variant === 'corner' ? 'eager' : 'lazy';
    $alt = 'Maskot '.($sipengBranding['app_name'] ?? 'SiPepeng');
@endphp

@if ($variant === 'login')
    <div {{ $attributes->merge(['class' => 'sipeng-mascot-login']) }}>
        <div @class(['sipeng-mascot-figure', 'sipeng-mascot-figure--login']) aria-hidden="true">
            <div class="sipeng-mascot-figure__motion">
                <img
                    src="{{ $src }}"
                    @if ($srcset !== [])
                        srcset="{{ implode(', ', $srcset) }}"
                        sizes="{{ $sizes }}"
                    @endif
                    alt="{{ $alt }}"
                    class="sipeng-mascot-figure__img"
                    width="520"
                    height="780"
                    loading="{{ $loading }}"
                    decoding="async"
                    draggable="false"
                >
            </div>
        </div>
    </div>
@elseif ($variant === 'assistant')
    <div @class(['sipeng-mascot-figure', 'sipeng-mascot-figure--assistant']) aria-hidden="true">
        <div class="sipeng-mascot-figure__motion">
            <img
                src="{{ $src }}"
                @if ($srcset !== [])
                    srcset="{{ implode(', ', $srcset) }}"
                    sizes="{{ $sizes }}"
                @endif
                alt="{{ $alt }}"
                class="sipeng-mascot-figure__img"
                width="520"
                height="780"
                loading="lazy"
                decoding="async"
                draggable="false"
            >
        </div>
    </div>
@else
    <div {{ $attributes->merge(['class' => 'sipeng-mascot-corner']) }}>
        <div @class(['sipeng-mascot-figure', 'sipeng-mascot-figure--corner']) aria-hidden="true">
            <div class="sipeng-mascot-figure__motion">
                <img
                    src="{{ $src }}"
                    @if ($srcset !== [])
                        srcset="{{ implode(', ', $srcset) }}"
                        sizes="{{ $sizes }}"
                    @endif
                    alt="{{ $alt }}"
                    class="sipeng-mascot-figure__img"
                    width="520"
                    height="780"
                    loading="{{ $loading }}"
                    decoding="async"
                    draggable="false"
                >
            </div>
        </div>
    </div>
@endif
