import 'swiper/css';
import 'swiper/css/a11y';
import 'swiper/css/effect-fade';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

import Swiper from 'swiper';
import { A11y, Autoplay, EffectFade, Keyboard, Navigation, Pagination } from 'swiper/modules';

const commonModules = [A11y, Autoplay, Keyboard, Navigation, Pagination];

export function initCarousels(reducedMotion) {
    const hero = document.querySelector('.hero-swiper');
    if (hero) {
        const heroSwiper = new Swiper(hero, {
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
            if (userPaused) heroSwiper.autoplay?.stop();
            else heroSwiper.autoplay?.start();
            renderPauseState();
        });

        hero.addEventListener('focusin', () => heroSwiper.autoplay?.pause());
        hero.addEventListener('focusout', (event) => {
            if (!userPaused && !hero.contains(event.relatedTarget)) heroSwiper.autoplay?.resume();
        });

        renderPauseState();
    }

    // Popular categories — multi-card carousel with side arrows
    const cats = document.querySelector('.cat-swiper');
    if (cats) {
        new Swiper(cats, {
            modules: commonModules,
            speed: reducedMotion ? 0 : 600,
            spaceBetween: 14,
            slidesPerView: 2.3,
            watchOverflow: true,
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
    }

    const testimonials = document.querySelector('.testimonial-swiper');    if (testimonials) {
        new Swiper(testimonials, {
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
    }

    // Products slider (Explore by Category — Bajaao real products)
    const products = document.querySelector('.products-swiper');
    if (products) {
        new Swiper(products, {
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
    }
}
