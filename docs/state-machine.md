# Commerce State Machines

## Order state

Canonical values are defined by `App\Enums\OrderStatus`; legacy model constants alias enum values for compatibility.

| From | Allowed next state | Owner/evidence |
|---|---|---|
| `pending` | `confirmed`, `cancelled` | payment capture or customer cancellation |
| `confirmed` | `processing`, `shipped`, `cancelled` | operations/fulfillment or eligible cancellation |
| `processing` | `shipped`, `cancelled` | fulfillment or eligible cancellation |
| `shipped` | `delivered`, `cancelled` | fulfillment event or exceptional approved cancellation |
| `delivered` | none | terminal fulfillment state |
| `cancelled` | none | terminal order state; payment refund can continue separately |
| `refunded` | none | legacy terminal order label; payment/refund ledgers remain authoritative |

`OrderStateMachine::assertTransition()` rejects unknown and illegal values. `OrderService::changeStatus()` owns mutation, history, and customer notification. Fulfillment no longer writes order status directly; it delegates synchronization to this path.

## Order payment summary state

Canonical values are defined by `App\Enums\OrderPaymentStatus`.

- `unpaid` — no captured attempt; a valid retry may exist.
- `paid` — captured payment recorded and first inventory capture committed.
- `failed` — latest known attempt failed; does not erase attempt history.
- `refund_pending` — a refund request exists but provider completion is not established.
- `refunded` — approved refund processing has recorded completion according to refund rules.

Order status and payment status are orthogonal. A cancelled paid order normally remains order `cancelled` while payment moves `paid → refund_pending → refunded`.

## Payment attempt state

Canonical values are defined by `App\Enums\PaymentStatus`.

- `initiated → paid` after verified provider facts/signature.
- `initiated → failed` after a known failure.
- `paid → refunded` only after completed refund accounting.
- Terminal paid/failed/refunded attempts are never reused as a new attempt.

Gateway event identity is separately deduplicated in `payment_events`; callback retries do not imply payment-attempt retries.

## Refund state

- `pending → processing` after explicit approval/reservation.
- `processing → refunded` after a verified provider result.
- `processing → failed` after a known provider failure.
- Unknown provider outcome remains reconcilable and must not be blindly retried.

## Shipment state

- `draft → ready | cancelled`
- `ready → dispatched | cancelled`
- `dispatched → delivered`
- `delivered` and `cancelled` are terminal.

Shipment writes are transactional and event-ledgered. Full dispatched allocation synchronizes the order to `shipped`; full delivered allocation synchronizes it to `delivered` through the order state machine.

## Inventory operation state

Inventory is a ledger operation rather than a mutable workflow:

1. Lock order.
2. Check unique operation identity.
3. Conditional atomic decrement or exact restoration.
4. Read resulting balance.
5. Append movement with operation identity.
6. Commit all or roll back all.

Duplicate capture/restoration returns without changing stock. Insufficient stock or missing source rolls back both balance and ledger.

## Prohibited transitions

- cancelled order → paid;
- paid order → failed due to a late callback;
- delivered/cancelled order → active fulfillment state;
- inventory mutation without movement evidence;
- refund completion based only on a client request;
- arbitrary status strings from controllers/admin forms without state-machine validation.
