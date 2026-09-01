import { initUi } from './modules/ui';

const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

document.addEventListener('DOMContentLoaded', async () => {
    initUi();

    const jobs = [];

    if (document.querySelector('.swiper')) {
        jobs.push(import('./modules/carousels').then(({ initCarousels }) => initCarousels(reducedMotion)));
    }

    // GSAP, ScrollTrigger, Lenis and CountUp are homepage-only payloads.
    if (document.querySelector('.hero-mm')) {
        jobs.push(import('./modules/motion').then(({ initMotion }) => initMotion(reducedMotion)));
        jobs.push(import('./modules/cinema').then(({ initCinema }) => initCinema(reducedMotion)));
    }

    if (document.querySelector('#categories.pin')) {
        jobs.push(import('./modules/categories-pin').then(({ initCategoriesPin }) => initCategoriesPin(reducedMotion)));
    }

    await Promise.all(jobs);
});
