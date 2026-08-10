/* ═══════════════════════════════════════════════════════════════
   RHYTHM EXPORTS — Cinematic Commerce Prototype (vanilla JS)
   Same content as the live site: logo, menu, Bajaao products…
   ═══════════════════════════════════════════════════════════════ */
'use strict';

/* ─────────────── DATA (same as live site config) ─────────────── */

const CATEGORIES = [
  ['Guitars', '480+ instruments', 'https://www.bajaao.com/cdn/shop/files/FEN-0373152506.jpg?v=1779349747'],
  ['Ukuleles & Violins', '80+ ukuleles', 'https://www.bajaao.com/cdn/shop/files/kala-soprano-ukuleles-kala-makala-mk-s-soprano-ukulele-18300244328609.jpg?v=1686443810'],
  ['Keyboards & Pianos', '210+ instruments', 'https://www.bajaao.com/cdn/shop/files/ROL-FP30XBK.jpg?v=1779349747'],
  ['Studio & Recording', '260+ essentials', 'https://www.bajaao.com/cdn/shop/files/FCR-SCR2I24.jpg?v=1782732174'],
  ['Drums & Percussion', '190+ instruments', 'https://www.bajaao.com/cdn/shop/files/ALE-NITROMAXKIT.jpg?v=1780654577'],
  ['Software & Plugins', '150+ titles', 'https://www.bajaao.com/cdn/shop/files/3k_poster_1024x1024_3c293394-d670-408c-8ee7-63cce91a0f31.jpg?v=1773078135'],
  ['Live Sound', '160+ systems', 'https://www.bajaao.com/cdn/shop/files/Mackie_revised_Website_Banner_1400_x_486.jpg?v=1776311162'],
  ['Indian Instruments', '140+ instruments', 'https://www.bajaao.com/cdn/shop/files/ultimate-guru-other-indian-percussion-taal-sangat-digital-tabla-12538672771.jpg?v=1688490765'],
  ['Wind Instruments', '120+ instruments', 'https://www.bajaao.com/cdn/shop/files/vault-harmonicas-red-vault-ha500-key-c-10-hole-harmonica-29054261919923.jpg?v=1744670088'],
  ['Accessories', '900+ essentials', 'https://www.bajaao.com/cdn/shop/files/ernie-ball-electric-guitar-strings-ernie-ball-2239-super-slinky-rps9-electric-guita.png?v=1744656943'],
];

