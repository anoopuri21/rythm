# Phase 12 — Security Threat Model and Baseline

**Status:** Baseline recorded; remediation and runtime qualification remain open.
**Date:** 30 August 2026
**Branch:** `rhythm-uat`
**Method:** Read-only source/config/test inventory. No persistent UAT or production operation was performed.

## Trust boundaries and protected assets

| Boundary | Protected assets | Current controls observed |
|---|---|---|
| Public storefront to application | Product data, contact submissions, newsletter addresses, order lookup | Laravel web middleware, request validation, honeypot fields on contact/newsletter, route throttles on several public writes |
| Authenticated customer to account/order data | Profile, addresses, orders, notifications, wishlist and stock alerts | `auth` route group, user-scoped queries/services, ownership checks, signed order links, noindex account/order pages |
| Browser to payment provider | Payment/order state and callback integrity | Razorpay callback/webhook signature verification, idempotent payment-event handling, callback/webhook CSRF exceptions limited to payment endpoints, rate limits |
| Staff browser to Filament | Catalogue, content, orders, finance, notifications, returns and audit data | Filament authentication, MFA outside tests, strict authorization, permission-backed policies and audit service |
| Application to filesystem/media | Uploaded and published media | Private local disk exists; Filament upload MIME/type, image and size limits; media rights review field for imported product content |
| Application to database/queue/mail | PII, commerce records, jobs and delivery events | Eloquent ownership relationships, encrypted session default, notification/payment reconciliation services and audit redaction |

## Existing control inventory

- `bootstrap/app.php` prepends `SecurityHeaders` to the web group and excludes CSRF only for Razorpay callback/webhook paths.
- `SecurityHeaders` sets `nosniff`, same-origin framing, referrer policy, permissions policy, CSP and conditional HSTS.
- Login, registration, password-reset, contact, newsletter, notification mutations, stock-alert cancellation, return actions, payment endpoints and payment retry have explicit throttling where recorded in `routes/web.php`.
- Customer ownership is enforced in `AccountController`, `AddressService`, `BackInStockSubscriptionService`, `OrderController`, `ReturnRequestController`, `OrderService` and related services.
- Filament uses MFA for non-test runtime, strict authorization and discovered policies/resources.
- `AdminAuditService` hashes IP addresses with the application key and recursively redacts password/token/signature/cookie/OTP/card-like keys before persistence.
- Input boundaries include length/email/password/phone/pincode rules and honeypot rejection for contact/newsletter submissions.
- `npm audit --omit=dev --audit-level=high` returned zero high/critical vulnerabilities in this Arena run. Composer/PHP dependency qualification still requires the disposable external QA copy/owner runtime.
- A filename-only tracked-source secret scan found no matching private-key or live-key filenames/patterns. No secret values were copied into this document.

## Review queue — not completion claims

| ID | Area | Evidence | Initial disposition |
|---|---|---|---|
| SEC12-001 | CSP still requires `unsafe-inline`/`unsafe-eval` for the current inline Alpine/Livewire integration; unused Google/CDN script origins were removed and `frame-ancestors 'self'` was added | `app/Http/Middleware/SecurityHeaders.php` | Continue CSP nonce/strict-dynamic review only with rendered integration evidence; do not remove required inline allowances by inference |
| SEC12-002 | HSTS is emitted only when the request is detected as secure | `SecurityHeaders.php` | Verify trusted proxy/HTTPS production configuration in owner runtime; do not infer production behavior from Arena |
| SEC12-003 | Razorpay callback/webhook bypass CSRF by design | `bootstrap/app.php`, `RazorpayController.php` | Keep exception narrow; independently test signature, unknown order, malformed JSON, replay and conflict paths |
| SEC12-004 | Customer profile/password/address/order-cancel/retry-payment mutations and logout have explicit auth boundaries plus route-specific limits; Livewire/cart actions remain under review | `routes/web.php`, Livewire components | Verify limits in owner runtime and continue reviewing Livewire/cart boundaries |
| SEC12-005 | Filament uploads have MIME/type/size limits, but malware scanning and retention are not established by this baseline | Filament resources and media configuration | Require an explicit hosting/business decision before introducing a scanner or retention policy |
| SEC12-006 | Account export/deletion and retention rules are not yet implemented | Phase 12 runbook and current models | Human/legal gate; do not implement from inference |
| SEC12-007 | Cart/order services needed an explicit product-variant ownership boundary; wishlist writes needed an active-product boundary | `CartService.php`, `OrderService.php`, `WishlistService.php` | Corrected with focused static/feature regression coverage; owner PHP runtime verification remains required |
| SEC12-008 | Checkout address selection must not advance state for another customer’s address; coupon application is an authenticated checkout action | `app/Livewire/CheckoutWizard.php` | Corrected with owner-scoped address lookup, explicit auth guard and focused feature/static coverage; owner PHP runtime verification remains required |

## Required next checks

1. Complete route-by-route and Livewire action authorization matrix.
2. Inspect all state-changing endpoints for validation, CSRF and abuse controls.
3. Run owner-side PHP/Composer checks in isolated runtime and record redacted findings.
4. Use browser/axe checks for Phase 12 accessibility remediation candidates.
5. Resolve or formally gate every review-queue item before Phase 12 acceptance.

**Safety statement:** This baseline is not a penetration-test report and does not declare Phase 12 secure or complete.
