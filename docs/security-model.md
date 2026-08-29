# Security model

## Trust boundaries

Rythme treats the browser, Livewire properties, route parameters, uploaded files, and all third-party callbacks as untrusted. Prices, discounts, tax, shipping, inventory, order ownership, and payment state are resolved again on the server. Razorpay is trusted only after cryptographic verification and semantic correlation to a locally initiated payment.

## Authentication and authorization

- The storefront uses Laravel authentication, email verification where required, CSRF middleware, and owner-scoped queries.
- `/admin` is an authenticated Filament boundary. `User::canAccessPanel()` accepts staff roles only. MFA is required outside tests.
- Filament strict authorization is enabled. Model policies deny operations unless `AdminAccess` grants the required permission.
- Staff access management is separately restricted to `staff.manage`; destructive staff deletion is disabled. MFA reset and role changes require a reason and are audited.
- Order views require the owning account or a short-lived signed guest link. Order cancellation and payment retry require the owning authenticated account.
- Livewire checkout re-resolves account, address, cart, order and payment ownership on the server. Client totals and paid-state claims are never authoritative.

The canonical role matrix is [permissions-matrix.md](permissions-matrix.md).

## Input and output safety

- Controller and Livewire inputs have type, format and length validation; service methods re-check critical invariants.
- Rich HTML is sanitized with Symfony HtmlSanitizer at both read and write boundaries. This also protects output from legacy rows that predate the cast.
- JSON-LD is emitted only from decoded arrays using script-safe JSON encoding. Arbitrary database-managed `<head>` script injection is disabled.
- Blade escapes plain content by default. Raw rendering is limited to sanitized rich text and safe JSON serialization.
- Security headers set `nosniff`, `SAMEORIGIN`, strict referrer policy, a permissions policy and CSP. HSTS is sent on secure requests.

## Uploads

Filament media inputs accept raster images only. SVG and executable formats are not accepted. Rules constrain MIME types, file count and size:

- product gallery: JPEG/PNG/WebP/AVIF, 5 MiB each, at most 12;
- product social image: JPEG/PNG/WebP, 3 MiB, one file;
- hero desktop/mobile: JPEG/PNG/WebP, 8/6 MiB, one each;
- homepage media: JPEG/PNG/WebP, 5 MiB, one file;
- category/brand image: JPEG/PNG/WebP, 2 MiB, one file.

Spatie Media Library owns generated storage paths; user-controlled paths are not accepted. Public media must be served with content-type sniffing disabled. Production should use a non-executable storage mount and must not map PHP execution into `storage/app/public`.

## Abuse controls

Route throttles protect authentication, password reset, verification, contact, newsletter, guest order lookup, cancellation, payment retry, payment callbacks and webhooks. Livewire/service throttles independently protect checkout placement, payment confirmation, coupon probing, questions and reviews because Livewire calls do not traverse the original page route on every action.

## Audit and secrets

Critical and admin-managed model creates, updates and deletes are written to the append-only admin audit ledger. Rich content and personal email fields are represented by SHA-256 fingerprints rather than copied into audit payloads. Secret-like setting values are redacted. Payment receipt storage contains only bounded metadata and hashes, never raw payloads or signatures.

Razorpay credentials are read only from `RAZORPAY_KEY_ID`, `RAZORPAY_KEY_SECRET`, and `RAZORPAY_WEBHOOK_SECRET` through `config/services.php`. They must never be committed, placed in site settings, written to logs, or included in supervisor state.

## Session, CSRF and cookies

Production requirements:

- HTTPS end to end and correctly configured trusted proxies;
- `SESSION_DRIVER=database`, `SESSION_HTTP_ONLY=true`, `SESSION_SECURE_COOKIE=true`, `SESSION_SAME_SITE=lax` (or stricter after checkout compatibility testing), and a narrow cookie domain;
- a unique protected `APP_KEY`, `APP_DEBUG=false`, and short operational session lifetime;
- configuration cache rebuilt after environment changes.

Laravel CSRF protection remains enabled for browser routes and Livewire. Only Razorpay callback/webhook endpoints are excepted because Razorpay cannot possess the application CSRF token; those endpoints instead require provider signatures and local payment correlation. The browser callback is not sufficient by itself: the gateway API must independently report a captured payment.

## Residual operational gates

Static code qualification does not replace runtime security testing. Before production, run PHP tests against an isolated copy, validate policies for every role, test malicious rich text and upload fixtures, and replay signed Razorpay fixtures. The current local host must be switched from the unrelated `maverick_academy` database to `rhythm_db` before any Rythme runtime/database qualification.