const PRODUCTS = [
  ['Fender', 'Squier Sonic Stratocaster Electric Guitar', 17999, 21999, 128, 'Best Seller', 'https://www.bajaao.com/cdn/shop/files/FEN-0373152506.jpg?v=1779349747'],
  ['Yamaha', 'Yamaha F310 Dreadnought Acoustic Guitar', 7050, null, 42, null, 'https://www.bajaao.com/cdn/shop/files/yamaha-acoustic-guitars-yamaha-f310-drea.jpg?v=1686104369'],
  ['Kala', 'Kala Makala MK-S Soprano Ukulele', 4999, null, 18, null, 'https://www.bajaao.com/cdn/shop/files/kala-soprano-ukuleles-kala-makala-mk-s-soprano-ukulele-18300244328609.jpg?v=1686443810'],
  ['Roland', 'Roland JUPITER-XM Synthesizer', 181261, null, 25, 'Premium', 'https://www.bajaao.com/cdn/shop/files/roland-synthesizers-roland-jupiter-xm-r.jpg?v=1686047780'],
  ['Akai', 'Akai MPK Mini Play Controller Keyboard', 9375, null, 61, null, 'https://www.bajaao.com/cdn/shop/files/akai-midi-keyboard-controllers-akai-mpk-mini.jpg?v=1685871303'],
  ['Focusrite', 'Focusrite Scarlett 2i2 4th Gen Interface', 26533, null, 33, 'New', 'https://www.bajaao.com/cdn/shop/files/FCR-SCR2I24.jpg?v=1782732174'],
  ['KRK', 'KRK Classic 7 Studio Monitor', 22299, null, 27, null, 'https://www.bajaao.com/cdn/shop/files/krk-studio-monitors-krk-classic-7-active.jpg?v=1686114803'],
  ['Audio-Technica', 'ATH-M20X Studio Headphones', 4197, null, 54, 'Popular', 'https://www.bajaao.com/cdn/shop/files/audio-technica-studio-headphones-audio-technica-ath-m20x.jpg?v=1686079503'],
  ['Audio-Technica', 'ATM510 Cardioid Microphone', 8200, null, 19, null, 'https://www.bajaao.com/cdn/shop/files/audio-technica-handheld-microphones-audio-technica-atm510.jpg?v=1686116553'],
  ['Alesis', 'Alesis Nitro Pro XL Electronic Drum Kit', 90608, null, 38, 'Deal', 'https://www.bajaao.com/cdn/shop/files/alesis-electronic-drum-kits-alesis-nitro-pro.jpg?v=1686116403'],
  ['Roland', 'Roland EC10 El Cajon Hybrid Cajon', 57485, null, 22, null, 'https://www.bajaao.com/cdn/shop/files/roland-cajons-roland-ec10-el-cajon-hybrid.jpg?v=1686113203'],
  ['Ultimate Guru', 'Taal Sangat Digital Tabla', 6200, null, 17, null, 'https://www.bajaao.com/cdn/shop/files/ultimate-guru-other-indian-percussion-taal-sangat-digital-tabla-12538672771.jpg?v=1688490765'],
  ['Hohner', 'Hohner Ocean Star Tremolo Harmonica', 836, null, 45, null, 'https://www.bajaao.com/cdn/shop/files/vault-harmonicas-red-vault-ha500-key-c-10-hole-harmonica-29054261919923.jpg?v=1744670088'],
  ['Ernie Ball', 'Super Slinky RPS9 Electric Guitar Strings', 909, null, 30, null, 'https://www.bajaao.com/cdn/shop/files/ernie-ball-electric-guitar-strings-ernie-ball-2239-super-slinky-rps9-electric-guita.png?v=1744656943'],
  ['Granada', 'Adagio Complete Violin with Bow & Case', 7505, null, 12, 'New', 'https://www.bajaao.com/cdn/shop/files/granada-violins-granada-adagio-complete.jpg?v=1686117803'],
  ['Casio', 'Casio Privia PX-860 Digital Piano', 76937, null, 29, null, 'https://www.bajaao.com/cdn/shop/files/casio-digital-pianos-casio-privia-px-860-88-key-digital-piano-with-piano-stool-12837748867.jpg?v=1686255161'],
];

const HERO_SLIDES = [
  { img: 'https://www.bajaao.com/cdn/shop/files/FEN-0373152506.jpg?v=1779349747', label: 'High quality · Best sellers' },
  { img: 'https://www.bajaao.com/cdn/shop/files/ROL-FP30XBK.jpg?v=1779349747', label: 'High quality · Keys & pianos' },
  { img: 'images/hero3.jpg', label: 'Live stages' },
  { img: 'images/hero4.jpg', label: 'Home studios' },
  { img: 'images/poster.jpg', label: 'The Rhythm Exports standard' },
];

const STORIES = [
  ['Setup', 'Why every Rhythm Exports guitar is stage-ready out of the box', '4 min read', 'How we set up', 'images/story1.jpg'],
  ['Studio', 'Building your first home studio without breaking the bank', '6 min read', 'The budget studio', 'images/story2.jpg'],
  ['Practice', 'A 30-day practice plan that actually sticks', '5 min read', '30 days of progress', 'images/story3.jpg'],
];

