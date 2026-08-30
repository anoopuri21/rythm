# Phase 12 — Privacy Data Map Baseline

**Status:** Inventory only; retention, export, deletion and legal wording are not approved.
**Date:** 30 August 2026
**Safety:** No customer records, credentials or raw uploaded evidence are included.

## Data categories observed in the schema/code

| Data subject / flow | Data observed | Storage/reference | Current handling evidence | Decision still required |
|---|---|---|---|---|
| Customer identity | Name, email, password hash, email verification timestamp, remember token, role/MFA fields | `users` and `User` model | Password/remember token hidden; password cast hashed; new-user verification remains false by default | Account export/deletion scope and statutory retention |
| Customer contact/address | Name, email where present, phone, address lines, city/state/pincode/country, default flag | `addresses`; checkout/order address snapshots | User-scoped address service; order snapshot preserves fulfillment history | Retention period, erasure/anonymization treatment and access process |
| Commerce history | Order number, user ID, email, status/payment state, amounts, currency, address snapshots, notes, order items | `orders`, `order_items`, payments/refunds/shipments/returns and histories | Owner checks, signed guest read journey, financial reconciliation/audit paths | Financial/legal retention and redaction policy |
| Communications | Contact name/email/phone/subject/message/status; newsletter email/subscription timestamps | `contact_messages`, `newsletter_subscribers` | Validation, honeypots and route throttles; subscriber email unique | Purpose, consent/unsubscribe, retention and deletion process |
| Product interaction | Review/question author IDs and content; wishlist/cart links; stock-alert user/product/variant | Interaction tables and user relations | Authenticated services bind user IDs; stock alerts support owner cancellation | Content moderation/retention and account deletion behavior |
| Notifications | User ID, category/read state, delivery metadata and event references | Notification/notification-delivery tables | User-scoped reads and idempotent delivery services | Delivery-log retention and data-subject handling |
| Staff/audit telemetry | Actor, action, subject, reason, redacted before/after values, HMAC IP hash, user agent, request ID, timestamp | `admin_audit_logs` and `AdminAuditService` | Sensitive key fragments redacted; IP is hashed with app key | Audit retention and lawful access policy |
| Media and source provenance | Product/brand/content images, media metadata, import/source hashes and rights review flags | Media library and catalogue source tables/files | MIME/type/size limits and explicit commercial-use approval field | Copyright/provenance, removal process and storage retention |
| Operational telemetry | Request IDs, logs, job/delivery/payment events and exception class names | Application logs and operational tables | Payment logs avoid payloads; audit service redacts sensitive keys | Log retention, access and deletion/anonymization policy |

## Current privacy boundaries

- No account data export or deletion workflow is enabled by this baseline.
- No retention duration is invented here; legal/business approval is required before implementing destructive or anonymizing behavior.
- No cookie/consent banner is added because actual tracking technologies and approved wording must be established first.
- Credentials, payment secrets, raw customer records and source uploads are not copied to repository documents.
- Order, payment, refund, tax and audit history may have statutory/business retention constraints; they must not be deleted merely because an account is removed.

## Human-gated decisions

1. Exact data-subject export format and authentication/re-authentication requirements.
2. Account deletion versus anonymization behavior for orders, payments, refunds, notifications, reviews, questions and audit logs.
3. Retention periods and legal basis/purpose for each category.
4. Actual analytics/marketing/tracking technologies and any approved consent language.
5. Approved Terms, Privacy, Shipping, Returns, Warranty and Cancellation text.

These decisions must be supplied by the owner/legal professional before the corresponding feature is implemented or published. Until then, unknown rules remain unpublished/disabled.
