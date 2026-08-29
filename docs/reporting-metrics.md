# Reporting metrics

All currency metrics use stored INR order/payment amounts and application timezone. Operational cards are directional dashboards, not an accounting ledger.

| Metric | Definition | Source | Cadence / owner |
|---|---|---|---|
| Paid revenue (7d) | Sum `orders.total` where `payment_status=paid` and order created since start of current week | Orders | Daily / Finance |
| Orders today | Count of orders created since local start of day, all statuses | Orders | Daily / Operations |
| Payment attention | Count of payment rows still `initiated` or `failed` | Payments | Several times daily / Finance |
| Refund pending | Orders/refunds awaiting provider completion or finance action | Orders, refunds | Daily / Finance |
| Customers | Count of users with role `customer`; staff excluded | Users | Weekly / Support |
| Low stock | Active products where stock is at/below that product's threshold | Products | Daily / Catalogue |
| Product health | Products inactive or with no media relation | Products/media | Daily / Catalogue |
| Fulfilment backlog | Paid/processing orders not shipped/delivered/cancelled | Orders | Daily / Operations |
| Delivery throughput | Orders changed to delivered in period | Status history | Weekly / Operations |
| Payment success rate | Distinct paid payments divided by completed payment attempts; define attempt window before external reporting | Payments/events | Weekly / Finance |
| Average order value | Paid order revenue divided by paid order count in the same period | Orders | Weekly/monthly / Finance |
| Notification failure rate | Failed deliveries divided by sent+failed delivery attempts | Notification deliveries | Daily / Support |

## Interpretation rules

- Keep date window and status filters identical when comparing periods.
- Never add authorized-only, failed, cancelled, unpaid or duplicate payment events to paid revenue.
- Refund-adjusted/net revenue is a separate finance metric: paid revenue minus completed refunds in the reporting period. Do not silently label gross paid order value as net revenue.
- Product stock reports use owned local stock only. Source catalogue availability is never inventory.
- Product health may count one product in multiple defect categories; the dashboard card counts matching products once.
- Customer counts exclude all staff roles.

## Reconciliation

Daily Finance compares payment-attention/refund queues with Razorpay. Monthly Finance reconciles gross captured payments, completed refunds, fees/taxes from provider settlement reports and bank settlement; local dashboard revenue alone is insufficient for statutory accounts. Record discrepancies by local order number, gateway order/payment ID and event ID without copying secrets or full sensitive payloads.

## Export safety

Exports are permission-scoped and should include only fields needed for the operational purpose. Avoid password/authentication data, full provider payloads and unnecessary personal data. Store exports outside public webroot, encrypt when transferred, set an expiry, and delete after use. Large exports should be queued/bounded rather than generated in a web request.