const TESTIMONIALS = [
  ['Aarav Mehta', 'Guitarist · Mumbai', 'AM', 'Fender Player II Stratocaster', 'The guitar arrived perfectly set up — low action, spot-on intonation, and packed like it was touring the country. It felt personal, not transactional.'],
  ['Naina Kapoor', 'Singer-songwriter · Delhi', 'NK', 'Studio recording bundle', 'I was building my first home studio and the advice was refreshingly honest. Rhythm Exports helped me spend where it mattered and save where it did not.'],
  ['Rohan Iyer', 'Keys player · Bengaluru', 'RI', 'Yamaha CK61 Stage Keyboard', 'From the first call to delivery, everyone spoke the language of musicians. My keyboard reached Bengaluru ahead of schedule and was ready for rehearsal.'],
  ['Ishita Sen', 'Classical musician · Kolkata', 'IS', 'Professional brass tabla set', 'Finding an authentic tabla set online felt risky. The detailed consultation, careful tuning, and beautiful instrument changed my mind completely.'],
];

const FAQS = [
  ['How long does delivery take across India?', 'Metro cities receive orders in 2–4 working days; most other locations in 4–7 working days. Every order ships with tracking and signature-on-delivery, and shipping is free above ₹999.'],
  ['Can I return an instrument if I change my mind?', 'Yes — you have 7 days from delivery for easy, no-questions returns on unused products in original packaging. Instruments that are played and then returned are covered by our separate 7-day play-trial policy for select guitars and keyboards.'],
  ['Do you offer EMI or easy payment options?', 'Yes — no-cost EMI is available on leading credit cards, and we support UPI, net banking, wallets and cash on delivery for eligible orders. The full list of options appears at checkout.'],
  ['Are instruments set up before shipping?', 'Every guitar, bass, ukulele and keyboard is inspected, tuned and set up by our in-house technicians before dispatch — free of charge. This includes action, intonation and string checks.'],
  ['How do I get expert buying advice?', 'Call or WhatsApp our team of working musicians for honest, product-specific advice — no scripts. We will help you compare models, plan upgrades and even recommend the right cable.'],
];

const UGC = [
  ['@ria.makes.music', 'Tracking vocals on her first studio bundle', 'images/ugc1.jpg'],
  ['@akash.plays', 'Sunday mornings with the CS11 and a window seat', 'images/ugc2.jpg'],
  ['@decks.by.dev', 'Weekend sets on a Rhythm Exports-sourced rig', 'images/ugc3.jpg'],
];


/* ─────────────── MEGA MENU DATA ─────────────── */
const MENU = [
  ['Guitars', 'guitars', CATEGORIES[0][2], ['Acoustic Guitars', 'Electric Guitars', 'Bass Guitars', 'Classical Guitars', 'Guitar Amps', 'Effects & Pedals']],
  ['Ukuleles & Violins', 'ukuleles-violins', CATEGORIES[1][2], ['Soprano Ukuleles', 'Concert Ukuleles', 'Baritone Ukuleles', 'Violins', 'Violas', 'Cellos']],
  ['Keyboards & Pianos', 'keyboards-pianos', CATEGORIES[2][2], ['Digital Pianos', 'Synthesizers', 'Arranger Keyboards', 'MIDI Controllers', 'Stage Pianos']],
  ['Studio & Recording', 'studio-recording', CATEGORIES[3][2], ['Audio Interfaces', 'Studio Monitors', 'Studio Headphones', 'Microphones', 'Studio Bundles', 'Sound Treatment']],
  ['Drums & Percussion', 'drums-percussion', CATEGORIES[4][2], ['Acoustic Drums', 'Electronic Drums', 'Cajons', 'Cymbals', 'Hand Drums', 'Drum Hardware']],
  ['Software & Plugins', 'software-plugins', CATEGORIES[5][2], ['DAW Software', 'Virtual Instruments', 'Plugins & Effects', 'Sample Packs']],
  ['Live Sound', 'live-sound', CATEGORIES[6][2], ['PA Speakers', 'Guitar Amps', 'DJ Controllers', 'DJ Mixers', 'DJ Headphones']],
  ['Indian Instruments', 'indian-instruments', CATEGORIES[7][2], ['Tabla', 'Sitar', 'Harmonium', 'Dholak', 'Other Percussion']],
  ['Wind Instruments', 'wind-instruments', CATEGORIES[8][2], ['Harmonicas', 'Flutes', 'Saxophones', 'Trumpets', 'Clarinets']],
  ['Accessories', 'accessories', CATEGORIES[9][2], ['Guitar Strings', 'Picks & Plectrums', 'Cases & Gig Bags', 'Stands', 'Cables & Tuners']],
];

