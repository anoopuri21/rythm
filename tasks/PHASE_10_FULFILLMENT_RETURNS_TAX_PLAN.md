# Phase 10 — Shipping, Fulfillment, Returns and India Tax Workflow

**Status:** IN PROGRESS — Chunk 1 complete; Chunk 2 implemented with external PHP/MySQL and rendered-workflow qualification pending
**Date:** 29 August 2026
**Accountable:** Agent 0
**Primary:** Agents 15, 3, 4, 6 and 12
**Independent review:** Agents 9 and 11

## Chunk 0 read-only audit

### Existing qualified foundation

- Orders preserve immutable item, shipping-address, billing-address, shipping-fee and tax totals.
- Checkout calculates configured flat shipping and tax rates server-side and records them on the order.
- Paid order processing, inventory movement, cancellation, refund and financial reconciliation foundations are present.
- Order status history, protected customer tracking, guarded Filament order actions and admin audit evidence exist.
- Central idempotent commerce notifications can carry approved fulfillment outcomes.
- Existing public shipping, returns and warranty copy avoids unsupported rates, windows and promises.

### Material gaps

1. No shipment entity, item allocation, manual carrier/AWB/tracking record or shipment event history exists.
2. Partial fulfillment and explicit fulfillment state are not represented independently from the broad order status.
3. No return request/RMA domain, eligibility configuration, reason catalogue or guarded state machine exists.
4. No product-level HSN/tax classification or immutable order-line tax breakdown exists.
5. Existing single tax rate cannot safely claim CGST/SGST/IGST treatment or legal invoice compliance.
6. No shipping zone, PIN-code serviceability or shipping-method configuration exists.
7. No immutable invoice/credit-note numbering contract exists.
8. Fulfillment, support and finance responsibilities need explicit least-privilege policies and audited transitions.

## Locked safety boundary

- Do not invent or publish GST, HSN, shipping, serviceability, return, replacement, warranty or invoice rules.
- Unknown rates, eligibility windows and legal text remain disabled/unpublished until qualified professional approval.
- No carrier integration or external credential is assumed.
- Manual shipment references are operational data, never proof that a carrier accepted or delivered a parcel.
- Return approval does not imply refund success; the Phase 8 durable refund workflow remains authoritative.
- Tax and address snapshots are immutable after order placement; corrections use explicit audited records rather than silent mutation.
- State changes are deny-by-default, validated, idempotent where replayable, and audited with non-sensitive evidence.
- Persistent UAT is never targeted by destructive migrations or tests.

## Delivery chunks

### 10.1 — Durable fulfillment domain

- Add shipment, shipment-item and event records with manual carrier/AWB/tracking fields.
- Add explicit fulfillment states and bounded transition service.
- Enforce allocation limits, replay protection, ownership and database integrity.

### 10.2 — Manual fulfillment operations

- Add least-privilege Filament shipment creation and guarded status transitions.
- Support partial shipment without over-allocation.
- Add customer-safe shipment timeline and approved transactional notifications.

### 10.3 — Return/RMA workflow

- Add configurable-but-disabled eligibility rules, reason management and return requests.
- Add customer-owned requests and support/order-manager review state machine.
- Connect approved refund requests to Phase 8 without conflating return and financial outcomes.

### 10.4 — Tax and invoice framework

- Add optional product HSN/tax classification and immutable order-line tax snapshots.
- Add disabled-by-default state-aware tax configuration structures.
- Add invoice/credit-note identity framework only where numbering rules can remain configurable and unpublished.

### 10.5 — Qualification and professional gate

- Run focused/full tests, MySQL-safe migrations, build, style, authorization and rendered workflow checks.
- Require qualified business/tax approval before enabling or publishing jurisdictional tax, return, warranty, shipping or invoice rules.

## Chunk 0 decision

Agent 0 accepts this bounded plan. Chunk 1 may proceed using isolated QA databases and deterministic manual fulfillment fixtures. Professional business/tax approval remains a human gate; credentials, carrier calls, real financial writes, deployment, Phase 18 and Agent 10 remain disabled.
