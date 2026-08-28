# Phase 9 Chunk 1 — Durable Notification Domain

**Status:** COMPLETE
**Date:** 29 August 2026
**Accountable:** Agent 0
**Primary:** Agents 14, 3, 4 and 8
**Independent review:** Agents 9 and 11

## Delivered

- Immutable commerce events with deterministic event keys, payload hashes, bounded aggregate identity and allowlisted metadata.
- Event replay returns the existing record; reuse of an identity with changed data is rejected.
- Per-recipient/channel delivery records with deterministic duplicate-suppression keys.
- Recipient addresses are represented by hashes rather than stored in delivery evidence.
- Bounded delivery status, attempt count, timestamps and failure metadata support later operational reconciliation.
- Standard Laravel database notification storage is active for customer inbox delivery.
- Per-user optional notification preferences support approved order/product update categories.
- Mandatory transactional/security categories always remain enabled and cannot be configured off.
- User relationships expose owned deliveries and preferences without introducing public admin access.

## Gates

- Focused notification/infra/governance regression: **20 tests / 114 assertions passed**.
- Full Laravel regression: **326 tests / 1,306 assertions passed**.
- Migration forward → rollback → forward passed in disposable SQLite QA.
- Pint passed in disposable external QA.
- Workspace `vendor` entry remained absent.

## Safety

No email, external provider request, credential, persistent-UAT write or deployment occurred. Metadata allowlisting excludes arbitrary PII and secrets. Agent 0 accepts Chunk 1; central after-commit commerce events and listeners are next.
