# Owner-side Phasewise Execution Runbook

**Language:** Hinglish / simple English  
**Purpose:** Record owner gates and safe manual actions while Agent 0 runs approved work.
**Auto Mode:** ACTIVE by explicit owner command on 30 August 2026.
**Current open work:** Phase 12 — security, privacy, compliance and accessibility hardening.
**Deployment:** Phase 18 and Agent 10 remain inactive.

---

## How to use this runbook

1. Ek phase ke saare **Tasks** complete karein.
2. Us phase ke **Achieve when** checks pass karein.
3. **Evidence pack** banakar Agent 0 ko bhejein.
4. Agent 0 ke acceptance ke baad hi next phase start karein.
5. Agar koi check fail ho, phase ko `BLOCKED`/`QA` samjhein; next phase par jump na karein.
6. Password, API key, database password, private customer data, payment card data ya provider secrets screenshots/logs/chat mein na bhejein. Sirf redacted output bhejein.

### Important current status

Repository evidence ke hisaab se Phases **0, 0A, 0B, 1–10 and 6A** accepted/complete hain. In phases ko dobara karne ki zaroorat nahi hai unless code, database, business rule ya environment change hua ho. Current manual gate Phase 11 hai. Neeche complete historical-to-future sequence diya hai so that every owner-side step traceable rahe.

### Manual owner execution

Owner manual execution hi authoritative rahega. Har command alag se chalayein, output check karein, aur fail hone par next command par jump na karein. Phase 11 ke liye `tests/Feature/PhaseElevenCustomerExperienceTest.php` aur `tests/Feature/AccountTest.php` focused files hain.

Focused failed tests ko dobara dekhne ke liye PHPUnit ka `--filter` use karein:

```bash
php artisan test tests/Feature/PhaseElevenCustomerExperienceTest.php tests/Feature/AccountTest.php --filter="admin_managed_related_rule|account_lists_and_cancels|account_paginates"
```

Failure log save karne ke liye:

```bash
php artisan test tests/Feature/PhaseElevenCustomerExperienceTest.php tests/Feature/AccountTest.php 2>&1 | tee storage/logs/phase11-focused.log
php artisan test 2>&1 | tee storage/logs/php-regression.log
```

`storage/logs/*.log` Git mein commit nahi karna. Share karne se pehle log se credentials, `.env`, PII aur secrets remove karein.

### Windows CMD / Cmder variant

Agar prompt `C:\...>` ya `λ` dikhata hai aur Bash quoting/`tee`/`mkdir -p` kaam nahi karta, to current CMD window mein ye syntax use karein. Pehle ek fresh terminal kholna behtar hai:

```bat
set "DB_CONNECTION=mysql"
set "DB_DATABASE=rhythm_phase11_qa"
php artisan config:clear
php artisan tinker --execute="echo json_encode((array) DB::selectOne('SELECT VERSION() AS server_version, @@version_comment AS version_comment')).PHP_EOL;"
php artisan migrate --force
php artisan migrate:status
php artisan route:list --path=account/stock-alerts
set "DB_CONNECTION="
set "DB_DATABASE="
```

`set` values sirf current CMD window ke liye hain; `.env` modify nahi hoti. PHP tests chalane se pehle variables clear karna zaroori hai, warna tests QA MySQL par ja sakte hain. Log ke liye CMD mein `tee` ki jagah `> file 2>&1` use karein:

```bat
if not exist storage\logs mkdir storage\logs
php artisan test tests\Feature\PhaseElevenCustomerExperienceTest.php --filter=admin_managed_related_rule > storage\logs\phase11-failed-tests.log 2>&1
php artisan test tests\Feature\AccountTest.php --filter=account_lists_and_cancels >> storage\logs\phase11-failed-tests.log 2>&1
php artisan test tests\Feature\AccountTest.php --filter=account_paginates >> storage\logs\phase11-failed-tests.log 2>&1
findstr /N /C:"FAIL" /C:"FAILED" /C:"Tests:" /C:"Failures:" storage\logs\phase11-failed-tests.log
```

### Common safety rules

- Correct branch: `rhythm-uat`.
- Persistent UAT/production database par kabhi `migrate:fresh`, `db:wipe`, destructive seed, `RefreshDatabase` suite ya unapproved import na chalayein.
- Destructive PHP tests ke liye disposable isolated database use karein.
- Persistent project/UAT database par sirf reviewed forward migration, for example `php artisan migrate --force`, chalayein.
- Exact database engine verify karein; MariaDB output MySQL 8 acceptance nahi hai.
- Live Razorpay payment/refund, production deployment, tax/return/legal enablement sirf explicit approval ke baad.
- Guest email collection, marketing opt-in, price-drop/abandoned-cart marketing, SMS/WhatsApp aur persistent search daemon is scope mein nahi hain.

### Common checkout/evidence preflight

Project root mein:

```text
git checkout rhythm-uat
git pull --ff-only origin rhythm-uat
git status --short
git rev-parse HEAD
```

Expected: clean tree, branch `rhythm-uat`, and the SHA noted in the evidence. Exact MySQL identity:

```sql
SELECT VERSION() AS server_version, @@version_comment AS version_comment;
```

Owner evidence mein database name/password/customer PII hide karein. Environment values `.env` ya terminal process mein hi rakhein; repository mein nahi.

