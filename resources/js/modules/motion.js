import Lenis from 'lenis';
import { CountUp } from 'countup.js';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

function revealContent(reducedMotion) {
    const elements = gsap.utils.toArray('.reveal-section');

    if (reducedMotion) {
        gsap.set(elements, { opacity: 1, y: 0 });
        return;
    }

    ScrollTrigger.batch(elements, {
        start: 'top 88%',
        once: true,
        onEnter: (batch) => gsap.to(batch, {
            opacity: 1,
            y: 0,
            duration: 0.85,
            stagger: 0.1,
            ease: 'power3.out',
            overwrite: true,
        }),
    });

    gsap.utils.toArray('.image-reveal').forEach((frame) => {
        gsap.fromTo(frame,
            { clipPath: 'inset(0 0 100% 0 round 2rem)' },
            {
                clipPath: 'inset(0 0 0% 0 round 2rem)',
                duration: 1.15,
                ease: 'power4.inOut',
                scrollTrigger: { trigger: frame, start: 'top 82%', once: true },
            },
        );
    });
}

function animateCounters(reducedMotion) {
    const section = document.querySelector('.numbers-section');
    if (!section) return;

    ScrollTrigger.create({
        trigger: section,
        start: 'top 75%',
        once: true,
        onEnter: () => {
            document.querySelectorAll('.stat-counter').forEach((element) => {
                const counter = new CountUp(element, Number(element.dataset.count), {
                    duration: reducedMotion ? 0.01 : 2.4,
                    decimalPlaces: Number(element.dataset.decimals || 0),
                    useGrouping: true,
                });
                if (!counter.error) counter.start();
            });
        },
    });
}

function addCinematicScroll(reducedMotion) {
    if (reducedMotion) return;

    gsap.to('.hero-slide-image', {
        yPercent: 10,
        ease: 'none',
        scrollTrigger: {
            trigger: '#hero',
            start: 'top top',
            end: 'bottom top',
            scrub: 0.7,
        },
    });

    gsap.utils.toArray('.parallax-media').forEach((image) => {
        gsap.fromTo(image,
            { scale: 1.08, yPercent: -4 },
            {
                scale: 1.02,
                yPercent: 5,
                ease: 'none',
                scrollTrigger: {
                    trigger: image.parentElement,
                    start: 'top bottom',
                    end: 'bottom top',
                    scrub: 0.8,
                },
            },
        );
    });

    gsap.fromTo('.category-card',
        { y: 45 },
        {
            y: 0,
            stagger: 0.05,
            ease: 'none',
            scrollTrigger: {
                trigger: '#categories',
                start: 'top bottom',
                end: 'center center',
                scrub: 0.6,
            },
        },
    );

    const mediaQuery = gsap.matchMedia();
    mediaQuery.add('(min-width: 1024px)', () => {
        const section = document.querySelector('.why-section');
        const media = document.querySelector('.why-media');
        if (!section || !media) return undefined;

        const pin = ScrollTrigger.create({
            trigger: section,
            start: 'top top+=104',
            end: 'bottom bottom-=80',
            pin: media,
            pinSpacing: false,
            anticipatePin: 1,
        });

        return () => pin.kill();
    });

    const footerWordmark = document.querySelector('#footer > p[aria-hidden="true"]');
    if (footerWordmark) {
        gsap.fromTo(footerWordmark, { yPercent: 35 }, {
            yPercent: 0,
            ease: 'none',
            scrollTrigger: {
                trigger: '#footer',
                start: 'top bottom',
                end: 'bottom bottom',
                scrub: true,
            },
        });
    }
}

function trackPageProgress() {
    const bar = document.querySelector('.scroll-progress span');
    if (!bar) return;

    ScrollTrigger.create({
        start: 0,
        end: 'max',
        onUpdate: ({ progress }) => {
            bar.style.transform = `scaleX(${progress})`;
        },
    });
}

export function initMotion(reducedMotion) {
    const lenis = new Lenis({
        duration: reducedMotion ? 0 : 1.15,
        easing: (time) => Math.min(1, 1.001 - Math.pow(2, -10 * time)),
        orientation: 'vertical',
        gestureOrientation: 'vertical',
        smoothWheel: !reducedMotion,
        wheelMultiplier: 1,
        touchMultiplier: 2,
        anchors: true,
    });

    lenis.on('scroll', ScrollTrigger.update);
    gsap.ticker.add((time) => lenis.raf(time * 1000));
    gsap.ticker.lagSmoothing(0);

    revealContent(reducedMotion);
    animateCounters(reducedMotion);
    addCinematicScroll(reducedMotion);
    trackPageProgress();

    const refresh = () => ScrollTrigger.refresh();
    window.addEventListener('load', refresh, { once: true });
    document.fonts?.ready.then(refresh);

    window.gsap = gsap;
    window.ScrollTrigger = ScrollTrigger;
    window.lenis = lenis;

    return lenis;
}
