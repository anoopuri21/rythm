# Storefront Flow

**Phase:** 2 — complete single-brand ecommerce experience  
**Updated:** 29 August 2026  
**Status:** implementation candidate; runtime UAT remains gated by the local database selection.

## Primary purchase journey

1. **Homepage** — customer sees the brand promise, hero CTA, trust points, categories, current catalogue groups, curated best/deal surfaces, moderated testimonials when available, FAQs and a final catalogue/contact CTA.
2. **Discovery** — `/shop` supports query search, category shortcuts/tree, searchable brands, price range, approved-review rating, category-aware product attributes, availability, real sale state, active-filter chips, sorting and pagination.
3. **Product decision** — `/product/{slug}` shows a square multi-image gallery, truthful price/discount, active variants, variant stock, quantity, shipping/payment notes, wishlist, policy links, moderated reviews, product Q&A, FAQs, recently viewed products and related products.
4. **Cart** — drawer provides a quick review and obvious checkout step; full cart supports quantity/removal, server-derived totals, shipping/tax preview language and a clear checkout CTA.
5. **Checkout** — authenticated two-step address/payment flow; coupon validation, shipping/tax/total breakdown, gateway status and policy links are visible before payment.
6. **Completion** — signed success route presents the recorded order. Customer can use account orders or protected guest lookup to track subsequent status.
7. **Aftercare** — owned order pages permit eligible cancellation; paid cancellation records a pending refund request. Post-delivery questions currently enter through customer support after policy review.

## Returning-customer journeys

- **Wishlist:** account-backed save/remove from cards and product pages; guest is directed to authentication.
- **Recently viewed:** session-backed, bounded to 12 active product IDs; up to four prior products appear on product pages. No personal data or product snapshot is stored in the session.
- **Account:** overview, orders, saved addresses, profile/password, notifications and a support hub.
- **Order tracking:** account order links or throttled guest lookup; authorization protects detail and invoice access.

## Trust journey

Trust statements must describe implemented behavior, not promises the business has not approved:

- catalogue availability and totals are revalidated by the server;
- shipping and taxes are displayed before payment according to configuration;
- payment methods come from the configured gateway;
- only approved verified-purchase reviews contribute to public ratings;
- cancellations and refund requests describe recorded status, never provider completion;
- shipping, returns, terms, privacy, FAQs, tracking and contact routes are available from decision/checkout surfaces.

## Failure and empty states

- Empty catalogue: explain that publication is pending; return to homepage.
- No filter result: retain filter context and offer one clear reset.
- Out of stock: disable add-to-cart and offer an availability enquiry; do not claim a persistent stock alert.
- Empty cart/wishlist/orders: explain state and provide one recovery CTA.
- Payment failure: preserve the order and expose bounded retry only when allowed by payment state.

## Open human/runtime gates

1. Point `rythm.test` to `rhythm_db` and clear cached configuration.
2. Run focused/full PHP tests in an external disposable dependency runtime.
3. Verify responsive homepage, shop, product, cart, checkout, account and tracking views with real UAT data.
4. Owner must approve operational rules before a persistent back-in-stock subscription or post-delivery returns workflow is activated.
