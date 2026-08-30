# Phase 10 Qualification — Fulfillment, Returns and Tax Snapshots

**Candidate baseline:** `4a6c498` on `rhythm-uat`
**Status:** TECHNICALLY QUALIFIED — owner-reported external evidence accepted; returns/tax values remain disabled
**Safety:** never run destructive tests or migrations against persistent UAT

## 1. Entry gate

Record these values without copying secrets into evidence:

| Evidence | Required value |
|---|---|
| Commit | exact clean `rhythm-uat` SHA |
| PHP | 8.3 target |
| Laravel | repository-locked version |
| Filament | repository-locked version |
| Database | isolated MySQL 8 restored from a verified backup or disposable fresh QA database |
| Node | supported Vite runtime |
| Mail | safe test transport/recipient |
| Payment | fake/test implementation; no real financial write |
| Tester/date | named owner and timestamp |

Stop if the database is production, persistent UAT without a verified backup, or cannot be positively identified as isolated.

## 2. Safe migration gate

The Phase 10 forward migrations are additive:

- `2026_08_29_000003_create_fulfillment_domain`
- `2026_08_29_000007_create_return_request_domain`
- `2026_08_29_000008_add_optional_tax_classification_snapshots`

Migrations `000004`–`000006` are previously reviewed additive index migrations and may also be pending in the target copy.

Run only after verifying the isolated database:

```text
php artisan about
php artisan migrate:status
php artisan migrate --force
php artisan migrate:status
```

Do **not** use `migrate:fresh`, `db:wipe`, rollback, refresh, or seeders against persistent UAT.

Required evidence:

- [ ] Every pending migration reports `DONE` once.
- [ ] A second `php artisan migrate --force` reports nothing to migrate.
- [ ] Existing order/product/customer counts remain unchanged except intentional test fixtures in the isolated database.
- [ ] New tables and columns use expected MySQL types, foreign keys, unique keys and indexes.

## 3. Focused automated gate

From the isolated external QA copy:

```text
composer validate --strict
composer audit
php artisan optimize:clear
php artisan test tests/Feature/FulfillmentDomainTest.php
php artisan test tests/Feature/ReturnRequestDomainTest.php
php artisan test tests/Feature/CheckoutTest.php
php artisan test tests/Feature/RefundOperationsTest.php tests/Feature/NotificationOperationsTest.php
php artisan test
npm ci --no-audit --no-fund
npm audit --omit=dev
npm run build
npm run test:automation
```

Required evidence:

- [ ] Focused suites pass with no skipped release-critical test.
- [ ] Full PHP suite passes.
- [ ] Node automation passes (current repository expectation: at least 102 tests).
- [ ] Vite build passes and produces a valid manifest.
- [ ] No critical/high dependency finding remains unresolved.

## 4. Fulfillment workflow matrix

Use paid test orders only and manual test carrier data. Manual data is not evidence of carrier acceptance or delivery.

- [ ] Order Manager can create a partial shipment from an eligible paid order.
- [ ] A second parcel can allocate only remaining quantities.
- [ ] Duplicate item rows, zero/negative quantities, foreign-order items and cumulative over-allocation are rejected.
- [ ] Replayed fulfillment identity returns the original shipment; conflicting replay is rejected.
- [ ] Support can inspect shipment records but cannot create or transition them.
- [ ] Catalogue, Marketing and Finance roles cannot mutate fulfillment.
- [ ] `draft → ready → dispatched → delivered` works; pre-dispatch cancellation releases allocation.
- [ ] Dispatch requires carrier identity; tracking URL accepts only HTTP/HTTPS.
- [ ] Direct broad-order shipped/delivered actions disappear after a shipment ledger exists.
- [ ] One parcel delivery does not complete a partially fulfilled order.
- [ ] Full allocation plus all active parcels delivered completes the order once.
- [ ] Shipment events record actor, reason and timestamp.
- [ ] Dispatch/delivery notifications are one-per-parcel-state and safe replay creates no duplicate delivery.

## 5. Customer parcel rendering

Test authenticated owner and temporary signed guest link at 360×800, 390×844, 768×1024 and 1440×900.

