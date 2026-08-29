# Storefront Page Checklist

Use this checklist for desktop, tablet, mobile, keyboard and screen-reader smoke review. “Conditional” means the block renders only with truthful source data.

## Global shell

- [x] Skip link, semantic main landmark and visible keyboard focus.
- [x] Search, categories, account, wishlist and cart are reachable from the header.
- [x] Footer links contact, shipping, returns/refunds, FAQs, tracking, cart, wishlist, account, terms and privacy.
- [x] Poppins hierarchy and shared UI primitives are loaded through Vite.
- [ ] Runtime test header/category resilience against `rhythm_db` UAT data.

## Homepage `/`

- [x] Hero with primary discovery CTA.
- [x] Trust/USP strip.
- [x] Popular categories.
- [x] New arrivals, trending and curated catalogue rows.
- [x] Best deals and recently launched/best-seller merchandising.
- [x] Brand/category navigation content.
- [x] Moderated testimonials (conditional; never synthetic fallback copy).
- [x] FAQ accordion (conditional).
- [x] Final browse/contact CTA.
- [ ] Verify section sequence, imagery and claims with owner-curated UAT content.

## Shop `/shop`

- [x] Search by product name, SKU and brand.
- [x] Parent/child category selection and category search.
- [x] Searchable multi-brand filter.
- [x] Min/max price, stock, real-sale and approved-rating filters.
- [x] Category-aware normalized attribute filters.
- [x] Featured, newest, price and discount sorting.
- [x] URL-backed filter state, result count, active chips, reset and pagination.
- [x] Responsive filter drawer and loading/no-result states.
- [ ] Runtime query-plan/load test with 500+ active products and realistic facets.

## Product `/product/{slug}`

- [x] Breadcrumbs and product JSON-LD.
- [x] Multi-image gallery, thumbnails and fallback.
- [x] Brand, title, approved-review summary, truthful price and stock.
- [x] Active variants, variant stock, quantity and primary add-to-cart CTA.
- [x] Wishlist and out-of-stock availability enquiry.
- [x] Description/specification tabs.
- [x] Shipping, returns/refunds, payment/privacy and tracking links.
- [x] Moderated verified reviews and product Q&A.
- [x] FAQ, related products and session-backed recently viewed products.
- [ ] Replace generic FAQs with category/product FAQ mapping if the owner supplies taxonomy.
- [ ] Owner approval required before claiming fixed warranty/delivery windows or authenticity certification.

## Cart `/cart` and drawer

- [x] Clear product, variant, quantity, removal and line total.
- [x] Server-derived subtotal and shipping/tax preview language.
- [x] Obvious continue-shopping and checkout actions.
- [x] Empty, error and loading behavior.
- [ ] Consider cart-level coupon entry only if product wants duplication of checkout coupon logic.
- [ ] Runtime concurrency test for stock/price changes between cart and checkout.

## Checkout `/checkout`

- [x] Authentication gate and two-step address/payment journey.
- [x] Saved/new addresses and field validation.
- [x] Order summary, coupon state, shipping, tax and final total.
- [x] Gateway/test-mode distinction and protected processing state.
- [x] Shipping, returns/refunds, terms and privacy links before payment.
- [ ] Controlled UAT for success, failure, replay and retry without real charges.

## Account `/account`

- [x] Overview, order history, saved addresses, profile and password settings.
- [x] Wishlist and notification entry points.
- [x] Support hub for contact, tracking and return/refund guidance.
- [ ] Dedicated post-delivery case history requires approved returns rules and a separate domain workflow.

## Orders and aftercare

- [x] Owned/signed/guest-authorized detail.
- [x] Invoice and status history.
- [x] Throttled guest lookup.
- [x] Eligible cancellation and pending-refund recording.
- [ ] Persistent back-in-stock subscription is not yet active; current UX is an availability enquiry.
- [ ] Post-delivery return authorization workflow awaits owner policy and operational ownership.
