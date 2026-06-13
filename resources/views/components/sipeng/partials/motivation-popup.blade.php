<div
    class="sipeng-motivation-popup"
    role="dialog"
    aria-modal="false"
    aria-labelledby="sipeng-motivation-title"
>
    <div class="sipeng-motivation-popup-header">
        <div class="min-w-0">
            <p id="sipeng-motivation-title" class="sipeng-motivation-popup-title">Motivasi SiPepeng</p>
            <p
                class="sipeng-motivation-popup-subtitle"
                x-text="quote?.source ? quote.source : 'Tips & semangat LPPM'"
            ></p>
        </div>
        <button
            type="button"
            class="sipeng-motivation-popup-close"
            aria-label="Tutup popup motivasi"
            @click="close(false)"
        >
            &times;
        </button>
    </div>

    <blockquote class="sipeng-motivation-popup-quote" x-text="quote?.text"></blockquote>

    <div class="sipeng-motivation-popup-actions">
        <button type="button" class="sipeng-motivation-popup-btn-primary" @click="semangat()">
            Semangat!
        </button>
        <button type="button" class="sipeng-motivation-popup-btn-secondary" @click="close(false)">
            Tutup
        </button>
        <button type="button" class="sipeng-motivation-popup-btn-link" @click="nextQuote()">
            Motivasi lainnya
        </button>
        <button type="button" class="sipeng-motivation-popup-btn-link" @click="hideForToday()">
            Jangan tampilkan lagi hari ini
        </button>
    </div>
</div>