### Evidence format for every phase

```text
Phase: <number>
Commit SHA: <40-char SHA>
Environment: <QA/staging; PHP, Laravel, MySQL, Node, browser versions>
Database: <isolated/persistent; exact MySQL result redacted as needed>
Tasks completed: <short list>
Tests/checks: <exact command and counts>
Manual result: PASS/FAIL per item
Known defects: <none or ticket/reference>
Data-safety confirmation: <no destructive persistent-UAT operation>
Owner decision: ACCEPT / BLOCK / NEEDS FIX
```

---

# Phase 1 — Homepage and Shop design specification

**Repository status:** COMPLETE.  
**Goal:** Design direction, reference evidence, responsive contract, accessibility and SEO rules lock karna.

## Tasks

1. Confirm the approved product identity: Rythme / Rhythm Exports, musical-instrument catalogue, Indian INR context.
2. Review the approved Homepage and Shop reference evidence. Reference products, copy, images, trademarks or theme code copy na karein.
3. Confirm the four verification sizes: `1440×900`, `768×1024`, `390×844`, plus `320px` overflow check.
4. Confirm the current logo is retained and the owner-approved Rythme Red direction is `#B20202` with strong/hover red `#930303`.
5. Approve the Homepage order: hero, benefits, categories, arrivals, promotions, advantages, deals, campaigns, recently launched, brands, optional real editorial/newsletter, footer.
6. Approve the Shop structure: shortcuts, results toolbar, filters, product grid, pagination and empty state.
7. Confirm product-card information: name, brand/category, approved rating, current price, valid compare-at price, stock/sale state and accessible actions.
8. Confirm keyboard/focus rules for navigation drawer, carousel, filters, tabs, forms and status updates.
9. Confirm SEO rules: one meaningful H1, canonical URL policy, filtered-search robots policy, pagination, Product JSON-LD and breadcrumb consistency.

## Achieve when

- Reference screenshots/measurements and design specification are recorded.
- Brand, layout, responsive and component decisions are explicit.
- Unsupported claims and third-party content copying are excluded.
- Accessibility and SEO acceptance criteria are written before frontend implementation.

## Evidence to send

- `tasks/PHASE_1_HOMEPAGE_SHOP_DESIGN_SPEC.md`
- `tasks/PHASE_1_REFERENCE_EVIDENCE.md`
- `tasks/PHASE_1_SCREENSHOT_MEASUREMENTS.md`
- Redacted screenshot metadata/dimensions and owner approval of the design direction.

---

# Phase 2 — MySQL schema and domain architecture

**Repository status:** COMPLETE.  
**Goal:** Database/domain foundation ko safe, forward-migratable and concurrency-aware banana.

## Tasks

1. Take a backup/snapshot before any persistent schema change.
2. Create/use a disposable isolated MySQL 8 database for destructive migration and tests.
3. Run the engine identity query and record version/comment.
4. Review migrations, foreign keys, unique keys, indexes, decimal/money fields and state transitions.
5. On the isolated database, run migration forward, targeted rollback, and forward again. Verify schema after each cycle.
6. Verify normalized product attributes, values, category applicability and product/variant assignments.
7. Verify inventory movement ledger, idempotency keys, payment-event identity and order idempotency foundations.
8. Run schema/domain/inventory focused tests, full PHP regression, syntax/style checks, build and dependency audits in disposable QA.
9. On persistent UAT only, after reviewing SQL, run the non-destructive forward migration and `migrate:status`. Never reset it.

## Achieve when

- Isolated migration forward/rollback/forward passes.
- Exact MySQL 8 engine is evidenced.
- Persistent UAT forward migration and `migrate:status` show expected migrations as `Ran` without data reset.
- Foreign-key, uniqueness, money, inventory and replay/idempotency invariants pass.
- No business-dependent shipping/tax/return rule is invented or enabled.

## Evidence to send

- Redacted `SELECT VERSION(), @@version_comment` output.
- Isolated migration logs and exact PHP test/assertion count.
- Persistent UAT `migrate --force` and `migrate:status` output with database identity/password removed.
- Schema diff or migration list; backup confirmation.

---

# Phase 3 — Homepage and Shop frontend qualification

**Repository status:** COMPLETE.  
**Goal:** Approved design ko actual responsive, accessible and truthful UI mein qualify karna.

## Tasks

1. Use an isolated populated fixture for browser checks; persistent UAT ko destructive tests se protect karein.
2. Render Homepage and Shop at `1440×900`, `768×1024`, `390×844`, and `360/320px` narrow-width check.
3. Test both populated and empty/catalogue-preparation states.
4. Test search, no-result, category, brand, price, availability, sale, rating/facet and pagination states.
5. Test header/category drawer, mobile filter drawer, carousels, keyboard focus, Escape and focus restoration.
6. Run axe/WCAG checks, console-error checks, broken-link checks and horizontal-overflow checks.
7. Confirm product images have dimensions and safe local/fallback behavior.
8. Confirm no unsupported shipping, warranty, EMI, trade-in, fake-sales, countdown or synthetic-social-proof claim is visible.
9. Confirm canonical/robots, headings, Product/Website metadata and sitemap behavior.

## Achieve when

