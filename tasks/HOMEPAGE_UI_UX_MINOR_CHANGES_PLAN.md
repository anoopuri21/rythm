# Homepage Minor UI/UX Changes — Plan First

**Status:** PLAN READY — implementation intentionally held until owner content/privacy choices are confirmed
**Auto Mode:** PAUSED by owner hold
**Branch:** `rhythm-uat`
**Scope:** Homepage/site-shell UI only; no Phase 12 autonomous continuation during this task

## Requested outcome

1. Add a slim top bar containing a phone number, email address and social-media icons.
2. Add a continuously looping offer/deal strip directly after the hero with truthful selective-product discounts from 10% through 50%.
3. Add a bottom-left fixed last-purchase card containing product name, price, user name and short product detail; close it permanently for that browser/user after dismissal and otherwise show it across pages.

## Safety and content rules

- Phone number, email address and social URLs must come from owner-provided values or approved environment/configuration; no contact details or social profiles will be invented.
- Offer percentages must be calculated from existing product `compare_at_price` and `price` data. No fake discount, scarcity or unsupported offer copy will be added. Products outside the 10–50% range will be excluded from the ticker.
- A real customer’s name and purchase details must not be shown as public social proof without explicit consent and an approved privacy basis. The recommended safe implementation is an authenticated customer’s own most recent eligible purchase, shown only to that customer; guests see no personal purchase card.
- The card will exclude cancelled/refunded/failed orders and will not expose order numbers, addresses, phone numbers, email addresses or payment details.
- Account/order data will be loaded only for the authenticated user, preferably through a cached view composer/service. Dismissal will use a versioned browser key scoped to the authenticated user/order context; no sensitive data will be stored in localStorage.
- The close button will be keyboard-accessible, labelled and announceable. The offer ticker will pause on hover/focus and respect `prefers-reduced-motion`.

## Implementation sequence

### Step 1 — Confirm content and privacy inputs

- Confirm exact phone number, support email and social profile URLs/handles.
- Confirm whether “last buy” means the signed-in customer’s own latest eligible order (recommended) or a consented public purchase/social-proof feed.
- Confirm whether card price means the purchased item unit price or the order total.

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

### Step 4 — Authenticated recent-purchase card

- Add a small view composer/service for the global layout and a dedicated `resources/views/components/recent-purchase-card.blade.php`.
- Query only the authenticated user’s latest eligible purchase with the minimum required product/order fields.
- Include product name, approved price field, user display name and product short description.
- Add accessible close control and per-user/order dismissal persistence.
- Do not render the card for guests or when there is no eligible purchase.

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

## Explicit non-goals

- No public real-customer purchase feed without consent.
- No invented phone/email/social URLs, fake offer data, fake countdowns or fake scarcity.
- No account deletion/export, cookie banner or unrelated Phase 12 security changes.
- No production deployment, live payment, persistent destructive operation or Agent 10 activation.
