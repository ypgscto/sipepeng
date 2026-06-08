const STORAGE_LAST_SHOWN = 'sipeng-motivation-last-shown';
const STORAGE_DISMISSED = 'sipeng-motivation-dismissed-at';

function readTimestamp(key) {
    const raw = localStorage.getItem(key);
    if (! raw) {
        return null;
    }

    const value = Number.parseInt(raw, 10);

    return Number.isFinite(value) ? value : null;
}

function writeTimestamp(key) {
    localStorage.setItem(key, String(Date.now()));
}

export function registerSipengMascotAssistant(Alpine) {
    Alpine.data('sipengMascotAssistant', (config = {}) => ({
        quotes: Array.isArray(config.quotes) ? config.quotes : [],
        intervalMs: Number(config.intervalMs) || 300000,
        snoozeMs: Number(config.snoozeMs) || 300000,
        open: false,
        quote: null,
        _timer: null,

        init() {
            if (this.quotes.length === 0) {
                return;
            }

            this.pickRandom();
            this._timer = window.setInterval(() => this.tryAutoShow(), this.intervalMs);

            window.setTimeout(() => this.tryAutoShow(), this.intervalMs);
        },

        destroy() {
            if (this._timer) {
                window.clearInterval(this._timer);
            }
        },

        canAutoShow() {
            const dismissedAt = readTimestamp(STORAGE_DISMISSED);
            if (dismissedAt !== null && Date.now() - dismissedAt < this.snoozeMs) {
                return false;
            }

            const lastShown = readTimestamp(STORAGE_LAST_SHOWN);
            if (lastShown === null) {
                return true;
            }

            return Date.now() - lastShown >= this.intervalMs;
        },

        tryAutoShow() {
            if (! this.canAutoShow()) {
                return;
            }

            this.show(false);
        },

        openFromClick() {
            this.show(true);
        },

        show(fromManual = false) {
            if (this.quotes.length === 0) {
                return;
            }

            if (! fromManual && ! this.canAutoShow()) {
                return;
            }

            if (! this.quote) {
                this.pickRandom();
            }

            this.open = true;
            writeTimestamp(STORAGE_LAST_SHOWN);

            if (fromManual) {
                localStorage.removeItem(STORAGE_DISMISSED);
            }
        },

        close() {
            this.open = false;
            writeTimestamp(STORAGE_DISMISSED);
        },

        nextQuote() {
            this.pickRandom(true);
        },

        pickRandom(forceDifferent = false) {
            if (this.quotes.length === 0) {
                this.quote = null;

                return;
            }

            if (this.quotes.length === 1) {
                this.quote = this.quotes[0];

                return;
            }

            let next = this.quotes[Math.floor(Math.random() * this.quotes.length)];

            if (forceDifferent && this.quote) {
                let guard = 0;
                while (next.text === this.quote.text && guard < 8) {
                    next = this.quotes[Math.floor(Math.random() * this.quotes.length)];
                    guard += 1;
                }
            }

            this.quote = next;
        },
    }));
}