- All required viewport renders have no clipping, overlap or horizontal overflow.
- Keyboard/touch actions work and critical/serious axe violations are zero.
- Console/page errors and broken same-site links are zero.
- Empty states are truthful and usable.
- Product cards show current approved data and no unsupported claims.

## Evidence to send

- Viewport table with URL/state/result only; no customer PII.
- Full-page or focused screenshots as needed, with dimensions and hashes.
- Axe, console, overflow and link-check summaries.
- Exact test/build/audit results.

---

# Phase 4 — Accounts, cart, wishlist, checkout and orders

**Repository status:** COMPLETE.  
**Goal:** Customer commerce journey ko authorization, price, stock and replay safety ke saath verify karna.

## Tasks

1. Create disposable verified and unverified test accounts. Do not use real customer data.
2. Test registration, login, logout, password reset, email verification and email-change reverification.
3. Test address create/update/default/delete, including another customer ke address par direct URL/action denial.
4. Test guest cart, login merge, authenticated cart, quantity limits, inactive product/variant and insufficient-stock rejection.
5. Test wishlist add/remove/move-to-cart and out-of-stock/inactive behavior.
6. Test empty cart, valid address, invalid address, coupon states and server-calculated totals.
7. In approved fake/test environment, test checkout retry, refresh, duplicate click and callback/webhook replay.
8. Verify one order, one payment state, one inventory movement, one coupon reservation/release and one notification per successful transition.
9. Test order detail, tracking, invoice, signed success URL, guest lookup and cross-customer access denial.
10. Test eligible cancellation and pending-refund truthfulness; do not claim gateway refund completion unless actually reconciled.
11. Run responsive/accessibility checks at the four project viewport sizes.

## Achieve when

- All account/cart/checkout/order journeys pass with server-derived prices and totals.
- Cross-account access is forbidden and leaves the other customer's records unchanged.
- Repeated submissions cannot duplicate order/payment/stock/coupon effects.
- No fake/live payment mode is exposed in UAT without explicit approval.
- Responsive, keyboard, status/error and noindex checks pass.

## Evidence to send

- Journey matrix and exact focused/full test counts.
- Redacted order/payment/inventory/reconciliation identifiers only if needed.
- Authorization denial results and browser/axe/overflow summary.
- Confirmation that persistent UAT was not reset or destructively tested.

---

# Phase 5 — Verified reviews, moderated product Q&A and coupons

**Repository status:** COMPLETE.  
**Goal:** Approved customer interaction scope ko moderation and abuse controls ke saath qualify karna.

## Tasks

1. Confirm scope: verified-purchase reviews plus moderated product Q&A; blog comments excluded.
2. Create a disposable paid/delivered purchase fixture and test eligible review submission.
3. Test ineligible customer, wrong product, duplicate review and concurrent duplicate submission.
4. Test pending/approved/rejected review moderation and bounded merchant reply.
5. Test Q&A authentication, 10–1,000 character validation, rate limit, pending moderation, approval/answer and escaped output.
6. Test coupon uppercase normalization, fixed/percentage types, min order, expiry, usage limit and maximum discount.
7. Test coupon reservation, checkout repricing, unpaid cancellation release and concurrency.
8. Render review/Q&A/coupon flows at `1440×900`, `768×1024`, `390×844`, `360/320px`; run axe/overflow/console checks.
9. Confirm no synthetic rating/testimonial, unsupported policy claim or raw customer HTML is public.

## Achieve when

- Only eligible verified purchases can review.
- Public content is approved, moderated and safely escaped.
- Coupon totals are server-authoritative and usage cannot exceed limits.
- Abuse/rate-limit/ownership checks pass.
- Four-width accessibility/responsive evidence is clean.

## Evidence to send

- Moderation and authorization matrix.
- Coupon scenario table and exact tests/assertions.
- Browser/axe/overflow/console report.
- Exact MySQL forward-migration evidence if migrations changed.

---

# Phase 6 — Controlled catalogue acquisition and import

**Repository status:** COMPLETE.  
**Goal:** Bounded public-source acquisition, safe normalization, inactive import and review-controlled activation.

## Tasks

1. Resolve source/content/image rights before publication. Public technical access is not commercial rights.
2. Use only documented public pages/JSON; no login bypass, CAPTCHA bypass or scraping restrictions bypass.
3. Run the bounded pilot first, for example:

   ```text
   php artisan catalogue:acquire-pilot --collection=acoustic-guitars --limit=5 --delay=1500 --images=3
   ```

4. Keep raw responses, normalized output and media outside the repository in disposable storage.
5. Inspect request count, pacing, failures, host allowlist, media MIME/dimensions/hash and disk footprint.
6. Test resume/rerun; completed products/media must not be downloaded again unnecessarily.
7. Run import dry-run first. Inspect creates, skips, conflicts, failures and missing media.
8. Use explicit `--commit` only after dry-run review. New imported products/variants must start inactive and zero-stock.
9. Verify provenance/source identity, payload hash, local managed media and no competitor hotlinks.
10. In Filament, review title/copy/category/brand/price/media and enter/verify real stock manually.
11. Activate only through the authorized review action requiring reason and audit trail.
12. Verify unchanged rerun is idempotent and changed owner-managed source/slug records are held as conflicts.
13. Delete disposable acquisition artefacts after compact evidence extraction.

