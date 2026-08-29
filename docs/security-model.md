# Security model

## Trust boundaries

Rythme treats the browser, all request fields, uploaded files, URLs, and unsigned provider payloads as untrusted. Laravel authentication, policies, server-side order calculations, transactional inventory services, and cryptographically verified Razorpay responses form the application trust boundary. Production secrets exist only in the host environment.

## Identity and authorization

- Customers authenticate separately from staff and cannot access the Filament panel.
- `User::canAccessPanel()` admits only known staff roles. Filament strict authorization and registered model policies deny missing authorization.
- Permissions are centralized in `App\Support\AdminAccess`; policy checks are required for resources and mutating actions.
- Filament app-based MFA is required outside unit tests.
- `super_admin` is the owner/root role. `admin` is a temporary legacy wildcard alias, not a role for new accounts. Migrate each legacy account to the narrowest role after owner review, then remove the alias in a separately approved change.
- The final privileged owner account cannot be demoted by the model guard.

## Input and output

- Form requests and Filament field constraints validate scalar input; domain services re-check transactional invariants.
- Product, page, homepage-section and FAQ rich text is sanitized on every model save using a centralized allowlist. Event attributes, styles, embedded media, scripts, and unsafe URL schemes are removed.
- JSON-LD is decoded and re-encoded with HTML-safe JSON flags. Arbitrary database-backed head scripts are neither editable nor rendered. Controlled application scripts must ship through reviewed source/Vite assets.
- Raw Blade output is allowed only for sanitized rich text or safely encoded JSON-LD.

## Uploads

Admin media fields accept JPEG, PNG, or WebP only, with a 5 MiB per-file ceiling and explicit file counts. Files are assigned to fixed Spatie Media Library collections; request-supplied storage paths are not accepted. Product galleries allow at most 12 files; single-image collections allow one. Operators must remove replaced media through the owning resource so database and storage cleanup remain synchronized.

## Orders and payments

- Checkout totals, inventory, ownership, payment amount and currency are server-derived.
- Order detail routes require ownership, a signed link, or a server-established guest lookup grant.
- Payment callbacks and webhooks are throttled, HMAC/signature checked and deduplicated.
- Only captured provider evidence can mark an order paid. See `payment-security.md`.
- Refund actions require finance permission, a reason, bounded amounts, captured payment evidence, idempotency and reconciliation on uncertain outcomes.

## Platform controls

- CSRF remains enabled except for the two Razorpay POST endpoints, which have mandatory cryptographic verification.
- Sessions use the database driver, HTTP-only cookies, SameSite=Lax and secure cookies when HTTPS is detected. Production must set `APP_ENV=production`, `APP_DEBUG=false`, HTTPS `APP_URL`, `SESSION_SECURE_COOKIE=true`, and a suitable cookie domain.
- Responses set content-type, frame, referrer, permissions, CSP and HTTPS HSTS headers. CSP compatibility currently requires inline allowances; removing these is a future hardening target.
- Contact, newsletter, payment callback and webhook writes are rate-limited.
- Important admin model changes are recorded by `AdminAuditableObserver`; order status/payment/refund records provide domain history.

## Secret handling

The canonical Razorpay keys are `RYTHME_RAZORPAY_KEY_ID`, `RYTHME_RAZORPAY_KEY_SECRET`, and `RYTHME_RAZORPAY_WEBHOOK_SECRET`. Values belong in host environment configuration only. Never place credentials in Git, tickets, prompts, logs, audit reasons, backups stored in webroot, or supervisor state. Rotate a secret immediately if exposure is suspected and clear/rebuild Laravel config cache after rotation.

## Qualification checklist

1. Confirm production environment/cookie settings and HTTPS redirects.
2. Confirm all staff roles and MFA enrollment; remove dormant accounts.
3. Send valid and invalid callback/webhook fixtures; verify invalid signatures never mutate payment.
4. Upload wrong MIME and oversized files; confirm rejection.
5. Save XSS-rich content; confirm executable markup is absent on output.
6. Review audit log and failed payment events.
7. Run PHP/Laravel tests against an isolated Rythme database—not persistent UAT.

Runtime qualification remains blocked until `rythm.test` points to `rhythm_db` rather than the unrelated database documented in the project tracker.