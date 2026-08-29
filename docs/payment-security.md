# Payment security

## Principle

Only server-verified Razorpay state may advance an order. Browser values can initiate a verification attempt but cannot set amount, currency, order ownership, inventory, `payment_status`, or fulfilment status.

## Local initiation

1. Checkout requires an authenticated account and an address owned by that account.
2. The server locks cart/product rows and calculates line prices, discounts, shipping, tax and total from database state.
3. Checkout uses a per-mount UUID idempotency key and a unique database constraint.
4. The server creates the Razorpay order and stores its gateway order ID against one local order/payment.
5. Payment retry locks the order, checks ownership and eligible state, and prevents duplicate active reservations.

## Browser callback

The callback locates the local payment by gateway order ID, verifies Razorpay's callback signature, then independently fetches the payment from Razorpay. Paid transition is allowed only when provider data says `captured` and its payment ID, order ID, amount and currency match local state. A failed/untrusted callback does not mark the order failed or paid; a later authenticated provider signal can reconcile it.

## Webhook handling

1. Read the exact raw request body.
2. Verify `X-Razorpay-Signature` as HMAC-SHA256 over that raw body with `RAZORPAY_WEBHOOK_SECRET` using constant-time comparison.
3. Decode JSON only after signature verification.
4. Derive idempotency identity from `X-Razorpay-Event-Id`; if absent, use SHA-256 of the raw body.
5. Store only bounded redacted metadata and the payload hash. Reuse of one event identity with another payload is rejected with 409.
6. Explicitly allow payment-state handling only for:
   - `payment.authorized`: verify local gateway order, payment ID, amount, currency and `authorized` status; record `authorized` but do **not** confirm the order, capture inventory, or grant paid state;
   - `payment.captured`: require matching fields, `captured` status, and the capture flag, then perform the paid transition;
   - `order.paid`: require the included payment entity to satisfy the same captured-payment checks, then perform the paid transition.
7. Other correctly signed events are recorded as processed/ignored and receive a fast 200 response without touching commerce state.
8. Previously processed identical deliveries receive 200. Conflicting or previously rejected identities are not silently accepted.

## Atomic paid transition

`OrderService::markPaid()` runs in a database transaction and locks the order/payment. It rejects cancelled orders, returns without effect if already paid, requires a matching locally initiated payment, records the provider payment ID, captures inventory once, confirms the order, writes status history and dispatches an idempotently keyed notification.

`markPaymentAuthorized()` also locks and correlates local rows, but changes only payment authorization state. Authorization is deliberately not equivalent to capture.

## Failure and response policy

| Condition | Response/effect |
|---|---|
| Invalid/missing signature | 400, no receipt/state mutation |
| Malformed signed JSON | 400, no payment-state mutation |
| Signed ignored event | 200 `ignored` |
| Duplicate processed event | 200 replay acknowledgement |
| Event ID reused with different body | 409 |
| Unknown local gateway order | 404 and failed receipt |
| Amount/currency/order/status mismatch | 422 and failed receipt |
| Transition failure | non-2XX; no partial paid transition due to DB transaction |

Endpoint throttles are an abuse backstop, not a substitute for signature verification or idempotency. Razorpay source IP allowlisting may be added only if operationally maintained; stale IP rules must not replace HMAC verification.

## Secrets and logging

Canonical configuration is `config/services.php`, backed only by:

- `RAZORPAY_KEY_ID` (public checkout key),
- `RAZORPAY_KEY_SECRET`,
- `RAZORPAY_WEBHOOK_SECRET`.

Secret values, signatures, raw webhook bodies and full provider payloads must not be logged or stored in Git/database audit records. Error logs use event/order identifiers or exception classes, not credential-bearing request bodies.

## Operations and testing

- Configure HTTPS and preserve the raw body through the proxy/web server.
- Subscribe at minimum to `payment.authorized`, `payment.captured`, and `order.paid`.
- Replay fixed signed fixtures for accepted, ignored, duplicate, conflict and mismatch cases.
- Test authorization followed by capture, capture arriving first, duplicates, cancellation races and amount/currency tampering.
- Reconcile authorized payments that do not capture within the operational window.
- Runtime qualification remains blocked until the owner points `rythm.test` at `rhythm_db`; never test financial writes against the unrelated database.