## Achieve when

- Bounded pilot, normalization, media integrity, resume and import tests pass.
- Dry-run is explainable; explicit commit creates only inactive/zero-stock records.
- No source availability is converted into Rythme stock.
- Local media and provenance are valid; no raw dump/media enters Git.
- Owner review/activation, audit reason and idempotent rerun pass.

## Evidence to send

- Redacted pilot/import report with counts, duration, bytes and failure totals.
- Dry-run vs commit report, active/inactive/held totals.
- Filament review/activation screenshots without PII.
- Rights/content approval record; do not paste third-party secrets or private contracts.

---

# Phase 7 — Admin governance, staff RBAC and auditability

**Repository status:** COMPLETE.  
**Goal:** Every staff role ko least privilege, MFA and audit proof ke saath verify karna.

## Tasks

1. Use separate test accounts for Super Admin, Catalogue Manager, Order Manager, Support, Marketing and Finance.
2. Confirm customer account cannot enter `/admin` or any direct Filament URL.
3. For each role, open allowed and forbidden direct URLs; hidden menu inspection alone is not enough.
4. Test product/category/brand/media permissions for Catalogue Manager.
5. Test order/customer/fulfillment permissions for Order Manager and Support.
6. Test content/coupon/newsletter permissions for Marketing.
7. Test finance/refund permissions for Finance; ensure Finance cannot change catalogue/fulfillment.
8. Test required TOTP 2FA, recovery, session timeout and login alerts using non-production accounts.
9. Test sensitive product price/stock, order status, coupon, refund, settings and staff actions require confirmation/reason where required.
10. Verify audit rows include actor, action, bounded before/after, reason and timestamp without secrets/PII leakage.
11. Test bulk-operation limits, soft-delete/restore behavior and protected export/PII rules.
12. Verify final Super Admin cannot be accidentally demoted/deleted.

## Achieve when

- Every role can do only its approved tasks.
- Every denied direct URL/action returns 403 or equivalent safe denial.
- MFA/recovery works and sensitive changes are attributable in audit logs.
- No password, token, payment secret or excessive PII is logged/exported.

## Evidence to send

- Role × resource/action matrix with PASS/FAIL.
- TOTP/recovery test summary; never send QR secret or backup codes.
- Redacted audit examples.
- Exact focused/full test counts and owner admin-UAT result.

---

# Phase 6A — Owner-prioritized catalogue expansion and Homepage/Shop expansion

**Position:** Post-Phase-6 operation, completed before Phase 8. It is not a replacement canonical delivery phase.  
**Goal:** Bounded multi-category catalogue and category-led Homepage rows.

## Tasks

1. Confirm the approved manifest and exact category shortfalls before acquisition.
2. Use at most one/two explicitly selected groups per batch; maximum approximately 20 products per batch.
3. Use the command shape only with disposable output:

   ```text
   php artisan catalogue:acquire-expansion tasks/PHASE_6A_ACQUISITION_MANIFEST.json \
     --group=acoustic-guitars \
     --batch=phase6a-owner-batch \
     --delay=1500 --images=3 \
     --output=<disposable-path>
   ```

4. For each batch, verify manifest handle/source ID, normalized fields, variants, images, MIME/dimensions/hashes, local paths and publication-review flags.
5. Stop if source layout drifts, unexpected volume appears or integrity/media failures occur.
6. Run dry-run import; only then use explicit commit for reviewed batch.
7. Keep imported records inactive/zero-stock until admin review, price/media/content and real-stock verification.
8. Configure Homepage category rows with bounded row count/product count, active categories/products and truthful empty behavior.
9. Test Homepage/Shop at four widths with the larger catalogue, filters, pagination, active variants, current prices and no broken cards.
10. Delete every temporary batch directory after evidence extraction.

## Achieve when

- All selected batches have bounded request/disk use and zero unexplained integrity failures.
- Import is idempotent, source conflicts are held, no hotlinks/raw dumps remain.
- Owner can see/edit/review products in Filament and activation requires real stock/reason.
- Homepage/Shop larger catalogue renders correctly and empty/inactive categories are omitted.

## Evidence to send

- Manifest hash, batch IDs, product/image/failure/review totals and media bytes.
- Dry-run/commit/active/held totals.
- Filament review and Homepage/Shop viewport results.
- Cleanup confirmation: temporary raw/media/test files deleted.

---

# Phase 8 — Payment, refund and financial reconciliation operations

**Repository status:** COMPLETE.  
**Goal:** Razorpay-like payment flow, retries, refunds and reconciliation ko test mode mein safely qualify karna.

## Tasks

1. Confirm Razorpay test mode and a safe staging/QA recipient. Live keys/live bank instrument use na karein.
2. Verify callback and webhook signature handling with real test-mode events where approved.
3. Test expected amount, INR currency, gateway order ownership, payment ID and captured status.
4. Replay valid callback/webhook; verify no duplicate payment/order/inventory/notification.
5. Send malformed signature, unknown order, amount mismatch, currency mismatch, status mismatch and capture-flag mismatch; verify paid state is never granted.
6. Test failed/abandoned payment retry: same order remains one order, new attempt identity is bounded, stock/coupon are not duplicated.
7. Test paid/cancelled/already-refunded retry rejection.
8. Test partial refund and cumulative full refund; total refund must not exceed captured amount.
9. Replay refund request and provider response; verify one durable idempotent refund record/effect.
10. Test Finance-only approval/processing and direct URL denial for all other roles.
11. Run reconciliation report; every order/payment/refund/event must be matched or explicitly explained.
12. Reconcile provider test dashboard and local records. Do not send provider credentials or full provider payloads.

