@php
    $quotes = config('sipepeng_motivation.quotes', []);
    $intervalMs = (int) config('sipepeng_motivation.interval_minutes', 5) * 60 * 1000;
    $snoozeMs = (int) config('sipepeng_motivation.snooze_minutes', 5) * 60 * 1000;
    $mascotSrc = asset('images/sipepengmaskot_bg.png');
    $appName = $sipengBranding['app_name'] ?? 'SiPepeng';
@endphp

@if ($quotes !== [])
    <div
        class="sipeng-mascot-assistant-anchor fixed bottom-[4.25rem] sm:bottom-[4.75rem] z-30 left-3 sm:left-5 transition-[left] duration-300 lg:left-[17rem]"
        :class="$store.sidebar.collapsed && 'lg:!left-[5.25rem]'"
        x-data="sipengMascotAssistant({
            quotes: @js($quotes),
            intervalMs: {{ $intervalMs }},
            snoozeMs: {{ $snoozeMs }},
        })"
    >
        <div class="relative">
            <button
                type="button"
                class="sipeng-mascot-assistant-trigger group"
                aria-label="Buka motivasi {{ $appName }}"
                aria-expanded="false"
                :aria-expanded="open.toString()"
                @click="openFromClick()"
            >
                <span class="sipeng-mascot-bounce block" :class="open && 'sipeng-mascot-bounce--attention'">
                    <img
                        src="{{ $mascotSrc }}"
                        alt="Maskot {{ $appName }}"
                        class="sipeng-mascot-main"
                        draggable="false"
                    >
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
                @keydown.escape.window="close()"
                class="sipeng-motivation-popup"
                role="dialog"
                aria-modal="false"
                aria-labelledby="sipeng-motivation-title"
            >
                <div class="sipeng-motivation-popup-header">
                    <div class="min-w-0">
                        <p id="sipeng-motivation-title" class="sipeng-motivation-popup-title">Motivasi SiPepeng</p>
                        <p class="sipeng-motivation-popup-subtitle">Tips &amp; semangat LPPM</p>
                    </div>
                    <button
                        type="button"
                        class="sipeng-motivation-popup-close"
                        aria-label="Tutup popup motivasi"
                        @click="close()"
                    >
                        &times;
                    </button>
                </div>

                <blockquote class="sipeng-motivation-popup-quote" x-text="quote?.text"></blockquote>
                <p class="sipeng-motivation-popup-source" x-text="quote?.source ? '— ' + quote.source : ''"></p>

                <div class="sipeng-motivation-popup-actions">
                    <button type="button" class="sipeng-motivation-popup-next" @click="nextQuote()">
                        Motivasi lainnya
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
