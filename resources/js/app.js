import './bootstrap';

import 'swiper/css';
import 'swiper/css/a11y';
import 'swiper/css/effect-fade';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

import { initCarousels } from './modules/carousels';
import { initMotion } from './modules/motion';
import { initUi } from './modules/ui';

const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

document.addEventListener('DOMContentLoaded', () => {
    initUi();
    initCarousels(reducedMotion);
    initMotion(reducedMotion);
});
