function initNavbar() {
    const navbar = document.getElementById('navbar');
    if (!navbar) return;

    // Prototype-style: white blur navbar gains a soft shadow on scroll.
    const render = () => {
        navbar.classList.toggle('scrolled', window.scrollY > 8);
    };

    render();
    window.addEventListener('scroll', render, { passive: true });
}

function initCountdowns() {
    document.querySelectorAll('.deal-countdown').forEach((clock) => {
        const storageKey = 'rythme-deal-deadline';
        const duration = Number(clock.dataset.deadlineHours || 72) * 60 * 60 * 1000;
        let deadline;

        try {
            deadline = Number(window.localStorage.getItem(storageKey));
            if (!deadline || deadline <= Date.now()) {
                deadline = Date.now() + duration;
                window.localStorage.setItem(storageKey, String(deadline));
            }
        } catch {
            deadline = Date.now() + duration;
        }

        const render = () => {
            const remaining = Math.max(0, deadline - Date.now());
            const values = {
                days: Math.floor(remaining / 86400000),
                hours: Math.floor((remaining / 3600000) % 24),
                minutes: Math.floor((remaining / 60000) % 60),
                seconds: Math.floor((remaining / 1000) % 60),
            };
            Object.entries(values).forEach(([unit, value]) => {
                const output = clock.querySelector(`[data-unit="${unit}"]`);
                if (output) output.textContent = String(value).padStart(2, '0');
            });
        };

        render();
        window.setInterval(render, 1000);
    });
}

function initNewsletter() {
    const form = document.querySelector('.newsletter-form');
    if (!form || !window.fetch) return;

    const button = form.querySelector('.newsletter-submit');
    const buttonLabel = button?.querySelector('span');
    const feedback = form.querySelector('.newsletter-feedback');
    const token = document.querySelector('meta[name="csrf-token"]')?.content;

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!form.reportValidity()) return;

        button?.setAttribute('disabled', 'disabled');
        if (buttonLabel) buttonLabel.textContent = 'Joining…';
        if (feedback) {
            feedback.textContent = 'Saving your place…';
            feedback.classList.remove('text-red-300', 'text-gold-light');
        }

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': token || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new FormData(form),
            });
            const payload = await response.json().catch(() => ({}));

            if (!response.ok) {
                const message = payload.errors?.email?.[0] || payload.message || 'We could not add you right now.';
                throw new Error(message);
            }

            form.reset();
            if (feedback) {
                feedback.textContent = payload.message;
                feedback.classList.add('text-gold-light');
            }
        } catch (error) {
            if (feedback) {
                feedback.textContent = error.message || 'Something went wrong. Please try again.';
                feedback.classList.add('text-red-300');
            }
        } finally {
            button?.removeAttribute('disabled');
            if (buttonLabel) buttonLabel.textContent = 'Join the list';
        }
    });
}

function initOfferPopup() {
    const popup = document.querySelector('[data-offer-popup]');
    if (!popup || popup.dataset.initialized === 'true') return;

    popup.dataset.initialized = 'true';
    const closeButton = popup.querySelector('[data-offer-popup-close]');
    const dialog = popup.querySelector('.offer-popup__dialog');
    const storageKey = popup.dataset.offerPopupStorageKey || 'rythme-offer-popup-closed-at-v1';
    const dayInMilliseconds = 24 * 60 * 60 * 1000;

    if (!closeButton || !dialog) return;

    try {
        const closedAt = Number(window.localStorage.getItem(storageKey));
        if (Number.isFinite(closedAt) && closedAt > 0 && Date.now() - closedAt < dayInMilliseconds) {
            popup.remove();
            return;
        }
    } catch {
        // Show the offer when browser storage is unavailable; close remains page-local.
    }

    popup.classList.remove('is-pending');
    popup.setAttribute('aria-hidden', 'false');
    document.body.classList.add('offer-popup-open');

    const focusableSelector = 'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])';
    const focusables = [...dialog.querySelectorAll(focusableSelector)];
    const close = () => {
        try {
            window.localStorage.setItem(storageKey, String(Date.now()));
        } catch {
            // The popup still closes for the current page when storage is blocked.
        }

        popup.classList.add('is-closing');
        popup.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('offer-popup-open');
        window.setTimeout(() => popup.remove(), 220);
    };

    closeButton.addEventListener('click', close);
    popup.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            event.preventDefault();
            close();
            return;
        }

        if (event.key !== 'Tab' || focusables.length < 2) return;
        const first = focusables[0];
        const last = focusables[focusables.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });

    closeButton.focus({ preventScroll: true });
}

function initRecentPurchasePreview() {
    const preview = document.querySelector('[data-recent-purchase-demo]');
    if (!preview || preview.dataset.initialized === 'true') return;

    preview.dataset.initialized = 'true';
    const storageKey = 'rythme-recent-purchase-preview-dismissed-v1';
    const closeButton = preview.querySelector('[data-recent-purchase-close]');
    const cards = [...preview.querySelectorAll('[data-recent-purchase-card]')];

    if (!closeButton || cards.length === 0) return;

    try {
        if (window.localStorage.getItem(storageKey) === '1') {
            preview.remove();
            return;
        }
    } catch {
        // Continue with an in-page dismissal if browser storage is unavailable.
    }

    let active = 0;
    let timer = null;
    let paused = false;
    const reducedMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches ?? false;

    const showCard = (index) => {
        active = (index + cards.length) % cards.length;
        cards.forEach((card, cardIndex) => {
            const isActive = cardIndex === active;
            card.classList.toggle('is-active', isActive);
            card.setAttribute('aria-hidden', isActive ? 'false' : 'true');
        });
    };

    const stopTimer = () => {
        if (timer !== null) {
            window.clearInterval(timer);
            timer = null;
        }
    };

    const startTimer = () => {
        stopTimer();
        if (paused || reducedMotion || cards.length < 2) return;
        timer = window.setInterval(() => showCard(active + 1), 10000);
    };

    const dismiss = () => {
        stopTimer();
        try {
            window.localStorage.setItem(storageKey, '1');
        } catch {
            // The current page is still hidden when persistent storage is blocked.
        }
        preview.classList.add('is-dismissed');
        window.setTimeout(() => preview.remove(), 260);
    };

    closeButton.addEventListener('click', dismiss);
    preview.addEventListener('mouseenter', () => {
        paused = true;
        stopTimer();
    });
    preview.addEventListener('mouseleave', () => {
        paused = false;
        startTimer();
    });
    preview.addEventListener('focusin', () => {
        paused = true;
        stopTimer();
    });
    preview.addEventListener('focusout', (event) => {
        if (!preview.contains(event.relatedTarget)) {
            paused = false;
            startTimer();
        }
    });

    showCard(0);
    startTimer();
}

export function initUi() {
    initNavbar();
    initCountdowns();
    initNewsletter();
    initOfferPopup();
    initRecentPurchasePreview();
    initScrollTop();
}

function initScrollTop() {
    const button = document.getElementById('scroll-top');
    if (!button) return;

    let visible = false;
    const render = () => {
        const show = window.scrollY > 400;
        if (show !== visible) {
            visible = show;
            button.classList.toggle('is-visible', show);
        }
    };

    button.addEventListener('click', () => {
        if (window.lenis && typeof window.lenis.scrollTo === 'function') {
            window.lenis.scrollTo(0, { duration: 1.2 });
        } else {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });

    // Native scroll + Lenis (both drive the same render; cheap & idempotent)
    window.addEventListener('scroll', render, { passive: true });
    if (window.lenis) window.lenis.on('scroll', render);

    render();
}
