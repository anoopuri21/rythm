import { initUi } from './modules/ui';

const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/**
 * A failing dynamic import must stay local. Without a catch here a single 404
 * (stale build hash on the host, blocked chunk) rejects the promise silently
 * and the whole module's DOM work — every carousel on the page — is skipped
 * with nothing in the console to explain why.
 */
function loadModule(label, loader) {
    return loader().catch((error) => {
        console.warn(`[home] failed to load ${label}`, error);
    });
}

document.addEventListener('DOMContentLoaded', async () => {
    try {
        initUi();
    } catch (error) {
        console.warn('[home] initUi failed', error);
    }

    const jobs = [];

    if (document.querySelector('.swiper')) {
        jobs.push(loadModule('carousels', () => import('./modules/carousels').then(({ initCarousels }) => initCarousels(reducedMotion))));
    }

    // GSAP, ScrollTrigger, Lenis and CountUp are homepage-only payloads.
    if (document.querySelector('.hero-mm')) {
        jobs.push(loadModule('motion', () => import('./modules/motion').then(({ initMotion }) => initMotion(reducedMotion))));
        jobs.push(loadModule('cinema', () => import('./modules/cinema').then(({ initCinema }) => initCinema(reducedMotion))));
    }

    if (document.querySelector('#categories.pin')) {
        jobs.push(loadModule('categories-pin', () => import('./modules/categories-pin').then(({ initCategoriesPin }) => initCategoriesPin(reducedMotion))));
    }

    await Promise.all(jobs);
});
