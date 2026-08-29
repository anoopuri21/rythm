# Admin workflows

## Daily start

1. Sign in with an individual staff account and MFA.
2. Review dashboard cards: weekly paid revenue, payment attention, today's orders, low stock and product health. Cards appear only when the role may view that domain.
3. Check failed notification deliveries, failed payment events and pending refunds appropriate to the role.

## Order flow

Canonical operational flow:

`pending → processing (packed) → shipped → delivered`

Terminal/exception paths are `cancelled` and `refunded`; paid orders may enter `refund_pending` while finance completes reconciliation. The stored legacy `confirmed` value may appear in old records but new operation should use `processing` for confirmed/packing work.

For each transition: open the order, confirm items/address/payment, choose the specific transition action, enter a useful internal reason/note, and confirm. Do not use generic bulk status mutation. Shipment actions require a real carrier/tracking handoff process outside the current application; record identifying detail in the note. Status history captures actor, time, previous state, next state and note.

## Refunds and cancellations

Only Finance requests/processes refunds. Verify captured payment, refundable balance, amount and reason. If a provider response is uncertain, stop: review the local refund, payment event and Razorpay dashboard before retrying. Customer cancellation of a paid order reserves a pending refund; Finance uses **Process pending refund** exactly once after reconciliation.

## Products and stock

Catalogue Manager creates/edits products, variants, category/brand assignment, prices, thresholds and media. Set stock from owned inventory only—never infer it from a source retailer. Imported products stay inactive until the review action confirms content, price/stock and media rights. Bulk activation is capped at 20 reviewed products. Use bulk delete only after confirming no operational dependency; prefer deactivation for historical products.

Low-stock means active stock is at or below that product's configured threshold. Product health flags inactive or media-less records. Resolve stock before activation and keep at least one locally managed gallery image for saleable products.

## Media

JPEG, PNG and WebP files only; maximum 5 MiB each. Product gallery maximum is 12; logo, icon, hero desktop/mobile, homepage block and OG collections accept one each. Prepare the correct crop before upload. Replace/delete media through its owning Filament resource so Spatie Media Library cleans database/storage together. Never manually place files in public storage or paste source hotlinks.

## Content and marketing

Rich text is sanitized on save. Use headings, lists, emphasis, quotes and safe links; embedded scripts/styles/iframes are intentionally removed. JSON-LD must be valid JSON and is safely encoded. New scripts or tags require a reviewed code release, not CMS insertion. Marketing manages coupons/newsletters; Content manages pages/homepage. Catalogue product content remains with Catalogue Manager.

## Notes, history and audit

Order transition reasons are internal comments and appear in status history. Refund reasons and provider states appear in refund/payment activity. Important admin model changes are audit logged with actor, request context and changed fields. Write concise operational facts; never include passwords, API keys, payment secrets or unnecessary personal data.

## Safe bulk actions

Allowed: bounded imported-product activation, reviewed product/category/brand deletion where policy permits, and resource-specific non-financial cleanup. Orders deliberately have no bulk state/refund action. Financial writes and fulfilment transitions are always record-by-record with confirmation and history.