# Homepage Minor UI/UX Changes — Plan First

**Status:** IMPLEMENTED — top bar + offer marquee + offer popup remain. **C5 (2026-09-12):** synthetic recent-purchase demo **purged** for production posture (no fabricated social proof).
**Auto Mode:** PAUSED by owner hold
**Branch:** `arena/01a09498-rythm`
**Scope:** Homepage/site-shell UI only; no Phase 12 autonomous continuation during this task

## Requested outcome

1. Add a slim top bar containing a phone number, email address and social-media icons.
2. Add a continuously looping offer/deal strip directly after the hero with truthful selective-product discounts from 10% through 50%.
3. Add a bottom-left fixed last-purchase card containing product name, price, user name and short product detail; close it permanently for that browser/user after dismissal and otherwise show it across pages.
4. Add a homepage-only offer ad pop-up with a close button; after close, suppress it for 24 hours, and keep it visible until the user closes it.

## Safety and content rules

- Phone number, email address and social URLs must come from owner-provided values or approved environment/configuration; no contact details or social profiles will be invented.
- Offer percentages must be calculated from existing product `compare_at_price` and `price` data. No fake discount, scarcity or unsupported offer copy will be added. Products outside the 10–50% range will be excluded from the ticker.
- The offer pop-up will reuse one eligible existing `bestDeals` product and its stored pricing; if no 10–50% eligible offer exists, the pop-up will not render. It is homepage-only, remains open until closed, and a versioned browser timestamp suppresses it for 24 hours after close.
- ~~Synthetic recent-purchase carousel~~ **REMOVED in C5** — production storefront must not show fabricated buyers. Real consented signals may return later behind an admin flag only.
- Offer ticker still pauses on hover/focus and respects `prefers-reduced-motion`.

## Implementation sequence

### Step 1 — Confirm content and privacy inputs

- Owner chose config/environment-driven phone/email/social values; missing values must be hidden rather than replaced with placeholders.
- Owner chose five synthetic front-end demo cards, no Admin control, fade transitions every 10 seconds and display on every page.
- Owner chose the purchased product unit price for the card.
- No further content input is needed for this design pass; the demo label remains mandatory for truthful presentation.

### Step 2 — Site-shell top bar

- Add `resources/views/components/top-bar.blade.php`.
- Render it above the existing navbar in `resources/views/layouts/app.blade.php`.
- Use config-driven values and hide missing optional values rather than displaying placeholders.
- Add responsive layout: compact links on desktop, wrapped/scroll-safe layout on mobile.
- Use embedded inline SVG icons with accessible labels; no external icon dependency.

### Step 3 — Truthful offer loop after hero

- Add `resources/views/home/_offer-marquee.blade.php` immediately after `home._hero` in `resources/views/home/index.blade.php`.
- Reuse the existing bounded `homepage.bestDeals` collection or add a small service-level eligible-offer projection if the view needs one.
- Calculate the discount from the stored prices; show product name and offer percentage without inventing an end date.
- Render a duplicated track for seamless looping, pause on hover/focus, and provide a reduced-motion static presentation.

### Step 3A — Homepage offer pop-up

- Add `resources/views/home/_offer-popup.blade.php` only to the homepage view, not the global layout.
- Select one existing eligible `bestDeals` product using the same truthful 10–50% discount calculation and show its current/compare-at prices.
- Keep the dialog open until the close button is used; Escape is an accessible equivalent close action and backdrop clicks do not dismiss it.
- Store only a close timestamp in a versioned browser key and suppress the pop-up for 24 hours after that timestamp.

### Step 4 — Front-end demo recent-purchase card — **C5 PURGED**

- Component emptied; layout include removed; `initRecentPurchasePreview` removed from `ui.js`.
- Do not reintroduce fabricated purchase names without owner consent + real data source.

### Step 5 — Styling, behavior and responsive QA

- Add namespaced CSS to `resources/css/app.css` using existing design tokens.
- Add only small vanilla-JS behavior in `resources/js/modules/ui.js` if Alpine/localStorage behavior cannot stay component-local.
- Prevent overlap with the scroll-top button, cart drawer, cookie/browser UI and small-screen content.
- Verify 1440×900, 768×1024, 390×844 and 360×800.

### Step 6 — Gates

- Add static automation coverage for placement, truthful discount calculation, config-driven contact values, privacy scoping, close persistence, keyboard label and reduced-motion behavior.
- Run `npm run test:automation` and `npm run build`.
- Owner PHP/runtime/browser checks remain required where applicable.
- Commit and push only after the applicable gates pass; keep Auto Mode paused until this manual task is complete.

### Arena verification snapshot — 31 August 2026

- Targeted homepage contract: **5/5 passed** (`node --test tests/automation/homepage-minor-ui.test.mjs`).
- Front-end production build: **passed** (`npm run build`) after installing the locked npm dependencies; no package files changed.
- Full Node automation: **115/117 passed**. The two failures are existing supervisor assertions that still expect an executing lifecycle while the owner-approved committed state is paused; they are outside this homepage scope and were not changed.
- PHP/Composer, MySQL and rendered browser/accessibility checks are unavailable in Arena and remain owner-side gates; no production-readiness claim is made.

## Explicit non-goals

- No public real-customer purchase feed without consent.
- No invented phone/email/social URLs, fake offer data, fake countdowns or fake scarcity.
- No account deletion/export, cookie banner or unrelated Phase 12 security changes.
- No production deployment, live payment, persistent destructive operation or Agent 10 activation.
