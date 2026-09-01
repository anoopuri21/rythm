# Phase 10 Chunk 3 — Disabled-by-default Return/RMA Workflow

**Status:** IMPLEMENTED — PHP/MySQL and rendered-workflow qualification pending
**Date:** 29 August 2026
**Accountable:** Agent 0
**Primary implementation:** Agents 15, 3, 4, 6 and 12
**Independent review:** Agents 9 and 11

## Delivered

- Added additive `return_reasons`, `return_requests`, `return_request_items`, and `return_request_events` structures.
- Return functionality defaults to disabled with no assumed eligibility window and no seeded reasons.
- Added explicit settings for a professionally approved enablement decision and post-delivery window.
- Added separately managed return reasons; each reason is inactive by default and customer guidance carries an approval warning.
- Added order-owner-only return request submission with bounded quantities, immutable reason snapshot, request identity replay protection, and cumulative over-request prevention.
- Added customer cancellation while a request is still newly requested; cancellation releases item quantity for a future request.
- Added an audited state machine:
  - `requested → under_review → approved → received → closed`
  - bounded rejection from requested/review
  - customer cancellation only from requested
- Support may perform initial triage only. Order managers control approval, rejection, receipt, and closure.
- Added least-privilege Filament return ledgers and configuration screens.
- Added a customer-safe order history showing request number, reason snapshot, item count, and state without internal transition reasons or actor evidence.
- Added explicit connection to Phase 8: finance may create one idempotent **pending** refund only after logistical approval. This operation does not invoke the payment provider and does not represent refund success.
- Added admin audit coverage for return configuration and return state/refund-link changes; durable return events remain the authoritative workflow history.

## Safety decisions

- No return period, reason, warranty, replacement rule, shipping instruction, refund entitlement, or legal promise is seeded or enabled.
- The workflow remains unavailable until both the global switch and a positive approved window are configured and at least one reason is actively published.
- Recorded delivery is required before configured window evaluation.
- Return approval, parcel receipt, pending refund reservation, and provider refund success remain distinct outcomes.
- Refund amounts are explicitly reviewed by finance and still pass through Phase 8 captured-payment and cumulative-refund limits.
- No carrier call, provider refund call, inventory mutation, replacement order, or automatic entitlement is performed.
- Customer pages exclude internal transition reasons, staff actors, idempotency identities, and financial implementation details.

## Automated evidence available in this workspace

- `npm run test:automation`: **99/99 passed**, including three new RMA safety contracts.
- `git diff --check`: passed.
- Focused PHP feature coverage was added in `tests/Feature/ReturnRequestDomainTest.php` for disabled defaults, ownership, eligibility, idempotency, allocation bounds, cancellation, role boundaries, state transitions, customer forms, Filament authorization, and pending-refund separation.

## Qualification still required

The workspace has no PHP/Composer runtime and `vendor` must remain absent. Before Agent 0 acceptance, use the approved external disposable QA runtime to run:

1. forward migration on an isolated restored MySQL 8 database;
2. `php artisan test --filter=ReturnRequestDomainTest`;
3. `php artisan test --filter=FulfillmentDomainTest` (Chunk 2 remains pending);
4. the full PHP suite;
5. rendered customer return submit/cancel checks at mobile and desktop widths;
6. rendered Filament reason configuration, triage, approval, receipt, closure, role denial, and pending-refund checks;
7. verification that defaults expose no customer return CTA and publish no return rule.

Never run destructive tests or migrations against persistent UAT.

## Agent 0 decision

Implementation is bounded and ready for external-runtime qualification. Chunk 3 is not accepted until focused/full PHP, migration, authorization, and rendered-workflow evidence passes. Phase 10 remains **IN PROGRESS**. Tax and invoice work must not invent HSN mappings, rates, state treatment, or numbering rules.