/* ─────────────── Helpers ─────────────── */
const $ = (s, c = document) => c.querySelector(s);
const $$ = (s, c = document) => [...c.querySelectorAll(s)];
const fmt = (n) => n.toLocaleString('en-IN');

function imgCard(src, alt, cls = '') {
  const d = document.createElement('img');
  d.src = src; d.alt = alt || ''; d.loading = 'lazy'; d.className = cls;
  return d;
}

/* Global image fallback (preview/offline me bhi kuch dikhe) */
document.addEventListener('error', (e) => {
  const t = e.target;
  if (t.tagName === 'IMG') {
    const wrap = t.closest('.pcard__imgwrap, .cat-card, .ugc-card, .hero__slide, .why__frame, .showcase__poster');
    if (wrap) {
      t.style.display = 'none';
      const ph = document.createElement('div');
      ph.textContent = '♪';
      ph.style.cssText = 'position:absolute;inset:0;display:grid;place-items:center;font-size:3rem;color:rgba(0,0,0,.18);background:#f2f2f2;';
      wrap.appendChild(ph);
    }
  }
}, true);

/* ─────────────── Navbar / drawer ─────────────── */
const nav = $('#nav');
const onScrollNav = () => nav.classList.toggle('scrolled', window.scrollY > 10);
window.addEventListener('scroll', onScrollNav, { passive: true });
onScrollNav();

const drawer = $('#drawer');
const overlay = $('#drawer-overlay');
const openDrawer = () => { drawer.classList.add('open'); overlay.classList.add('open'); document.body.style.overflow = 'hidden'; };
const closeDrawer = () => { drawer.classList.remove('open'); overlay.classList.remove('open'); document.body.style.overflow = ''; };
$('.nav__burger').addEventListener('click', openDrawer);
$('.drawer__close').addEventListener('click', closeDrawer);
overlay.addEventListener('click', closeDrawer);
document.addEventListener('keydown', (e) => { if (e.key === 'Escape') { closeDrawer(); closeModal(); } });


/* ─────────────── SHOP MEGA MENU ─────────────── */
const shopItem = $('#shop-item');
const shopBtn = $('#shop-btn');
const megaList = $('#mega-list');
const megaRight = $('#mega-right');
let activeCat = 0;

function renderMegaList() {
  megaList.innerHTML = '';
  MENU.forEach(([name], i) => {
    const b = document.createElement('button');
    b.type = 'button';
    b.className = 'mega__cat' + (i === activeCat ? ' active' : '');
    b.innerHTML = `${name}<svg class="mega__cat-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>`;
    b.addEventListener('click', () => { activeCat = i; renderMegaList(); renderMegaRight(); });
    megaList.appendChild(b);
  });
}
function renderMegaRight() {
  const [name, slug, img, children] = MENU[activeCat];
  megaRight.innerHTML = `
    <div class="mega__right-head">
      <div>
        <p class="mega__label">Shop ${name}</p>
        <h4 class="mega__right-title">${name.replace(' & ', ' <em>&amp;</em> ')}</h4>
      </div>
      <div class="mega__right-thumb"><img src="${img}" alt="${name}" loading="lazy"></div>
    </div>
    <div class="mega__right-list">
      ${children.map(c => `<a href="#categories">${c}</a>`).join('')}
    </div>
    <div class="mega__right-foot">
      <a href="#categories" class="btn btn--red">Explore ${name} <span>→</span></a>
    </div>`;
}
const closeMega = () => shopItem.classList.remove('open');
shopBtn.addEventListener('click', (e) => { e.stopPropagation(); shopItem.classList.toggle('open'); });
shopItem.addEventListener('mouseenter', () => shopItem.classList.add('open'));
shopItem.addEventListener('mouseleave', closeMega);
document.addEventListener('click', (e) => { if (!shopItem.contains(e.target)) closeMega(); });
document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeMega(); });
renderMegaList();
renderMegaRight();