## Achieve when

- Test-mode payment, replay, failure/retry, partial/full refund and reconciliation all pass.
- No duplicate order, charge, stock capture or refund occurs.
- Finance permission and audit reason are enforced.
- Unknown outcomes are reconciled before retry; no blind retry.

## Evidence to send

- Scenario matrix with redacted provider event type/status and local result.
- Reconciliation summary with counts/mismatches, not credentials or sensitive IDs.
- Finance authorization/audit result.
- Confirmation that no live money was charged.

---

# Phase 9 — Central notifications and integration event architecture

**Repository status:** COMPLETE.  
**Goal:** Transactional email ko central, idempotent, redacted and shared-host-safe banana.

## Tasks

1. Use only approved transactional email; SMS/WhatsApp/marketing channels are not enabled.
2. Test order/payment/refund/fulfillment event produces the correct email after durable state commit.
3. Replay the same event and verify one delivery identity, one intended email and no duplicate.
4. Verify delivery ledger status, attempts, sent/failed timestamps and redacted failure metadata.
5. Test failed delivery and bounded retry only after known failed outcome.
6. Test customer notification preferences cannot suppress mandatory transactional messages.
7. Test HTML and plain-text rendering, dedicated mail queue and signed links.
8. In staging, verify SPF/DKIM/DMARC and inbox/spam/plain-text rendering without sending credentials.
9. Test cron-driven bounded queue execution; no unmanaged permanent worker.
10. Run notification reconciliation and confirm no stale/unknown delivery is silently marked sent.

## Achieve when

- Exact-once/idempotent delivery identity passes replay tests.
- Rollback emits no notification.
- Failed delivery can be diagnosed and retried within bound.
- Mail authentication and rendered content pass in staging.
- No secrets, payment signatures or unnecessary PII are in email/log/evidence.

## Evidence to send

- Event × notification × recipient/channel matrix.
- Ledger/replay/retry result with redacted IDs.
- SPF/DKIM/DMARC and HTML/plain-text result.
- Queue/cron run summary.

---

# Phase 10 — Shipping, fulfillment, returns and India tax workflow

**Repository status:** COMPLETE for technical qualification; returns/tax values remain disabled.  
**Goal:** Manual fulfillment/RMA/tax framework ko safe, configurable and truthful rakhna.

## Tasks

1. Confirm no shipping, warranty, return, replacement, tax or invoice/legal value is invented.
2. Use an isolated order fixture and test fulfillment states, shipment creation, shipment items, manual carrier/AWB/tracking fields and partial shipment allocation.
3. Test invalid/duplicate/over-allocation transitions and direct unauthorized status changes.
4. Test customer-visible shipment timeline and notification truthfulness.
5. Test customer-owned return request, support/order-manager review states and ownership denial.
6. Confirm return approval does not equal refund success; Phase 8 refund state remains authoritative.
7. Verify HSN/tax classification and immutable line snapshot structures without enabling unknown tax rates.
8. Verify invoice/credit-note numbering stays configurable/unpublished unless approved rules exist.
9. Run focused/full tests, isolated MySQL migration/status, authorization and rendered fulfillment/RMA/tax workflow checks.
10. Obtain professional business/tax/legal approval before publishing or enabling any jurisdictional policy.

## Achieve when

- Manual fulfillment and RMA state machines are safe and audited.
- Allocation, snapshot, ownership and refund-separation tests pass.
- Returns/tax values remain disabled where approval is absent.
- No legal/tax/shipping/warranty promise is published from an unknown rule.

## Evidence to send

- State transition/authorization matrix.
- Redacted rendered workflow screenshots and exact tests.
- MySQL migration/status output with credentials hidden.
- Professional approval record or explicit confirmation that features remain disabled.

---

# Phase 11 — Customer experience, search and merchandising

**Repository status:** IN PROGRESS — current owner manual gate.  
**Goal:** Search, merchandising, stock alerts, account controls and product UX ko owner runtime par qualify karna.

## Task 11.1 — Checkout and isolate the owner runtime

1. Pull the latest `rhythm-uat` branch.
2. Confirm the checked-out SHA is at least the current Phase 11 candidate recorded in `tasks/PHASE_11_CHUNK_2_STOCK_DELIVERY_AND_CX_QUALIFICATION.md`.
3. Install normal Composer/PHP dependencies in the owner environment or disposable external QA copy. Repository ke andar physical `vendor` directory na banayein.
4. Use a disposable isolated MySQL 8 test database for destructive tests. Persistent UAT ko only reviewed forward migration/status ke liye use karein.
5. Record PHP, Laravel, MySQL, Node, browser and OS versions.

## Task 11.2 — Focused and full PHP qualification

Run in isolated QA:

```text
php artisan test tests/Feature/PhaseElevenCustomerExperienceTest.php tests/Feature/AccountTest.php
php artisan test
```

