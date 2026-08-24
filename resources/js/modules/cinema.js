/**
 * ============================================================
 * cinema.js — Cinematic "movie" scroll experience (s17)
 * ------------------------------------------------------------
 * Adds the filmic layers to the homepage:
 *   1. Film opening — fade-from-black curtain lift on load,
 *      like a movie title card starting.
 *   2. Film grain — animated analog noise over the whole page.
 *   3. Vignette — soft cinematic darkening of the edges.
 * (Letterbox bars were REMOVED per user request 2026-08-13.)
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

export function initCinema(reducedMotion) {
    injectOverlays();
    initFilmOpening(reducedMotion);
}
