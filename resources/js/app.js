import './bootstrap';

import Lenis from 'lenis';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

const lenis = new Lenis({
    duration: 1.2,
    easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
    orientation: 'vertical',
    gestureOrientation: 'vertical',
    smoothWheel: true,
    wheelMultiplier: 1,
    touchMultiplier: 2,
    infinite: false,
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
    if (logo) {
        logo.classList.toggle('text-white', !isScrolled);
        logo.classList.toggle('text-gold', isScrolled);
    }
};

document.addEventListener('DOMContentLoaded', () => {
    updateNavbar();
    window.addEventListener('scroll', updateNavbar, { passive: true });
});