Pass criteria:

- Search fields: name, SKU, brand, category, normalized attributes.
- Exact-name result ranks ahead of contains result with relevance `120`.
- Inactive products/variants/attribute definitions do not leak.
- Related/complementary/frequently-bought rules remain price-safe and active-product scoped.
- Consent is mandatory; customer email must be verified; stale/foreign/inactive variants are rejected.
- Notification command rejects limits below `1` and above `500`.
- Inactive product/variant and zero-stock records are skipped correctly.
- Repeated handling creates one commerce event, one delivery reservation and one `notified_at` transition.
- Account listing is bounded/paginated; customer can cancel only own request.
- Cross-customer cancellation returns HTTP `403` and leaves the other row pending.

## Task 11.3 — MySQL migration/status and route evidence

Against the disposable MySQL 8 database, use inline environment values so `.env` is not modified and the QA database setting does not leak into the PHP test commands:

```bash
DB_CONNECTION=mysql DB_DATABASE=rhythm_phase11_qa php artisan config:clear
DB_CONNECTION=mysql DB_DATABASE=rhythm_phase11_qa php artisan tinker --execute='$row = DB::selectOne("SELECT VERSION() AS server_version, @@version_comment AS version_comment"); echo json_encode((array) $row), PHP_EOL;'
DB_CONNECTION=mysql DB_DATABASE=rhythm_phase11_qa php artisan migrate --force
DB_CONNECTION=mysql DB_DATABASE=rhythm_phase11_qa php artisan migrate:status
DB_CONNECTION=mysql DB_DATABASE=rhythm_phase11_qa php artisan route:list --path=account/stock-alerts
```

MySQL host/user/password `.env` se liye jaate hain. `DB_DATABASE` ko `export` na karein; inline form use karne se baad ke PHP tests isolated in-memory SQLite par hi rahenge.

Against persistent UAT only after backup and SQL review:

```text
php artisan config:clear
php artisan migrate --force
php artisan migrate:status
```

Do not run full destructive feature tests on persistent UAT.

## Task 11.4 — Bounded worker evidence

1. Use a non-sending mail configuration or notification fake.
2. Run invalid limits: `0`, `501`, and one non-integer value if the CLI supports it; verify exit code `2` and no writes.
3. Create pending requests for an active product, inactive product, active variant with stock, inactive variant with stock and zero-stock target.
4. Run `back-in-stock:notify --limit=<safe value>`.
5. Verify only ready active targets dispatch.
6. Handle the same request/event twice; verify one `commerce_events` row, one `notification_deliveries` row and one `notified_at` transition.
7. Verify unverified/cancelled/notified/stale targets do not dispatch.
8. Confirm no database notification or SMS/WhatsApp channel is created.

## Task 11.5 — Search and realistic catalogue evidence

1. In a disposable database, create/import a catalogue above 500 products with active/inactive products, variants, brands, categories and attributes.
2. Run name, SKU, brand, category, attribute, exact-match, contains-match and bounded typo searches.
3. Verify inactive products, inactive variants and inactive attribute definitions do not leak into results/facets.
4. Verify bounded pagination and no unbounded export/query loop.
5. Record query count, elapsed time, environment and dataset size for representative Shop/search requests.
6. If needed, run `EXPLAIN`/`EXPLAIN ANALYZE` only in the disposable database; do not add persistent search infrastructure or credentials.

## Task 11.6 — Rendered responsive/accessibility/SEO/conversion review

Use a real browser at:

- `1440×900`
- `768×1024`
- `390×844`
- `360×800`

Review product, Shop/search, no-result, out-of-stock, variant, stock-request success/error, Account/Stock alerts and admin merchandising selector surfaces.

Check all of the following:

1. No horizontal overflow, clipping, overlap, unusable controls or broken images.
2. Search and no-result states have a clear recovery path.
3. Product cards use current price and active variant-aware stock state.
4. Complementary/frequently-bought sections hide when empty.
5. Admin selectors search active products and do not preload the catalogue.
6. Account Stock alerts is distinct from marketing preferences; active requests show product/variant and cancellation.
7. Guest sees login path, not an email field; consent copy says one availability email and no marketing.
8. Tabs, variants, forms, cancellation and status/error feedback are keyboard operable and labelled.
9. Product page has one H1, breadcrumbs, canonical link, valid Product JSON-LD and no stale legal promises.
10. Run axe or project accessibility checks: zero critical/serious violations, zero console errors and zero broken same-site links.

## Achieve when

- Focused and full PHP tests pass in isolated QA.
- MySQL 8 migration/status and route evidence pass.
- Worker bounds, skip logic, idempotency and ownership proof pass.
- Realistic >500-catalogue search/facet/pagination evidence is recorded.
- All four rendered viewports pass responsive, keyboard, accessibility, SEO and conversion checks.
- Owner sends the complete Phase 11 evidence pack; Agent 0 reviews before Phase 11 can become `COMPLETE`.

## Evidence to send

Use `tasks/PHASE_11_CHUNK_2_STOCK_DELIVERY_AND_CX_QUALIFICATION.md` as the controlling checklist. Send:

