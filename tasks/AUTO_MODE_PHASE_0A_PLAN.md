# Auto Mode Execution Plan — Phase 0A Critical Safety Remediation

**Activated:** 25 August 2026  
**Mode:** Autonomous  
**Status:** COMPLETE — accepted 25 August 2026; Auto Mode paused at phase boundary  
**Total chunks:** 6

## Chunk 1 — Admin Access Lockdown

- Add an explicit customer/admin role boundary without introducing a new package during safety remediation.
- Default every user to `customer` at database level.
- Allow Filament panel access only to `admin` users.
- Ensure seeded administrative account is explicitly assigned `admin`.
- Add tests proving registered customers receive 403 and administrators retain access.

**Autonomous decision:** Use a constrained role string (`customer`, `admin`) for the immediate security boundary. Granular Manager/Support permissions remain Phase 7 scope; allowing partially implemented staff roles now would grant excessive access.

## Chunk 2 — Checkout Price Integrity

- Stop trusting public Livewire discount amounts.
- Revalidate coupon from code and current cart at placement time.
- Recompute subtotal, discount, shipping and tax inside the server-side order transaction.
- Ensure invalid/expired/usage-exhausted coupons cannot create discounted orders.
- Add tampering and stale-coupon tests.

## Chunk 3 — Payment Idempotency

- Add unique gateway identifier/event protections where safe.
- Lock order/payment rows during finalization.
- Make already-paid finalization a no-op.
- Align callback, webhook and Livewire confirmation paths.
- Add duplicate callback/finalization regression tests.

## Chunk 4 — Inventory Integrity

- Decrement variant stock for variant order items and product stock for simple products.
- Make stock updates atomic and tied to first successful payment finalization.
- Prevent repeated decrements.
- Restore the same inventory source during eligible cancellation.
- Add variant, simple-product and replay tests.

## Chunk 5 — Truthful Refund Lifecycle

- Never mark money as refunded merely because an order is cancelled.
- Introduce refund state/records required for gateway-backed processing.
- Keep cancellation and refund completion as separate transitions.
- Implement safe gateway abstraction changes without requiring live credentials.
- Add cancellation/refund-state tests and operational notes.

## Chunk 6 — Regression and Independent Review

- Run targeted tests after every chunk.
- Run full PHP suite, production frontend build and dependency audits.
- Run syntax/style/security scans.
- Review changed transaction and authorization boundaries.
- Update Master Tracker, Phase 0 audit addendum and changelog.

## Phase Gate

Phase 0A completes only when all critical findings are closed, regression tests pass, no new critical/high issue is introduced, and Agent 0 records acceptance evidence.
