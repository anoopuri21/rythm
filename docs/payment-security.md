# Payment security

## Authoritative state

Frontend success messages and request fields are never payment evidence. An order becomes paid only after backend verification of a Razorpay signature plus provider data showing capture, with exact gateway order ID, payment ID, amount and currency matching the server-created payment/order.

Accepted state-changing webhook families:

- `payment.captured`
- `order.paid` (only when the included/fetched payment evidence satisfies captured invariants)

`payment.authorized` is acknowledged with HTTP 200 and recorded, but does not mark a payment/order paid. Other correctly signed event types are recorded and acknowledged as ignored. Invalid signatures are rejected and never persisted as trusted events.

## Browser callback

1. Rate limit the endpoint.
2. Require callback fields and a configured gateway.
3. Locate the local payment by gateway order ID; never accept an order total from the browser.
4. Verify HMAC over `order_id|payment_id` using the environment secret.
5. Fetch provider payment and require `captured` status.
6. Require local gateway order, exact paise amount, currency and payment ID invariants.
7. Mark paid transactionally through `PaymentEventService`/`OrderService`.
8. Redirect only through a temporary signed success URL.

## Webhook

1. Capture the raw body and `X-Razorpay-Signature`.
2. Verify HMAC before interpreting or mutating payload data.
3. Decode valid JSON, derive provider event ID when present, and store a payload-hash fallback.
4. Insert/deduplicate the payment event under transaction/unique constraints.
5. Return fast 2XX for replayed processed/rejected events, authorized-only events, unknown signed event types, and poison events that cannot match a local payment. This avoids unbounded provider retries; operations reviews the recorded failure.
6. For capture-capable types only, enforce local payment existence and all captured invariants before marking paid.

The handler is safe under repeated delivery: a provider event ID or stable payload hash identifies replay, payment provider IDs are unique, and paid transitions are idempotent.

## Tamper and failure behavior

| Condition | Result |
|---|---|
| Missing/invalid signature | 400; no trusted processing or mutation |
| Unknown signed event | 200 ignored; event retained |
| `payment.authorized` | 200 accepted; no paid transition |
| Duplicate processed event | 200 replay acknowledgement |
| Duplicate rejected event | 200 replay acknowledgement; no retry loop |
| Amount/currency/order/payment mismatch | Event failed, 200 accepted, no paid transition |
| Captured valid event | Payment/order marked paid once |
| Ambiguous refund provider outcome | Do not blindly retry; reconcile provider state first |

## Operations

Use test-mode keys outside production. Configure separate webhook secrets per environment and subscribe only to needed events. Monitor failed `payment_events`, initiated/failed payments, refund-pending orders and provider dashboard discrepancies. Never log key material, full payment credentials, signatures, or sensitive payload fields. Rotate secrets through the hosting environment, clear/rebuild config cache, then send a signed test event.