- checked-out SHA and runtime versions;
- focused/full PHP counts;
- isolated MySQL engine/migration/status results;
- route list result;
- worker commands and redacted ledger counts;
- >500 catalogue size, query count and timing table;
- four viewport PASS/FAIL table, axe/console/overflow/link results;
- authorization proof for cross-customer cancellation;
- confirmation that no persistent destructive command was run.

---

# Phase 12 — Security, privacy, compliance and accessibility hardening

**Status:** IN PROGRESS — Auto Mode activated 30 August 2026.
**Goal:** Production-focused OWASP/Laravel security, privacy and legal-readiness review.

## Tasks

1. Perform OWASP Top 10 and Laravel-specific threat review.
2. Review IDOR/authorization for every controller route, policy, Filament resource and Livewire action.
3. Verify validation, CSRF, throttling/rate limits, file upload MIME/size/count and safe storage.
4. Review CSP, Razorpay asset origins, HTTPS, HSTS, secure cookies, trusted proxy and `APP_DEBUG=false`.
5. Scan repository/history/build artifacts for secrets; rotate any exposed secret through the provider, never in Git/chat.
6. Verify bot/spam protection for registration, login, contact, reviews and Q&A.
7. Define PII classification, retention, deletion/anonymization and customer data export behavior.
8. Add/test account export and account deletion only after the owner approves exact retention/legal behavior.
9. Add cookie/privacy consent only for tracking technologies actually used; do not add a fake consent banner.
10. Have owner/legal professional provide approved Terms, Privacy, Shipping, Returns, Warranty and Cancellation text; do not invent wording.
11. Add dependency, secret and security scans to CI.
12. Commission an independent penetration test; resolve or owner-accept every finding.

## Achieve when

- No unresolved critical/high security finding.
- All IDOR/auth/validation/rate-limit/upload tests pass.
- Privacy export/deletion/retention behavior is documented and testable.
- Approved legal text is traceable; unknown rules remain unpublished/disabled.
- Independent penetration-test report has no open critical/high item.

## Evidence to send

- Redacted threat model and authorization matrix.
- Scan summaries, penetration-test report summary and remediation references.
- Privacy data map/retention decision and export/deletion test result.
- Approved policy references; never send secrets or private customer records.

---

# Phase 13 — Performance, scalability and resilience

**Status:** PENDING.  
**Goal:** Realistic load, latency, cache and failure behavior qualify karna.

## Tasks

1. Approve measurable SLOs: uptime, p50/p95 latency, error rate, checkout success and recovery time.
2. Profile Homepage, Shop/search, product, cart, checkout and admin queries at realistic catalogue volume.
3. Record query count, elapsed time, memory, slow queries and query plans.
4. Remove N+1 queries and add indexes only from evidence; re-run regression after each change.
5. Approve Redis/cache/session/queue use only if hosting and owner approve; do not introduce it just to pass a test.
6. Test cache invalidation, cold/warm cache and stampede-safe behavior.
7. Test images, WebP/AVIF variants, lazy loading, frontend bundle size and Core Web Vitals.
8. Run bounded load tests for browsing, search, cart and checkout in isolated/staging environment.
9. Run concurrent inventory/payment tests; verify no oversell or duplicate financial effect.
10. Simulate mail, cache, queue, Razorpay and carrier outage; verify graceful degradation and recovery.

## Achieve when

- Approved SLO/load targets pass with no duplicate order, payment or stock effect.
- Query plans and memory are within agreed shared-host limits.
- External-service failures are bounded, visible and recoverable.
- Cache invalidation and queue behavior are proven, not assumed.

## Evidence to send

- SLO table and load-test environment/data size.
- p50/p95, error rate, query count, memory and Core Web Vitals report.
- Concurrency/outage/recovery results.
- Approved performance decision for any new infrastructure.

---

# Phase 14 — Observability, backups and production operations

**Status:** PENDING.  
**Goal:** Failure visible, diagnosable and recoverable ho.

## Tasks

1. Configure structured logs with request/correlation IDs.
2. Confirm passwords, tokens, signatures, payment data and unnecessary PII are redacted.
3. Configure error monitoring and release tracking without leaking secrets.
4. Add application/database/queue/cache/external-service metrics.
5. Add alerts for 5xx spikes, queue backlog, failed jobs, payment mismatch, refund/notification failure and low stock.
6. Add safe health/uptime endpoints; do not expose secrets or unrestricted database details.
7. Build dashboards for orders, payments, refunds, queue and notification delivery.
8. Configure encrypted database and media backups with off-host retention.
9. Perform a restore drill into isolated database/storage and record duration/table/media checks.
10. Rehearse payment incident, queue failure, DB outage, rollback and secret-compromise runbooks.
11. Define on-call owner, escalation contacts and incident severity levels.

## Achieve when

- A simulated incident is detected, diagnosed, escalated and recovered using the runbook.
- Backup restoration passes in isolation.
- Alerts are actionable and do not contain secrets/PII.
- Operational owner and escalation path are confirmed.

## Evidence to send

- Redacted dashboard/alert screenshots.
- Restore drill report and backup retention policy.
- Incident tabletop timeline and runbook references.
- On-call/escalation record without personal secrets.

---

# Phase 15 — CI/CD and shared-hosting release packaging

**Status:** PENDING.  
**Goal:** Reproducible, secure and rollback-capable staging/release package banana.

