# Performance Budget

**Target:** stable Laravel 12 storefront on shared hosting with 500+ products.  
**Measured build:** 31 August 2026, Vite production build (re-measured after the homepage UI additions; all rows remain inside budget). Previous snapshot: 29 August 2026.

## Browser budgets

| Resource/metric | Budget | Current build evidence |
|---|---:|---|
| Global application JS | ≤ 15 KB gzip | 2.87 KB gzip |
| Carousel JS, only on pages containing `.swiper` | ≤ 35 KB gzip | 29.34 KB gzip |
| Motion JS, homepage only | ≤ 60 KB gzip | 53.27 KB gzip |
| Global CSS | ≤ 30 KB gzip | 27.93 KB gzip |
| Carousel CSS, conditional | ≤ 4 KB gzip | 2.35 KB gzip |
| Product-card image | ≤ 100 KB typical | 480×480 WebP conversion, quality 82 |
| Product-gallery image | ≤ 250 KB typical | max 1200×1200 WebP, quality 84 |
| Hero desktop/mobile | ≤ 350/180 KB typical | max 1920×1080 / 768×1024 WebP |
| CLS | < 0.1 | explicit dimensions/aspect ratios required |
| LCP p75 | < 2.5 s | first hero/product image eager + high priority |
| INP p75 | < 200 ms | checkout has no animation-library payload |

Byte targets for media require runtime samples because source complexity affects encoded size.

## JavaScript loading policy

The global entry contains only shared UI behavior. Vite dynamic imports load:

- Swiper and its CSS only when a `.swiper` element exists;
- GSAP, ScrollTrigger, Lenis and CountUp only on the homepage (`.hero-mm`);
- cinema and category-pin modules only on their matching homepage surfaces.

Footer content no longer depends on animation JavaScript to become visible. Checkout, account, cart, product and admin pages do not download the homepage animation bundle.

## Server budgets

| Surface | Budget |
|---|---:|
| Homepage warm application queries | ≤ 8, with primary homepage payload served from cache |
| Shop page | ≤ 15 queries including facets and pagination |
| Product page | ≤ 15 queries including gallery, FAQs, reviews summary and recommendations |
| Cart/checkout | bounded by displayed line count; no per-line product query |
| Admin list | 50 rows maximum by default; relationships loaded by list query |
| Synchronous HTTP work | target < 500 ms application time excluding external payment handoff |

Budgets must be measured with Laravel Debugbar/Telescope only in a disposable non-production runtime, never enabled publicly.

## Dataset and pagination policy

- Storefront catalogue: 12 products per page.
- Account orders: 10 per page.
- Notifications: 12 per page.
- Homepage collections: explicit limits of 4–10 depending on surface.
- Navigation categories and footer brands are cached and bounded for display.
- Filament resources use framework pagination; do not replace list tables with unbounded `get()` calls.
- Sitemap generation may stream/iterate large future datasets; at the current 500-product target its bounded output remains acceptable and should be revisited above 10,000 URLs.

## Checkout policy

No carousels, GSAP, Lenis, CountUp, cinema or category pin code is loaded on checkout. Financial calculations remain server-side and are not cached across customers. Provider scripts load only on the payment step when configured.

## Regression gates

1. Run `npm run build`; compare gzip output against this table.
2. Test a non-home page and confirm no `motion` or `carousels` request unless its matching DOM exists.
3. Test homepage cold/warm cache queries.
4. Load-test shop filters with 500+ active products.
5. Verify no missing conversion causes broken media; original files are valid fallbacks.