/* ─────────────── Hero slider (crossfade + ken burns) ─────────────── */
const heroSlides = $('#hero-slides');
HERO_SLIDES.forEach((s, i) => {
  const div = document.createElement('div');
  div.className = 'hero__slide' + (i === 0 ? ' active' : '');
  div.appendChild(imgCard(s.img, s.label));
  heroSlides.appendChild(div);
});
let heroIdx = 0;
const heroAll = $$('.hero__slide', heroSlides);
const goHero = (n) => {
  heroAll[heroIdx].classList.remove('active');
  heroIdx = (n + heroAll.length) % heroAll.length;
  heroAll[heroIdx].classList.add('active');
  $('#hero-cur').textContent = String(heroIdx + 1).padStart(2, '0');
};
$('#hero-next').addEventListener('click', () => goHero(heroIdx + 1));
$('#hero-prev').addEventListener('click', () => goHero(heroIdx - 1));
setInterval(() => goHero(heroIdx + 1), 7000);
document.addEventListener('DOMContentLoaded', () => $('.hero').classList.add('loaded'));

/* ─────────────── Pinned horizontal categories ─────────────── */
const pinWrap = $('.pin');
const pinStage = $('.pin__stage');
const pinView = $('#cat-viewport');
const pinTrack = $('#cat-track');
const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)').matches;

function gcard([name, count, img]) {
  const a = document.createElement('a');
  a.href = '#'; a.className = 'gcard';
  a.innerHTML = `
    <div class="gcard__img"><img src="${img}" alt="${name} — real product photo from Bajaao" loading="lazy"></div>
    <div class="gcard__body">
      <h3 class="gcard__name">${name}</h3>
      <p class="gcard__count">${count}</p>
      <span class="gcard__cta">Explore <span>→</span></span>
    </div>`;
  return a;
}
CATEGORIES.forEach(c => pinTrack.appendChild(gcard(c)));
const endCard = document.createElement('a');
endCard.href = '#'; endCard.className = 'gcard gcard--end';
endCard.innerHTML = `
  <div>
    <span class="gcard__end-icon">→</span>
    <h3>View all categories</h3>
    <p>3000+ instruments ready to ship</p>
  </div>`;
pinTrack.appendChild(endCard);

let maxX = 0;
function measurePin() {
  const viewW = pinView.clientWidth;
  const trackW = pinTrack.scrollWidth;
  maxX = Math.max(0, trackW - viewW);
  if (reducedMotion) { pinWrap.style.height = 'auto'; return; }
  pinWrap.style.height = (pinStage.offsetHeight + maxX) + 'px';
  onPinScroll();
}
function onPinScroll() {
  if (reducedMotion) return;
  const top = pinWrap.offsetTop;
  const p = Math.min(1, Math.max(0, (window.scrollY - top) / maxX));
  pinTrack.style.transform = `translate3d(${-p * maxX}px,0,0)`;
  $('#pin-progress').style.width = (p * 100) + '%';
  const idx = Math.min(CATEGORIES.length, Math.ceil(p * CATEGORIES.length));
  $('#pin-count').textContent = String(idx).padStart(2, '0') + ' / ' + CATEGORIES.length;
}
let pinTick = false;
window.addEventListener('scroll', () => {
  if (!pinTick) { pinTick = true; requestAnimationFrame(() => { onPinScroll(); pinTick = false; }); }
}, { passive: true });
function debounce(fn, ms) { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; }
window.addEventListener('resize', debounce(measurePin, 160));
measurePin();