## Tasks

1. Define development, isolated QA, staging and production boundaries.
2. Add CI gates: Composer validation/audit, PHP syntax/style/static checks, PHP tests, Blade/build, Node tests, dependency audit and secret scan.
3. Add browser E2E for critical journeys.
4. Build a versioned release artifact from the exact approved commit; exclude `.env`, dumps, logs, `vendor` workspace copy, `node_modules` and temporary media.
5. Review migration SQL, backup prerequisite and rollback plan before staging deploy.
6. Document cPanel PHP version, public path, storage, scheduler and cron-driven bounded queue; do not assume SSH or persistent workers.
7. Store environment secrets outside Git and verify rotation procedure.
8. Prove staging deployment, smoke test and rollback with a disposable/staging database.
9. Verify staging Razorpay webhook and mail test paths.
10. Protect production admin access, DNS/TLS/storage/cron documentation and operator access.

## Achieve when

- Same commit produces reproducible artifact and all CI gates pass.
- Staging deploy, migration, smoke test and rollback are repeatable.
- Shared-host cron/queue/scheduler work without a permanent daemon.
- No secrets or temporary/customer/source artefacts enter the release.

## Evidence to send

- CI run URL/summary, artifact manifest/hash and scan output.
- Staging deploy/rollback record.
- Migration review and backup confirmation.
- cPanel/shared-host operations checklist.

---

# Phase 16 — Full QA, compatibility, UAT and release candidate

**Status:** PENDING.  
**Goal:** Complete system ko launch-quality release candidate ke roop mein validate karna.

## Tasks

1. Run unit, feature, integration, Livewire and policy coverage for every critical path.
2. Run E2E journeys: register/login, search, cart, coupon, checkout, payment, tracking, review/Q&A, stock alerts, fulfillment and refund.
3. Test duplicate submit, slow network, refresh/back during payment and queue delays.
4. Test supported Chrome, Firefox, Safari and Edge versions.
5. Test common Android/iOS viewport/device combinations.
6. Run WCAG 2.2 AA keyboard, focus, labels, errors, contrast and basic screen-reader checks.
7. Validate SEO metadata, Product JSON-LD, canonical, sitemap and robots in staging.
8. Validate transactional email delivery, SPF/DKIM/DMARC and HTML/plain-text rendering.
9. Validate invoice, tax, shipping, return and refund flows with business owners; unknown policy values stay disabled.
10. Execute admin permission, import/media and 500+ catalogue regression.
11. Complete owner UAT and record every PASS/FAIL/defect.
12. Freeze the release candidate only after blocker/critical issues are fixed and high-risk issues are resolved or formally accepted.

## Achieve when

- No blocker/critical defect and no unresolved high-risk defect.
- All supported browsers/devices and required viewport checks pass.
- Owner business UAT sign-off is recorded.
- Exact release candidate SHA is frozen and all evidence points to it.

## Evidence to send

- Master QA matrix with environment and tester/date.
- Browser/device/axe/SEO/email reports.
- Defect register with severity, owner, resolution/acceptance.
- Release candidate SHA and owner UAT sign-off.

---

# Phase 17 — Production-readiness review and sign-off decision

**Status:** PENDING.  
**Goal:** All release gates ko independently reconcile karna; deployment authorize nahi karna.

## Tasks

1. Reconcile Phases 0–16 status, evidence freshness, exact commits and unresolved blockers.
2. Verify functional, payment, inventory, security, privacy/legal, quality, accessibility, performance, reliability, operations, backup and deployment gates.
3. Verify commercial catalog/content/media rights and approved business policy configuration.
4. Verify production secrets exist only in the target secret/environment manager; do not share them with Agent 0/chat.
5. Verify staging release/rollback, backup restore, queue/cron, payment/reconciliation and email operations.
6. Review critical/high defect register and obtain explicit owner decisions for any accepted residual risk.
7. Prepare a go/no-go memo naming exact release SHA, environment, operator, window and rollback decision-maker.
8. Agent 0 records technical readiness decision. Owner separately decides whether to request deployment.

## Achieve when

- Every mandatory release gate is independently green.
- No unresolved critical/high blocker remains without explicit documented owner acceptance.
- Exact release SHA, rollback, backup, operator and support ownership are clear.
- Agent 0 technical sign-off and owner go/no-go decision are recorded.
- Phase 18 is still inactive until a separate explicit deployment command.

## Evidence to send

- Signed/redacted production-readiness matrix.
- Final risk/defect register.
- Backup/restore, CI/CD, staging rollback, payment/email/monitoring summaries.
- Go/no-go memo with no secrets.

---

# Phase 18 — Deployment and stabilization boundary

**Status:** INACTIVE — not authorized by the current owner instruction.  

Phase 18 may not start from this runbook. It requires a separate explicit owner deployment command after Phase 17 acceptance. Only then may the reviewed release checklist cover production backup, migration, artifact publish, smoke test, monitoring and stabilization. Do not send an implicit approval by merely completing Phase 17 evidence.

---

## Current owner action

**Phase 11 is accepted.** The owner issued `ACTIVATE AUTO MODE` on 30 August 2026, so Phase 12 is now active under the autonomous supervisor. Phase 18, deployment and Agent 10 remain inactive.
