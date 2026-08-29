# Auto Mode Execution Plan — Phase 4 Accounts, Cart, Wishlist, Checkout and Orders

**Activated:** 26 August 2026
**Mode:** Autonomous
**Status:** COMPLETE — accepted by Agent 0 on 26 August 2026; Auto Mode paused at phase checkpoint
**Evidence:** `tasks/PHASE_4_COMMERCE_QA.md`
**Primary agents:** Agent 3 (commerce), Agent 4 (payments/data), Agent 9 (QA), Agent 11 (security), Agent 12 (database), Agent 14 (UX)
**Review authority:** Agent 0
**Total chunks:** 5

## Chunk 1 — Commerce Safety and Truthfulness Baseline

- Audit account, address, cart, wishlist, checkout, payment callback, order, tracking and invoice paths against Phase 0A invariants.
- Remove unsupported shipping, warranty, dispatch, refund-timing and payment-provider claims.
- Ensure every displayed shipping/tax/discount/total value comes from persisted or server-calculated data.
- Repair authorization, ownership and signed-link gaps found during review.

## Chunk 2 — Account, Address, Cart and Wishlist Correctness

- Complete address CRUD/default behavior with strict ownership checks and immutable order snapshots.
- Preserve email-verification integrity when account email changes.
- Fix product-variant quantity checks, inactive-item behavior and guest/auth cart merge edge cases.
- Qualify wishlist toggle, removal and stock-aware move-to-cart behavior.

## Chunk 3 — Checkout, Payment and Order Lifecycle

- Add checkout-attempt idempotency so repeated placement cannot create duplicate orders.
- Revalidate cart products, variants, prices, stock, coupons, addresses and totals inside transactions.
- Qualify callback/webhook ownership, replay handling, inventory movement, cancellation, coupon release and refund-pending truthfulness.
- Make guest order lookup and signed invoice journeys functional without exposing another customer's order.

## Chunk 4 — Responsive UX, Accessibility and Automated QA

- Audit account/cart/wishlist/checkout/order views at 320/390/768/1440 source breakpoints.
- Verify labels, errors, focus/status semantics, loading/duplicate-submit protection, noindex policy and truthful empty/failure states.
- Add focused regression tests for every closed finding and ownership boundary.

## Chunk 5 — Independent Phase Gate

- Run targeted suites, full PHP regression, syntax/Pint, Blade compilation, production Vite build, dependency audits and static claim/security scans.
- Record bounded limitations and governance evidence.
- Commit/push each completed code checkpoint to `rhythm-uat`.
- Agent 0 alone may accept Phase 4; deployment remains inactive.

## Phase Gate

Phase 4 completes only when account, cart, wishlist, checkout and order journeys pass functional, authorization, price/inventory integrity, replay/idempotency, accessibility and responsive gates without weakening Phase 0A/Phase 2 invariants. Completion is not production sign-off.