- [ ] Parcel cards show allocated item, quantity, state and approved carrier/tracking information.
- [ ] Partial parcels remain understandable and responsive with no horizontal overflow.
- [ ] Internal identity, internal note, staff actor and transition reason are absent.
- [ ] Another authenticated customer receives 403.
- [ ] Tracking URL opens safely in a new tab and contains no unsupported delivery promise.

## 6. Return/RMA disabled-default gate

Before configuring anything:

- [ ] `returns_enabled` resolves to false/`0`.
- [ ] `return_window_days` resolves to `0`.
- [ ] No return reason is seeded or automatically active.
- [ ] Customer order pages expose no return CTA.
- [ ] Direct customer submission fails closed.

Only for workflow testing in the isolated database, enter clearly marked test values and remove/disable them after evidence capture.

## 7. Return/RMA workflow matrix

- [ ] Super Admin can configure test reasons and the test eligibility window.
- [ ] Non-Settings roles cannot configure reasons or enable returns.
- [ ] Only the delivered order owner can submit item quantities.
- [ ] Expired, undelivered, inactive-reason, foreign-order and over-quantity requests are rejected.
- [ ] Request replay is idempotent and conflicting replay is rejected.
- [ ] Customer can cancel only a newly requested return; cancellation releases quantity.
- [ ] Support can perform initial triage but cannot approve, reject, receive or close.
- [ ] Order Manager can approve/reject and record receipt/closure through valid transitions only.
- [ ] Customer surfaces show safe status evidence without staff actors or internal reasons.
- [ ] Logistical approval alone creates no refund.
- [ ] Finance can create one pending Phase 8 refund after approval.
- [ ] Creating the pending refund does not call the provider or report success.
- [ ] Existing Phase 8 controls remain authoritative for processing and reconciliation.

## 8. Tax disabled-default and snapshot matrix

Before enabling configured tax calculation:

- [ ] `tax_rules_enabled` resolves to false/`0`.
- [ ] A stored rate alone does not produce order tax.
- [ ] Tax rate/taxable/destination snapshots indicate disabled state.

Using professionally supplied **test-only** classifications/rates in the isolated database:

- [ ] Product HSN/class/rate fields are optional and audited.
- [ ] Invalid rates outside 0–100 fail closed.
- [ ] Global approved fallback and product-specific override behave as designed.
- [ ] Discount allocation is minor-unit bounded and line tax sums exactly to order tax.
- [ ] HSN, class, rate, taxable amount, tax amount, enabled state and destination region persist on order lines.
- [ ] Later product edits do not alter existing order-line snapshots.
- [ ] Direct Eloquent snapshot mutation is rejected.
- [ ] Existing pre-migration orders remain readable.
- [ ] No output claims CGST, SGST, IGST, cess, exemption, place of supply or legal invoice compliance.

## 9. Professional approval record — human gate

Do not enter values in Git. Store the approval in the owner's controlled business records and record only a non-sensitive reference here.

- [ ] Qualified reviewer identity/role recorded externally.
- [ ] Approved HSN/classification source and scope recorded externally.
- [ ] Approved rates, effective dates and product precedence recorded externally.
- [ ] Discount, shipping, rounding and destination treatment reviewed.
- [ ] Return eligibility, reasons, customer guidance and operational handling approved.
- [ ] Invoice/credit-note numbering and correction rules approved before any identity framework is built.
- [ ] Enablement and rollback owner named.

Approval reference: `PENDING`
Approval date: `PENDING`
Enablement authorized: `NO`

## 10. Exit gate

Phase 10 remains incomplete unless all applicable gates pass:

- [ ] Migrations pass on isolated MySQL 8.
- [ ] Focused and full PHP suites pass.
- [ ] Node/build/dependency gates pass.
- [ ] Role and ownership matrix passes by direct URL and attempted mutation.
- [ ] Mobile/desktop workflows pass without accessibility or console failures.
- [ ] Returns and tax remain disabled after test cleanup unless separately approved for UAT.
- [ ] Professional approval exists for any business/tax value that will be enabled.
- [ ] Agent 9/11 independent review is recorded.
- [ ] Agent 0 accepts the evidence and updates the Master Project Tracker.

This gate cannot authorize Phase 18 or activate Agent 10.
