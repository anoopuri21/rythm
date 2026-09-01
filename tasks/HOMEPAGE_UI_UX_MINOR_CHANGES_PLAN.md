# Homepage Minor UI/UX Changes — Plan First

**Status:** IMPLEMENTED — static/build gates complete; owner runtime/browser qualification and two existing hold-state automation expectations remain open
**Auto Mode:** PAUSED by owner hold
**Branch:** `rhythm-uat`
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
- The bottom-left cards are explicitly synthetic front-end demo data, not real customer social proof. Each card will carry a visible `Demo preview` label so fabricated names/purchases cannot be mistaken for production evidence.
- The demo carousel will contain five cards, fade between cards every 10 seconds, run on every page and have no Admin/database control.
- The card will use the purchased product unit price in the design payload; no addresses, phone numbers, email addresses, order numbers or payment details will appear.
- Dismissal will use a versioned browser key with no sensitive data stored in localStorage; after close it will remain hidden until the user clears site storage or the component version changes.
- The close button will be keyboard-accessible, labelled and announceable. The offer ticker will pause on hover/focus and respect `prefers-reduced-motion`. They both remain clearly front-end presentation features.

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

### Step 4 — Front-end demo recent-purchase card

- Add a dedicated `resources/views/components/recent-purchase-card.blade.php` with five synthetic cards containing product name, unit price, demo user name and short detail.
- Include a visible `Demo preview` label; do not query the database, create Admin controls or imply real customer activity.
- Render the component globally from `resources/views/layouts/app.blade.php`.
- Add accessible close control and versioned localStorage dismissal persistence.
- Fade to the next card every 10 seconds; pause the timer on hover/focus and reduce motion for users who request it.

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
