# Phase 8 — Payment, Refund and Financial Reconciliation Plan

**Status:** COMPLETE — Chunks 0–5 accepted by Agent 0
**Date:** 29 August 2026
**Accountable:** Agent 0
**Primary:** Agents 12, 3, 4 and 6
**Independent review:** Agents 9 and 11

## Chunk 0 read-only audit

### Existing qualified foundation

- Razorpay and fake-gateway abstraction exists.
- Browser callback signatures and raw webhook HMAC signatures are verified.
- Browser verification fetches the payment and checks captured status and amount.
- Checkout/order idempotency, unique gateway order/payment identifiers, row locks and exactly-once stock capture exist.
- Durable `payment_events` schema supports unique gateway events, hashes, redacted metadata and processing state.
- Durable refund requests are created for eligible paid cancellations without claiming gateway completion.
- Refund records, finance permissions and admin audit observation exist.

### Material gaps

1. The webhook controller does not write to `payment_events`; replay identity and durable failure evidence are therefore not active.
2. Webhook capture processing trusts status and identifiers but does not verify amount and currency against the internal order before `markPaid`.
3. Browser verification checks amount but not fetched currency or fetched gateway order ownership.
4. There is no bounded payment retry workflow for failed or abandoned payments.
5. `PaymentGateway` has no refund operation; `RefundService` records requests only.
6. The current unique `refunds.order_id` design permits only one refund and cannot represent safe partial-refund sequences.
7. There is no least-privilege finance refund approval/processing resource or complete customer/admin payment/refund timeline.
8. There is no reconciliation command/report comparing internal orders, payments, events and refunds with provider records.
9. Real Razorpay test-mode checkout, webhook and refund evidence remains an unavoidable owner-controlled external financial gate.

## Locked safety rules

- No real or test-mode external financial write occurs automatically; credentials stay environment-only.
- Local automated tests use fakes and isolated databases.
- Every provider write requires a durable idempotency identity, row locking and reconciliation-safe post-state handling.
- Unknown outcomes are reconciled before retry; blind refund/payment retries are forbidden.
- Refund totals cannot exceed captured amount and currency must match.
- Only finance-authorized staff can approve/process refunds; reason and audit evidence are mandatory.
- Webhook payload retention is allowlisted/redacted; signatures, secrets, full payment instruments and unnecessary PII are never stored or logged.
- Shared-hosting execution uses bounded commands/cron and does not require persistent workers.

## Delivery chunks

### 8.1 — Payment verification and event ledger

- Persist replay-safe webhook events with payload hashes and allowlisted metadata.
- Verify gateway order ownership, captured amount and currency on callback and webhook paths.
- Record processed/failed outcomes without leaking secrets or raw sensitive payloads.
- Add mismatch, replay, malformed event and duplicate callback tests.

### 8.2 — Safe payment retry

- Permit bounded retry only for an owned unpaid/failed order that remains payable.
- Create a new initiation identity without duplicating orders, coupon use or stock capture.
- Prevent retry for paid, cancelled or mismatched orders and test refresh/concurrency behavior.

### 8.3 — Full and partial refund operations

- Extend the gateway contract with an idempotent refund result.
- Support multiple bounded partial refunds and a full-refund terminal state.
- Add least-privilege finance approval/processing with mandatory reason and audit trail.
- Use fake-gateway tests; defer any external Razorpay call to the human test-mode gate.

### 8.4 — Timelines and reconciliation

- Add payment/refund status visibility for finance admins and customers without exposing sensitive metadata.
- Add a bounded read-only reconciliation command/report and actionable mismatch states.
- Add safe alerts/evidence hooks for invalid signatures, amount/currency mismatches and unreconciled records.

### 8.5 — Final QA and external test-mode gate

- Run full regression, migration cycles, build, dependency/security audits and permission tests in disposable QA.
- Owner runs controlled Razorpay test-mode payment, replay, failure, full/partial refund and reconciliation scenarios.
- Record exact provider IDs only in protected operational evidence, never in Git if sensitive.

## Chunk 5 owner-controlled test-mode result — 29 August 2026

The owner reported that controlled Razorpay test-mode qualification passed:

- expected-INR payment capture and paid/confirmed order state;
- harmless callback/webhook replay;
- declined payment and retry without duplicate order or stock capture;
- partial refund with truthful remaining balance;
- cumulative full refund with matching provider/internal state;
- final reconciliation with no unresolved findings.

No credentials or provider identifiers were supplied or committed.

## Agent 0 decision

Agent 0 accepts Chunks 0–5 and marks Phase 8 complete. This test-mode acceptance does not authorize live financial actions, deployment, Phase 18 or Agent 10.
