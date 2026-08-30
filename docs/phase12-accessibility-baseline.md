# Phase 12 — Accessibility Baseline

**Status:** Baseline recorded; Phase 12 accessibility hardening and independent qualification remain open.
**Date:** 30 August 2026

## Accepted prior evidence carried forward

The owner-reported Phase 11 browser gate passed at 1440×900, 768×1024, 390×844 and 360×800 with zero console errors, zero critical/serious axe findings and zero horizontal overflow. That evidence remains a Phase 11 acceptance record; it is not a blanket Phase 12 accessibility audit.

## Phase 12 review surface

- Public layout/navigation, skip links, landmarks, heading order, focus visibility, keyboard reachability and mobile menu behavior.
- Product, Shop/search, filter/sort/pagination, cards, variants, cart and checkout state changes.
- Account, notifications, addresses, orders, stock alerts, returns, login/register/reset and verification forms.
- Contact/newsletter, reviews and moderated product Q&A forms.
- Livewire updates: loading, validation, error, success and focus-announcement behavior.
- Filament staff workflows: keyboard/focus, table actions, form errors, modal labels and MFA screens.
- SEO/accessibility interaction: meaningful titles, labels, alt text, noindex boundaries and no misleading status text.

## Static checks to retain

- Form controls must have associated labels or equivalent accessible names.
- Error and success messages must be programmatically associated with the relevant action/control where applicable.
- Decorative images must not receive misleading alternative text; meaningful product/content images need truthful alt text.
- Interactive elements must remain native buttons/links where possible; no color-only status communication.
- Focus styles, modal dismissal, keyboard order and responsive content must remain usable at all four established viewport sizes.
- Public pages must not expose authenticated account/order content through metadata or navigation.

## Required Phase 12 evidence

1. Re-run the established four-viewport browser pass after any UI changes.
2. Record keyboard-only checks for navigation, forms, filters, cart/checkout and account actions.
3. Run axe or equivalent with critical/serious findings separately reported.
4. Record console errors, overflow, link/heading/label failures and screen-reader-relevant announcements.
5. Keep legal/consent behavior out of scope until approved wording and actual tracking use are known.

**Acceptance rule:** Phase 12 accessibility is not complete from static inspection alone; rendered evidence and independent review are required.
