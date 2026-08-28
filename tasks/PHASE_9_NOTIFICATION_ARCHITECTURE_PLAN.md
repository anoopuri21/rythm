# Phase 9 — Notification and Integration Event Architecture

**Status:** IN PROGRESS — Chunks 0–1 complete; Chunk 2 next
**Date:** 29 August 2026
**Accountable:** Agent 0
**Primary:** Agents 14, 3, 4 and 8
**Independent review:** Agents 9 and 11

## Chunk 0 read-only audit

### Existing qualified foundation

- Order confirmation and shipped/delivered/cancelled email templates exist.
- Commerce services queue mail instead of blocking HTTP requests.
- Checkout/payment/order transitions are transaction-safe and already tested for duplicate finalization.
- Shared hosting has a bounded scheduled queue worker using `--stop-when-empty`, `--max-time`, retries, timeout and overlap protection.
- Laravel database queue and failed-job foundations exist.
- Customer accounts and protected account navigation provide an integration point for a notification center.

### Material gaps

1. Mail dispatch is embedded directly in `OrderService`; there is no central event contract or idempotent listener boundary.
2. Payment failure/retry and refund requested/completed/failed events have no customer or finance notification.
3. There is no durable notification delivery identity/status log, so duplicate suppression and support diagnosis are unavailable.
4. There is no customer database notification center, unread state or pagination.
5. There are no customer preferences separating mandatory transactional messages from optional updates.
6. `OrderStatusMail` is not explicitly routed to the dedicated email queue and templates have no plain-text fallback contract.
7. Failed notification retry is only generic queue behavior; there is no bounded domain retry/reconciliation command.
8. Admin delivery visibility, recipient-safe metadata and operational failure evidence are absent.
9. SMS/WhatsApp providers and marketing consent are unapproved and must not be inferred.

## Locked safety rules

- Mandatory transactional notifications cannot be disabled by customer preference.
- Optional notifications require explicit preference and must remain separate from marketing consent.
- No notification includes secrets, payment signatures, full provider payloads or unnecessary address/phone data.
- Every delivery uses a deterministic event/channel/recipient identity to prevent duplicates.
- Dispatch occurs after durable commerce state; rolled-back transactions emit nothing.
- Retry is bounded and only for failed deliveries whose prior outcome is known.
- Shared hosting uses short cron-driven queue drains; no persistent worker is assumed.
- Email-provider credentials stay environment-only. SMS/WhatsApp remain excluded until separately approved.

## Delivery chunks

### 9.1 — Durable notification domain

- Add immutable commerce-event identity and per-recipient/channel delivery records.
- Add database notification storage and minimal transactional preference structure.
- Enforce unique duplicate-suppression keys, bounded status/failure metadata and safe indexes.
- Add model, migration, authorization and idempotency tests.

### 9.2 — Central commerce events and listeners

- Replace direct order mail triggers with after-commit event/listener dispatch.
- Cover order confirmation/status, payment success/failure and refund requested/completed/failed.
- Add branded HTML/plain-text notification classes and deterministic delivery identities.
- Verify rollback, replay and exactly-once behavior with fakes.

### 9.3 — Customer notification center and preferences

- Add protected paginated notifications, unread count, read/unread and mark-all-read.
- Add preferences only for approved optional categories; mandatory transactional events always deliver.
- Add ownership, validation, accessibility and responsive tests.

### 9.4 — Admin evidence and bounded retries

- Add least-privilege delivery-log visibility with redacted metadata.
- Add read-only reconciliation and bounded retry commands for known failed deliveries.
- Route transactional work to shared-hosting-safe queues and add stale/failure evidence.

### 9.5 — Final QA and external mail gate

- Run full tests, migration cycles, build, audits and rendered customer/admin checks.
- Owner verifies controlled staging delivery, SPF/DKIM/DMARC status and inbox/plain-text rendering without sharing credentials.

## Chunk 0 decision

Agent 0 accepts the audit and bounded plan. Chunk 1 may proceed using isolated databases and local notification fakes. External mail delivery and credentials remain human-gated; deployment, Phase 18 and Agent 10 remain disabled.
