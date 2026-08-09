/**
 * ============================================================
 * cinema.js — Cinematic "movie" scroll experience (s17)
 * ------------------------------------------------------------
 * Adds the missing filmic layers to the homepage:
 *   1. Film opening — fade-from-black curtain lift on load,
 *      like a movie title card starting.
 *   2. Film grain — animated analog noise over the whole page.
 *   3. Vignette — soft cinematic darkening of the edges.
 *   4. Letterbox bars — widescreen top/bottom bars that appear
 *      while scrolling (velocity-driven) and fade when idle,
 *      like fast-forwarding a reel.
 * All overlays are aria-hidden, pointer-events: none, and
 * respect prefers-reduced-motion.
 * ============================================================
 */

function injectOverlays() {
    if (document.getElementById('cinema-overlays')) return;

    const overlays = document.createElement('div');
    overlays.id = 'cinema-overlays';
    overlays.setAttribute('aria-hidden', 'true');
    overlays.innerHTML = [
        '<div class="film-grain"></div>',
        '<div class="film-vignette"></div>',
        '<div class="letterbox-bar letterbox-top"></div>',
        '<div class="letterbox-bar letterbox-bottom"></div>',
    ].join('');

    document.body.appendChild(overlays);
}

/** Fade from black on first paint — the "movie starts" moment. */
function initFilmOpening(reducedMotion) {
    if (reducedMotion) return;

    const curtain = document.createElement('div');
    curtain.className = 'film-opening';
    curtain.setAttribute('aria-hidden', 'true');
    document.body.appendChild(curtain);

    // Double rAF so the initial paint has happened before we animate.
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            curtain.classList.add('is-open');
            window.setTimeout(() => curtain.remove(), 1400);
        });
    });
}

/** Widescreen bars that respond to scroll velocity. */
function initLetterbox(lenis) {
    const top = document.querySelector('.letterbox-top');
    const bottom = document.querySelector('.letterbox-bottom');
    if (!top || !bottom) return;

    let hideTimer = null;
    const show = () => {
        document.body.classList.add('is-scrolling-fast');
        window.clearTimeout(hideTimer);
        hideTimer = window.setTimeout(() => {
            document.body.classList.remove('is-scrolling-fast');
        }, 900);
    };

    if (lenis) {
        lenis.on('scroll', ({ velocity }) => {
            if (Math.abs(velocity) > 0.35) show();
        });
    }

    // Fallback for native scroll environments.
    let lastY = window.scrollY;
    window.addEventListener('scroll', () => {
        const delta = Math.abs(window.scrollY - lastY);
        lastY = window.scrollY;
        if (delta > 2) show();
    }, { passive: true });
}

export function initCinema(reducedMotion, lenis) {
    injectOverlays();
    initFilmOpening(reducedMotion);
    initLetterbox(lenis);
}
