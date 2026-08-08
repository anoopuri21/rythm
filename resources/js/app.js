import './bootstrap';

import Lenis from 'lenis';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import Swiper from 'swiper';
import { Autoplay, EffectFade, Navigation, Pagination } from 'swiper/modules';
import { CountUp } from 'countup.js';
import 'swiper/css';
import 'swiper/css/effect-fade';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

gsap.registerPlugin(ScrollTrigger);

const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const lenis = new Lenis({
    duration: reducedMotion ? 0 : 1.15,
    easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
    orientation: 'vertical',
    gestureOrientation: 'vertical',
    smoothWheel: !reducedMotion,
    wheelMultiplier: 1,
    touchMultiplier: 2,
});

lenis.on('scroll', ScrollTrigger.update);
gsap.ticker.add((time) => lenis.raf(time * 1000));
gsap.ticker.lagSmoothing(0);
window.gsap = gsap;
window.ScrollTrigger = ScrollTrigger;
window.lenis = lenis;

const updateNavbar = () => {
    const navbar = document.getElementById('navbar');
    if (!navbar) return;
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
};

const initHero = () => {
    if (!document.querySelector('.hero-swiper')) return;
    new Swiper('.hero-swiper', {
        modules: [Autoplay, EffectFade, Navigation, Pagination],
        loop: true,
        speed: 1100,
        effect: 'fade',
        fadeEffect: { crossFade: true },
        autoplay: reducedMotion ? false : { delay: 6500, disableOnInteraction: false },
        pagination: { el: '.hero-pagination', clickable: true },
        navigation: { nextEl: '.hero-next', prevEl: '.hero-prev' },
        keyboard: { enabled: true },
    });
};

const initReveals = () => {
    if (reducedMotion) {
        document.querySelectorAll('.reveal-section').forEach((el) => el.classList.add('is-visible'));
        return;
    }
    ScrollTrigger.batch('.reveal-section', {
        start: 'top 88%',
        once: true,
        onEnter: (elements) => gsap.to(elements, {
            opacity: 1,
            y: 0,
            duration: 0.85,
            stagger: 0.1,
            ease: 'power3.out',
            overwrite: true,
        }),
    });
};

const initCounters = () => {
    const section = document.querySelector('.numbers-section');
    if (!section) return;
    let started = false;
    ScrollTrigger.create({
        trigger: section,
        start: 'top 75%',
        once: true,
        onEnter: () => {
            if (started) return;
            started = true;
            document.querySelectorAll('.stat-counter').forEach((element) => {
                new CountUp(element, Number(element.dataset.count), {
                    duration: reducedMotion ? 0.01 : 2.4,
                    decimalPlaces: Number(element.dataset.decimals || 0),
                    useGrouping: true,
                }).start();
            });
        },
    });
};

const initCountdown = () => {
    document.querySelectorAll('.deal-countdown').forEach((clock) => {
        const storageKey = 'rythme-deal-deadline';
        const duration = Number(clock.dataset.deadlineHours || 72) * 60 * 60 * 1000;
        let deadline = Number(localStorage.getItem(storageKey));
        if (!deadline || deadline <= Date.now()) {
            deadline = Date.now() + duration;
            localStorage.setItem(storageKey, String(deadline));
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
};

document.addEventListener('DOMContentLoaded', () => {
    updateNavbar();
    initHero();
    initReveals();
    initCounters();
    initCountdown();
    window.addEventListener('scroll', updateNavbar, { passive: true });
});