/* ─────────────── Product card renderer ─────────────── */
function pcard([brand, name, price, old, reviews, badge, img]) {
  const art = document.createElement('article');
  art.className = 'pcard';
  art.innerHTML = `
    <div class="pcard__imgwrap">
      ${badge ? `<span class="pcard__badge">${badge}</span>` : ''}
      <button class="pcard__wish" aria-label="Add ${name} to wishlist">♡</button>
      <img src="${img}" alt="${name} — real product photo from Bajaao" loading="lazy">
    </div>
    <div class="pcard__body">
      <p class="pcard__brand">${brand}</p>
      <h3 class="pcard__name">${name}</h3>
      <p class="pcard__rating">★★★★★<span>(${reviews})</span></p>
      <div class="pcard__foot">
        <span class="pcard__price">₹${fmt(price)}</span>
        <button class="pcard__add"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M16 11V7a4 4 0 0 0-8 0v4M5 9h14l1 12H4L5 9z"/></svg> Add</button>
      </div>
    </div>`;
  return art;
}

/* ─────────────── Products carousel ─────────────── */
const track = $('#products-track');
[...PRODUCTS, ...PRODUCTS.slice(0, 2)].forEach((p) => track.appendChild(pcard(p)));
const slides = $$('.pcard', track);
let pos = 0;
const perView = () => window.innerWidth > 1100 ? 4 : window.innerWidth > 860 ? 3 : window.innerWidth > 560 ? 2 : 1;
const gap = 22;
const slideW = () => (track.parentElement.clientWidth - gap * (perView() - 1)) / perView();
function updateCarousel(instant = false) {
  const w = slideW();
  slides.forEach(s => { s.style.flexBasis = w + 'px'; s.style.flexShrink = '0'; s.style.flexGrow = '0'; });
  track.style.transition = instant ? 'none' : 'transform .7s cubic-bezier(.16,1,.3,1)';
  track.style.transform = `translateX(${-pos * (w + gap)}px)`;
  if (!instant) setTimeout(() => { track.style.transition = 'transform .7s cubic-bezier(.16,1,.3,1)'; }, 10);
  renderDots();
}
const dotsWrap = $('#products-dots');
function renderDots() {
  const total = PRODUCTS.length;
  const active = pos % total;
  dotsWrap.innerHTML = '';
  for (let i = 0; i < total; i++) {
    const d = document.createElement('span');
    d.className = 'carousel__dot' + (i === active ? ' active' : '');
    dotsWrap.appendChild(d);
  }
}
let carouselTimer;
function carouselAuto() { clearInterval(carouselTimer); carouselTimer = setInterval(() => nextProduct(), 4500); }
function nextProduct() {
  pos++;
  if (pos >= PRODUCTS.length) { pos = 0; updateCarousel(true); requestAnimationFrame(() => requestAnimationFrame(() => { pos = 1; updateCarousel(); })); }
  else updateCarousel();
  renderDots(); carouselAuto();
}
function prevProduct() {
  pos = pos <= 0 ? PRODUCTS.length - 1 : pos - 1;
  updateCarousel(); renderDots(); carouselAuto();
}
$('#prod-next').addEventListener('click', nextProduct);
$('#prod-prev').addEventListener('click', prevProduct);
updateCarousel(true); carouselAuto();
let rszT; window.addEventListener('resize', () => { clearTimeout(rszT); rszT = setTimeout(() => updateCarousel(true), 200); });

/* ─────────────── Bestsellers tabs ─────────────── */
const bsTabs = $('#bs-tabs');
const bsGrid = $('#bs-grid');
const cats = ['all', 'Guitars', 'Keys', 'Drums', 'Pro Audio'];
const brandCat = (b) => (['Fender', 'Yamaha', 'Kala', 'Granada'].includes(b) ? 'Guitars' : ['Roland', 'Casio', 'Akai'].includes(b) ? 'Keys' : ['Alesis'].includes(b) ? 'Drums' : 'Pro Audio');
bsTabs.innerHTML = cats.map(c => `<button class="tab${c === 'all' ? ' active' : ''}" data-cat="${c}">${c === 'all' ? 'All hits' : c}</button>`).join('');
function renderBS(cat) {
  bsGrid.innerHTML = '';
  const list = cat === 'all' ? PRODUCTS.slice(0, 8) : PRODUCTS.filter(p => brandCat(p[0]) === cat).slice(0, 4);
  list.forEach(p => bsGrid.appendChild(pcard(p)));
}
bsTabs.addEventListener('click', (e) => {
  const btn = e.target.closest('.tab'); if (!btn) return;
  $$('.tab', bsTabs).forEach(t => t.classList.remove('active'));
  btn.classList.add('active');
  renderBS(btn.dataset.cat);
});
renderBS('all');

