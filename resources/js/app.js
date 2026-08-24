import 'swiper/css';
import 'swiper/css/a11y';
import 'swiper/css/effect-fade';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

import { initCarousels } from './modules/carousels';
import { initMotion } from './modules/motion';
import { initUi } from './modules/ui';
import { initCinema } from './modules/cinema';
import { initCategoriesPin } from './modules/categories-pin';

const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

document.addEventListener('DOMContentLoaded', () => {
    initUi();
    initCarousels(reducedMotion);
    const lenis = initMotion(reducedMotion);
    initCinema(reducedMotion, lenis);
    initCategoriesPin(reducedMotion);
});
