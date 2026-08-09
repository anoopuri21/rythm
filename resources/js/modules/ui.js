function initNavbar() {
    const navbar = document.getElementById('navbar');
    if (!navbar) return;

    let ticking = false;
    const render = () => {
        const isScrolled = window.scrollY > 50;
        navbar.classList.toggle('navbar-transparent', !isScrolled);
        navbar.classList.toggle('navbar-solid', isScrolled);
        navbar.querySelectorAll('.nav-link').forEach((link) => {
            link.classList.toggle('text-white', !isScrolled);
            link.classList.toggle('text-rythme-black', isScrolled);
        });
        const logo = navbar.querySelector('.nav-logo');
        logo?.classList.toggle('text-white', !isScrolled);
        logo?.classList.toggle('text-gold', isScrolled);
        const logoText = logo?.querySelector('span');
        logoText?.classList.toggle('text-white', !isScrolled);
        logoText?.classList.toggle('text-rythme-black', isScrolled);
        ticking = false;
    };
    const requestRender = () => {
        if (ticking) return;
        ticking = true;
        window.requestAnimationFrame(render);
    };

    render();
    window.addEventListener('scroll', requestRender, { passive: true });
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

export function initUi() {
    initNavbar();
    initCountdowns();
    initNewsletter();
    initScrollTop();
}

function initScrollTop() {
    const button = document.getElementById('scroll-top');
    if (!button) return;

    const render = () => {
        button.classList.toggle('is-visible', window.scrollY > 600);
    };

    button.addEventListener('click', () => {
        window.lenis ? window.lenis.scrollTo(0, { duration: 1.2 }) : window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    render();
    window.addEventListener('scroll', render, { passive: true });
}
