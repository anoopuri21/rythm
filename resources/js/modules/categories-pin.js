/**
 * ============================================================
 * categories-pin.js — Pinned horizontal categories (P2)
 * ------------------------------------------------------------
 * The #categories section sticks while scrolling; the card track
 * translates left→right driven by scroll progress; HUD progress
 * bar + counter update; unpins at the end.
 * Mobile (≤900px)  → horizontal touch scroll, no pin.
 * Reduced motion   → static grid fallback (CSS handles layout).
 * ============================================================
 */

export function initCategoriesPin(reducedMotion) {
    const wrap = document.querySelector('#categories.pin');
    if (!wrap) return;

    const stage = wrap.querySelector('.pin__stage');
    const view = wrap.querySelector('.pin__viewport');
    const track = wrap.querySelector('.pin__track');
    const bar = document.querySelector('#pin-progress');
    const count = document.querySelector('#pin-count');

    if (!stage || !view || !track) return;

    const total = Number(track.dataset.total || track.children.length);
    const isMobile = window.matchMedia('(max-width: 900px)').matches;

    // Mobile / reduced motion: CSS already renders a scrollable/grid layout.
    if (reducedMotion || isMobile) return;

    let maxX = 0;

    const render = () => {
        if (maxX <= 0) return;

        const top = wrap.getBoundingClientRect().top + window.scrollY;
        const height = wrap.offsetHeight;
        const progress = Math.min(1, Math.max(0, (window.scrollY - top) / Math.max(1, height - window.innerHeight)));

        track.style.transform = `translate3d(${(-progress * maxX).toFixed(1)}px,0,0)`;

        if (bar) bar.style.width = `${(progress * 100).toFixed(1)}%`;

        if (count) {
            const idx = Math.min(total, Math.max(1, Math.round(progress * (total - 1)) + 1));
            count.textContent = `${String(idx).padStart(2, '0')} / ${String(total).padStart(2, '0')}`;
        }
    };

    const measure = () => {
        const viewWidth = view.clientWidth;
        const trackWidth = track.scrollWidth;
        maxX = Math.max(0, trackWidth - viewWidth);

        // Give the wrapper extra height equal to the travel distance so the
        // sticky stage pins for exactly the duration of the horizontal run.
        if (maxX > 0) {
            wrap.style.height = `${stage.offsetHeight + maxX}px`;
        } else {
            wrap.style.height = 'auto';
        }

        render();
    };

    let ticking = false;
    const onScroll = () => {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(() => {
            render();
            ticking = false;
        });
    };

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', measure);
    document.fonts?.ready.then(measure);
    window.addEventListener('load', measure, { once: true });

    measure();
}