/* ─────────────── New arrivals ─────────────── */
const arrGrid = $('#arrivals-grid');
[PRODUCTS[0], PRODUCTS[13], PRODUCTS[14]].forEach(p => arrGrid.appendChild(pcard(p)));

/* ─────────────── Stories ─────────────── */
const storiesGrid = $('#stories-grid');
STORIES.forEach(([cat, excerpt, read, title, img]) => {
  const a = document.createElement('article');
  a.className = 'pcard';
  a.innerHTML = `
    <div class="pcard__imgwrap" style="aspect-ratio:4/3">
      <img src="${img}" alt="${title}" loading="lazy" onerror="this.style.display='none'">
    </div>
    <div class="pcard__body">
      <p class="pcard__brand">${cat} · ${read}</p>
      <h3 class="pcard__name">${title}</h3>
      <p style="font-size:.8rem;color:#6b6b6b;margin-top:8px;line-height:1.6">${excerpt}</p>
      <a href="#" class="link-arrow" style="margin-top:14px">Read story <span>→</span></a>
    </div>`;
  storiesGrid.appendChild(a);
});

/* ─────────────── Testimonials carousel ─────────────── */
const tTrack = $('#t-track');
TESTIMONIALS.forEach(([name, role, initials, purchase, quote]) => {
  const div = document.createElement('div');
  div.className = 'tcard';
  div.innerHTML = `
    <p class="tcard__quote">“${quote}”</p>
    <div class="tcard__who">
      <div class="tcard__avatar">${initials}</div>
      <p class="tcard__name">${name}</p>
      <p class="tcard__role">${role}</p>
      <span class="tcard__purchase">${purchase}</span>
    </div>`;
  tTrack.appendChild(div);
});
let tIdx = 0;
const tCount = TESTIMONIALS.length;
const tDots = $('#t-dots');
for (let i = 0; i < tCount; i++) { const d = document.createElement('span'); d.className = 'carousel__dot' + (i === 0 ? ' active' : ''); tDots.appendChild(d); }
function goT(n) {
  tIdx = (n + tCount) % tCount;
  tTrack.style.transform = `translateX(-${tIdx * 100}%)`;
  $$('.carousel__dot', tDots).forEach((d, i) => d.classList.toggle('active', i === tIdx));
}
$('#t-next').addEventListener('click', () => goT(tIdx + 1));
$('#t-prev').addEventListener('click', () => goT(tIdx - 1));
setInterval(() => goT(tIdx + 1), 8000);

/* ─────────────── UGC ─────────────── */
const ugcGrid = $('#ugc-grid');
UGC.forEach(([handle, caption, img]) => {
  const a = document.createElement('a');
  a.href = '#'; a.className = 'ugc-card';
  a.innerHTML = `
    <img src="${img}" alt="${handle} — AI Generated" loading="lazy" onerror="this.style.display='none'">
    <div class="ugc-card__veil"></div>
    <div class="ugc-card__body"><span class="ugc-card__handle">${handle}</span><p class="ugc-card__caption">${caption}</p></div>`;
  ugcGrid.appendChild(a);
});

/* ─────────────── FAQ accordion ─────────────── */
const faqList = $('#faq-list');
FAQS.forEach(([q, a], i) => {
  const div = document.createElement('div');
  div.className = 'faq__item';
  div.innerHTML = `
    <button class="faq__q" aria-expanded="false" aria-controls="faq-a-${i}">
      ${q}<span class="faq__icon">+</span>
    </button>
    <div class="faq__a" id="faq-a-${i}"><p>${a}</p></div>`;
  faqList.appendChild(div);
});
faqList.addEventListener('click', (e) => {
  const btn = e.target.closest('.faq__q'); if (!btn) return;
  const item = btn.parentElement;
  const open = item.classList.contains('open');
  $$('.faq__item.open').forEach(x => { x.classList.remove('open'); $('.faq__a', x).style.maxHeight = '0'; $('.faq__q', x).setAttribute('aria-expanded', 'false'); });
  if (!open) { item.classList.add('open'); const a = $('.faq__a', item); a.style.maxHeight = a.scrollHeight + 'px'; btn.setAttribute('aria-expanded', 'true'); }
});

