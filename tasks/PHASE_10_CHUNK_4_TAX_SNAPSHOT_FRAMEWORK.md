# Phase 10 Chunk 4 — Optional Tax Classification and Immutable Snapshots

**Status:** IMPLEMENTED — PHP/MySQL and professional tax qualification pending
**Date:** 29 August 2026
**Accountable:** Agent 0
**Primary implementation:** Agents 15, 3, 4 and 12
**Independent review:** Agents 9 and 11

## Delivered

- Added optional product fields for HSN code, internal tax classification, and an approved product-specific rate.
- Added an explicit global tax-calculation switch that defaults to disabled.
- Retained the existing default tax-rate setting as an optional fallback, but it has no checkout effect until the new switch is explicitly enabled.
- Added immutable order-line snapshots for:
  - HSN code;
  - tax classification;
  - applied rate;
  - discounted taxable amount;
  - tax amount;
  - whether configured calculation was enabled;
  - customer-supplied destination region.
- Checkout now computes the order tax total from its line snapshots when configured tax calculation is enabled.
- Order-level discounts are allocated proportionally in integer minor units, with the final line receiving the bounded rounding remainder.
- Product-specific approved rates override the approved global fallback; invalid configured rates outside 0–100 reject checkout rather than being silently clamped.
- Order-line tax snapshot fields reject later Eloquent mutation.
- Added optional classification controls to the catalogue editor and snapshot evidence to the admin order view.
- Added admin audit coverage for product HSN, classification, and rate changes.
- Existing orders remain compatible through nullable/default snapshot columns.

## Safety decisions

- No HSN value, tax class, rate, state rule, place-of-supply rule, exemption, CGST/SGST/IGST split, cess, invoice identity, or credit-note identity is seeded or inferred.
- Tax calculation remains off unless a human explicitly enables professionally approved configuration.
- Product classification can remain blank.
- Destination region is stored only as immutable source evidence; it does not select a jurisdictional treatment.
- The framework does not claim that the existing printable order document is a legally compliant tax invoice.
- Invoice and credit-note numbering remain blocked until professional numbering and correction rules are approved.

## Automated evidence available in this workspace

- `npm run test:automation`: **102/102 passed**, including three dedicated tax safety contracts.
- Checkout PHP coverage now asserts:
  - disabled tax despite a stored rate when the explicit switch is off;
  - enabled aggregate and line-level calculations;
  - HSN/class/rate/taxable/tax/destination snapshots;
  - immutable snapshot rejection.
- `git diff --check`: required before commit.
- Vite build: required before commit.

## Qualification still required

Use only the approved external disposable QA runtime:

1. apply migrations `000007` and `000008` to an isolated restored MySQL 8 database;
2. run `php artisan test --filter=CheckoutTest`;
3. run `php artisan test --filter=ReturnRequestDomainTest`;
4. run `php artisan test --filter=FulfillmentDomainTest`;
5. run the full PHP suite;
6. verify product classification and order snapshot rendering in Filament;
7. obtain professional tax review of schema semantics, discount allocation, rounding, rate precedence, destination evidence, and enablement procedure.

Do not enable tax calculation or publish classification values solely because the software framework exists. Never run destructive tests or migrations against persistent UAT.

## Agent 0 decision

Implementation is bounded and ready for external-runtime and professional qualification. Chunk 4 is not accepted as legally or operationally enabled. Invoice/credit-note identity remains intentionally unimplemented pending approved numbering rules. Phase 10 remains **IN PROGRESS** until all qualification gates pass.
