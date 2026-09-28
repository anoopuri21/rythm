import 'swiper/css';
import 'swiper/css/a11y';
import 'swiper/css/effect-fade';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

import Swiper from 'swiper';
import { A11y, Autoplay, EffectFade, Keyboard, Navigation, Pagination } from 'swiper/modules';

const commonModules = [A11y, Autoplay, Keyboard, Navigation, Pagination];

/**
 * Swiper writes an inline `width` on every slide when it initialises. If the
 * constructor throws, no inline width is written and Swiper's own base
 * stylesheet (`.swiper-slide { width: 100% }`) takes over — one slide then
 * fills the entire row. Isolate each instance so a single failure can neither
 * cascade into the carousels below it nor fail silently.
 */
function createSwiper(element, options) {
    try {
        return new Swiper(element, options);
    } catch (error) {
        console.warn('[carousels] Swiper init failed on', element?.className || element, error);
        return null;
    }
}

/**
 * Swiper measures its container once, at construction. When the section is
 * laid out before the dynamic chunk's CSS has landed (or is zero-width at that
 * moment) the slides keep the base `width: 100%`. Re-measure after paint and
 * again on window load so the computed widths match the real container.
 */
function remeasureOnSettle(instance) {
    if (!instance) return;
    requestAnimationFrame(() => instance.update());
    window.addEventListener('load', () => instance.update(), { once: true });
}

export function initCarousels(reducedMotion) {
    const hero = document.querySelector('.hero-swiper');
    if (hero) {
        const heroSwiper = createSwiper(hero, {
            modules: [...commonModules, EffectFade],
            loop: true,
            speed: reducedMotion ? 0 : 1100,
            effect: 'fade',
            fadeEffect: { crossFade: true },
            autoplay: reducedMotion ? false : {
                delay: 6500,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            },
            pagination: {
                el: hero.querySelector('.hero-pagination'),
                clickable: true,
            },
            navigation: {
                nextEl: hero.querySelector('.hero-next'),
                prevEl: hero.querySelector('.hero-prev'),
            },
            keyboard: { enabled: true, onlyInViewport: true },
            a11y: { enabled: true },
        });

        const pauseButton = hero.querySelector('.hero-pause');
        const pauseLabel = pauseButton?.querySelector('[data-hero-pause-label]');
        let userPaused = false;

        const renderPauseState = () => {
            pauseButton?.setAttribute('aria-pressed', String(userPaused));
            pauseButton?.setAttribute('aria-label', userPaused ? 'Resume featured collections' : 'Pause featured collections');
            if (pauseLabel) pauseLabel.textContent = userPaused ? 'Play' : 'Pause';
        };

        pauseButton?.addEventListener('click', () => {
            userPaused = !userPaused;
            if (userPaused) heroSwiper?.autoplay?.stop();
            else heroSwiper?.autoplay?.start();
            renderPauseState();
        });

        hero.addEventListener('focusin', () => heroSwiper?.autoplay?.pause());
        hero.addEventListener('focusout', (event) => {
            if (!userPaused && !hero.contains(event.relatedTarget)) heroSwiper?.autoplay?.resume();
        });

        renderPauseState();
    }

    // Popular categories — multi-card carousel with side arrows
    const cats = document.querySelector('.cat-swiper');
    if (cats) {
        const catSwiper = createSwiper(cats, {
            modules: commonModules,
            speed: reducedMotion ? 0 : 600,
            spaceBetween: 14,
            slidesPerView: 2.3,
            watchOverflow: true,
            // Re-measure automatically if the section's box changes without a
            // window resize (lazy CMS block swap, font swap, scrollbar change).
            observer: true,
            observeParents: true,
            navigation: {
                nextEl: '.cat-next',
                prevEl: '.cat-prev',
            },
            keyboard: { enabled: true, onlyInViewport: true },
            a11y: { enabled: true },
            breakpoints: {
                640: { slidesPerView: 3 },
                768: { slidesPerView: 4 },
                1024: { slidesPerView: 5 },
                1400: { slidesPerView: 6 },
            },
        });
        remeasureOnSettle(catSwiper);
    }

    // Popular brands — horizontal logo / monogram slider
    const brands = document.querySelector('.brand-swiper');
    if (brands) {
        const brandRoot = brands.closest('.brand-mm__carousel') || brands.parentElement;
        const brandSwiper = createSwiper(brands, {
            modules: commonModules,
            speed: reducedMotion ? 0 : 650,
            spaceBetween: 12,
            slidesPerView: 2.15,
            watchOverflow: true,
            grabCursor: true,
            loop: brands.querySelectorAll('.swiper-slide').length > 6,
            autoplay: reducedMotion ? false : {
                delay: 4200,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            },
            navigation: {
                nextEl: brandRoot?.querySelector('.brand-next') || '.brand-next',
                prevEl: brandRoot?.querySelector('.brand-prev') || '.brand-prev',
            },
            pagination: {
                el: brandRoot?.querySelector('.brand-mm__pagination') || null,
                clickable: true,
            },
            keyboard: { enabled: true, onlyInViewport: true },
            a11y: { enabled: true },
            breakpoints: {
                480: { slidesPerView: 2.6, spaceBetween: 12 },
                640: { slidesPerView: 3.2, spaceBetween: 14 },
                768: { slidesPerView: 4, spaceBetween: 14 },
                1024: { slidesPerView: 5, spaceBetween: 16 },
                1280: { slidesPerView: 6, spaceBetween: 16 },
                1536: { slidesPerView: 7, spaceBetween: 18 },
            },
        });
        remeasureOnSettle(brandSwiper);
    }

    const testimonials = document.querySelector('.testimonial-swiper');
    if (testimonials) {
        const testimonialSwiper = createSwiper(testimonials, {
            modules: commonModules,
            speed: reducedMotion ? 0 : 750,
            spaceBetween: 18,
            slidesPerView: 1,
            watchOverflow: true,
            autoplay: reducedMotion ? false : {
                delay: 7000,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            },
            pagination: {
                el: testimonials.querySelector('.testimonial-pagination'),
                clickable: true,
            },
            navigation: {
                nextEl: '.testimonial-next',
                prevEl: '.testimonial-prev',
            },
            keyboard: { enabled: true, onlyInViewport: true },
            a11y: { enabled: true },
            breakpoints: {
                768: { slidesPerView: 1.35, spaceBetween: 22 },
                1024: { slidesPerView: 2, spaceBetween: 24 },
            },
        });
        remeasureOnSettle(testimonialSwiper);
    }

    // Products slider (Explore by Category — Bajaao real products)
    const products = document.querySelector('.products-swiper');
    if (products) {
        const productSwiper = createSwiper(products, {
            modules: commonModules,
            loop: true,
            speed: reducedMotion ? 0 : 700,
            slidesPerView: 1.15,
            spaceBetween: 16,
            watchOverflow: true,
            grabCursor: true,
            autoplay: reducedMotion ? false : {
                delay: 4000,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            },
            pagination: {
                el: products.querySelector('.products-pagination'),
                clickable: true,
            },
            navigation: {
                nextEl: '.products-next',
                prevEl: '.products-prev',
            },
            keyboard: { enabled: true, onlyInViewport: true },
            a11y: { enabled: true },
            breakpoints: {
                560: { slidesPerView: 2, spaceBetween: 18 },
                768: { slidesPerView: 2.4, spaceBetween: 20 },
                1024: { slidesPerView: 3, spaceBetween: 22 },
                1280: { slidesPerView: 4, spaceBetween: 24 },
            },
        });
        remeasureOnSettle(productSwiper);
    }
}
