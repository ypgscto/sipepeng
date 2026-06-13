@props([
    'layout' => 'dashboard',
])

@php
    $quotes = config('sipepeng_motivation.quotes', []);
    $firstDelayMs = (int) config('sipepeng_motivation.first_delay_seconds', 30) * 1000;
    $intervalMs = (int) config('sipepeng_motivation.interval_minutes', 5) * 60 * 1000;
    $snoozeMs = (int) config('sipepeng_motivation.snooze_minutes', 5) * 60 * 1000;
    $appName = $sipengBranding['app_name'] ?? 'SiPepeng';
    $peekMode = $layout === 'dashboard';
    $anchorClass = $peekMode
        ? 'sipeng-mascot-assistant-anchor sipeng-mascot-assistant-anchor--peek fixed bottom-[4.25rem] sm:bottom-[4.75rem] z-30 left-3 sm:left-5 transition-[left] duration-300 lg:left-[17rem]'
        : 'sipeng-mascot-assistant-anchor fixed bottom-[4.25rem] sm:bottom-[4.75rem] z-30 left-3 sm:left-5';
@endphp

@if ($quotes !== [])
    <div
        {{ $attributes->merge(['class' => $anchorClass]) }}
        @if ($peekMode)
            :class="$store.sidebar.collapsed && 'lg:!left-[5.25rem]'"
        @endif
        x-data="sipengMascotAssistant({
            quotes: @js($quotes),
            firstDelayMs: {{ $firstDelayMs }},
            intervalMs: {{ $intervalMs }},
            snoozeMs: {{ $snoozeMs }},
            peekMode: {{ $peekMode ? 'true' : 'false' }},
        })"
    >
        @if ($peekMode)
            {{-- Dashboard: maskot + popup muncul bersamaan, hilang saat ditutup --}}
            <div
                x-show="open"
                x-cloak
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-4"
                @keydown.escape.window="close(false)"
                class="relative"
            >
                <div class="sipeng-mascot-assistant-trigger pointer-events-none" aria-hidden="true">
                    <span class="sipeng-mascot-bounce block sipeng-mascot-bounce--attention">
                        <x-sipeng.mascot variant="assistant" />
                    </span>
                </div>

                @include('components.sipeng.partials.motivation-popup')
            </div>
        @else
            {{-- Halaman publik: maskot permanen, klik untuk buka popup --}}
            <div class="relative">
                <button
                    type="button"
                    class="sipeng-mascot-assistant-trigger group"
                    aria-label="Maskot {{ $appName }} — klik untuk motivasi"
                    title="Klik untuk motivasi!"
                    aria-expanded="false"
                    :aria-expanded="open.toString()"
                    @click="openFromClick()"
                >
                    <span class="sipeng-mascot-bounce block" :class="open && 'sipeng-mascot-bounce--attention'">
                        <x-sipeng.mascot variant="assistant" />
                    </span>
                    <span class="sr-only">Klik untuk motivasi LPPM</span>
                </button>

                <div
                    x-show="open"
                    x-cloak
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                    x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                    @keydown.escape.window="close(false)"
                >
                    @include('components.sipeng.partials.motivation-popup')
                </div>
            </div>
        @endif
    </div>
@endif
