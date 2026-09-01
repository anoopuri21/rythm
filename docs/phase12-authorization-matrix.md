# Phase 12 — Authorization Matrix Baseline

**Status:** Baseline recorded; route/action qualification remains open.
**Date:** 30 August 2026
**Method:** Static inventory of routes, controllers, Livewire components, Filament resources and service ownership checks.

## Customer/public surface

| Surface | Access boundary | Input/ownership evidence | Phase 12 verification |
|---|---|---|---|
| Homepage, Shop, product, contact page, CMS pages, sitemap and robots | Public | Public reads; dynamic page controller with withheld-page guard | Check public data exposure and CMS slug boundaries |
| Contact and newsletter writes | Public | Form request validation, honeypot, explicit route throttles | Abuse/spam and data-retention review |
| Login, registration and password reset | Guest | Form requests and explicit throttles | Credential-enumeration, session and reset-token review |
| Email verification | Authenticated | Signed verification URL and throttled send/verify routes | Verify notification abuse and default-unverified behavior |
| Cart | Guest or authenticated | Cart service resolves guest/user cart; item mutations verify cart ownership in Livewire and variant/product binding in services | Verify session/cart merge, malformed item IDs and authorization transitions |
| Wishlist | Authenticated | Auth route group and user-scoped service queries; writes accept only active products | Verify no cross-user IDs or inactive product IDs are accepted |
| Account/profile/password/address/stock alerts | Authenticated | Auth route group; address and subscription services check user ownership | Feature tests for every `{address}`/`{subscription}` action |
| Notifications | Authenticated | User relation is constrained; notification ID fetched through user relation | Verify mutation IDs cannot cross users |
| Checkout and signed success page | Authenticated | Auth route group; `CheckoutWizard` verifies selected address ownership before advancing; signed success URL; order ownership check | Verify signature expiry, user binding and sensitive output |
| Orders, invoice, cancel, retry payment | Temporary signed link for read-only guest detail/invoice; cancel/retry mutations require `auth` plus route throttles | `OrderController::authorizeView`, owner checks for mutations, temporary signed lookup | Verify every action separately and confirm owner/runtime behavior |
| Guest order lookup | Public write/read journey | Order number plus email match, temporary signed redirect, throttle | Check enumeration resistance and response uniformity |
| Returns | Authenticated order owner | Owner checks in controller and service; disabled-by-default settings remain | Verify order/item/reason ownership and state transitions |
| Reviews and product Q&A | Authenticated | Livewire components require auth; services bind user/product | Verify verified-purchase/moderation and cross-product IDs |
| Razorpay callback/webhook | Provider callback with no browser CSRF | Signature verification, payment/order matching, event idempotency | Owner/test runtime required; never treat route reachability as acceptance |

## Filament/admin surface

- Panel access is restricted by `User::canAccessPanel()` to staff roles defined by `AdminAccess`.
- MFA is required outside unit tests through `AdminPanelProvider`.
- Strict authorization is enabled on the Filament panel.
- Resources use policy-backed CRUD boundaries; the policy family delegates to permission constants in `AdminAccess`.
- Sensitive order actions visibly and server-side check order/finance permissions, then call domain services that authorize the actor and record audit events.
- Staff creation/editing is separately protected by `STAFF_MANAGE`; the final privileged user cannot be demoted by the model guard.
- Product activation requires catalogue permission and the imported-product activation service authorizes the actor.
- Static inventory reviewed 23 discovered Filament resource classes. Their model permissions are represented in `AdminAccess` or an explicitly registered policy; `AdminAuditLog` is the intentional explicit-policy exception rather than a customer/admin domain write model.
- Remaining work: verify hidden UI actions, bulk actions and relation managers in owner PHP/runtime tests, not only top-level resource methods.

## Livewire action review list

`AddToCart`, `CartBadge`, `CartDrawer`, `CartPage`, `CheckoutWizard`, `ProductQuestionSection`, `ReviewSection`, `ShopIndex`, `WishlistBadge`, `WishlistButton` and `WishlistPage` require action-by-action review. The baseline found authenticated user resolution and product/ownership service boundaries in the main commerce components. `CheckoutWizard::selectAddress` now verifies that the selected address belongs to the authenticated user before changing checkout state, and coupon application has an explicit authentication guard. This is not a substitute for independent runtime tests.

## Acceptance rule

No matrix row is accepted solely from route visibility or hidden buttons. Every state-changing action needs server-side authorization, validated input, CSRF or cryptographic provider verification, an appropriate abuse limit and a focused regression test. Any exception requiring legal, business or production evidence is a human gate.
