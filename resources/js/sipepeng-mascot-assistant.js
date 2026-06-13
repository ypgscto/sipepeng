const STORAGE_LAST_SHOWN = 'sipeng-motivation-last-shown';
const STORAGE_DISMISSED = 'sipeng-motivation-dismissed-at';
const STORAGE_HIDE_TODAY = 'sipeng-motivation-hide-today';
const STORAGE_HIDE_DATE = 'sipeng-motivation-hide-date';

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

function todayKey() {
    const now = new Date();

    return [
        now.getFullYear(),
        String(now.getMonth() + 1).padStart(2, '0'),
        String(now.getDate()).padStart(2, '0'),
    ].join('-');
}

function isHiddenForToday() {
    const storedDate = localStorage.getItem(STORAGE_HIDE_DATE);
    const today = todayKey();

    if (storedDate !== today) {
        localStorage.removeItem(STORAGE_HIDE_TODAY);
        localStorage.removeItem(STORAGE_HIDE_DATE);

        return false;
    }

    return localStorage.getItem(STORAGE_HIDE_TODAY) === '1';
}

function setHiddenForToday(hide) {
    if (hide) {
        localStorage.setItem(STORAGE_HIDE_TODAY, '1');
        localStorage.setItem(STORAGE_HIDE_DATE, todayKey());
    } else {
        localStorage.removeItem(STORAGE_HIDE_TODAY);
        localStorage.removeItem(STORAGE_HIDE_DATE);
    }
}

export function registerSipengMascotAssistant(Alpine) {
    Alpine.data('sipengMascotAssistant', (config = {}) => ({
        quotes: Array.isArray(config.quotes) ? config.quotes : [],
        firstDelayMs: Number(config.firstDelayMs) || 30000,
        intervalMs: Number(config.intervalMs) || 300000,
        snoozeMs: Number(config.snoozeMs) || 300000,
        peekMode: Boolean(config.peekMode),
        open: false,
        quote: null,
        _firstTimer: null,
        _intervalTimer: null,

        init() {
            if (this.quotes.length === 0) {
                return;
            }

            this.pickRandom();
            this._firstTimer = window.setTimeout(() => this.tryAutoShow(), this.firstDelayMs);
            this._intervalTimer = window.setInterval(() => this.tryAutoShow(), this.intervalMs);
        },

        destroy() {
            if (this._firstTimer) {
                window.clearTimeout(this._firstTimer);
            }

            if (this._intervalTimer) {
                window.clearInterval(this._intervalTimer);
            }
        },

        canAutoShow() {
            if (isHiddenForToday()) {
                return false;
            }

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
            if (this.open) {
                this.close(false);

                return;
            }

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

        close(hideForToday = false) {
            this.open = false;
            writeTimestamp(STORAGE_DISMISSED);

            if (hideForToday) {
                setHiddenForToday(true);
            }
        },

        semangat() {
            this.close(false);
        },

        hideForToday() {
            this.close(true);
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