/* ─────────────── Counters ─────────────── */
const ioCount = new IntersectionObserver((es) => es.forEach(en => {
  if (!en.isIntersecting) return;
  const el = en.target;
  const end = parseFloat(el.dataset.count);
  const dec = parseInt(el.dataset.decimals || 0);
  const suf = el.dataset.suffix || '';
  const dur = 2000; const t0 = performance.now();
  (function tick(t) {
    const p = Math.min(1, (t - t0) / dur);
    const eased = 1 - Math.pow(1 - p, 3);
    el.textContent = (end * eased).toLocaleString('en-IN', { minimumFractionDigits: dec, maximumFractionDigits: dec }) + suf;
    if (p < 1) requestAnimationFrame(tick);
  })(t0);
  ioCount.unobserve(el);
}), { threshold: .6 });
$$('.count').forEach(c => ioCount.observe(c));

/* ─────────────── Countdown ─────────────── */
const deadline = Date.now() + 72 * 3600 * 1000;
setInterval(() => {
  const r = Math.max(0, deadline - Date.now());
  const set = (u, v) => { const el = $(`[data-unit="${u}"]`, $('#deal-timer')); if (el) el.textContent = String(v).padStart(2, '0'); };
  set('days', Math.floor(r / 864e5));
  set('hours', Math.floor(r / 36e5) % 24);
  set('minutes', Math.floor(r / 6e4) % 60);
  set('seconds', Math.floor(r / 1e3) % 60);
}, 1000);

/* ─────────────── Reveal on scroll ─────────────── */
const ioReveal = new IntersectionObserver((es) => es.forEach(en => {
  if (en.isIntersecting) { en.target.classList.add('is-in'); ioReveal.unobserve(en.target); }
}), { threshold: .12, rootMargin: '0px 0px -40px 0px' });
$$('[data-reveal]').forEach(el => ioReveal.observe(el));

/* ─────────────── Parallax (rAF) ─────────────── */
const px = $$('[data-parallax]');
if (px.length && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
  let ticking = false;
  const apply = () => {
    const sy = window.scrollY;
    px.forEach(el => {
      const r = el.getBoundingClientRect();
      if (r.bottom < -100 || r.top > window.innerHeight + 100) return;
      const speed = parseFloat(el.dataset.parallax) || 0.1;
      const offset = (r.top + r.height / 2 - window.innerHeight / 2) * speed;
      el.style.transform = `translateY(${offset}px)`;
    });
    ticking = false;
  };
  window.addEventListener('scroll', () => { if (!ticking) { ticking = true; requestAnimationFrame(apply); } }, { passive: true });
  apply();
}

/* ─────────────── Scroll progress + to-top ─────────────── */
const prog = $('.scroll-progress span');
const toTop = $('#to-top');
window.addEventListener('scroll', () => {
  const h = document.documentElement;
  const p = h.scrollTop / (h.scrollHeight - h.clientHeight);
  prog.style.width = (p * 100) + '%';
  toTop.classList.toggle('show', h.scrollTop > 500);
}, { passive: true });
toTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

/* ─────────────── Video modal ─────────────── */
const modal = $('#video-modal');
const video = $('video', modal);
const openModal = () => { modal.classList.add('open'); modal.setAttribute('aria-hidden', 'false'); video.play().catch(() => {}); };
const closeModal = () => { modal.classList.remove('open'); modal.setAttribute('aria-hidden', 'true'); video.pause(); };
$('#play-video').addEventListener('click', openModal);
$$('[data-close]').forEach(el => el.addEventListener('click', closeModal));
