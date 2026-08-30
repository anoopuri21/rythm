# Storefront Content Coverage

**Principle:** absence is preferable to fabricated social proof, delivery promises, scarcity, warranties or certification claims.

| Surface | Required content | Source | Coverage |
|---|---|---|---|
| Homepage hero | headline, support copy, image/video, CTA | HeroSlide/admin configuration | Covered; conditional DB content |
| Categories | name, local image, active products | Category catalogue | Covered |
| Featured/new/trending/deals | active products and truthful merchandising state | Product/HomepageDataService | Covered |
| Trust points | implemented checkout/account capabilities | maintained Blade copy | Covered, deliberately conservative |
| Testimonials | real moderated customer story only | active testimonial blocks under business moderation | Conditional; no synthetic fallback |
| Homepage FAQ | practical approved answers | active FAQ records | Covered when records exist |
| Final CTA | catalogue and contact next steps | maintained Blade copy | Covered |
| Product gallery | locally managed product media | media library | Covered with honest fallback |
| Product commercial data | SKU, price, compare-at, variants, stock | catalogue database | Covered and server revalidated |
| Delivery/payment note | configuration-dependent explanation | maintained Blade copy + checkout totals | Covered without fixed promises |
| Product trust links | shipping, returns, privacy/payment, tracking | CMS pages and named routes | Covered |
| Reviews | approved verified-purchase reviews | ReviewService | Covered; empty state allowed |
| Product questions | moderated customer questions/answers | ProductQuestionSection | Covered |
| Related products | admin-curated active products with same-category fallback | ProductMerchandisingRule/ProductQueryService | Phase 11 chunk 1 covered |
| Recently viewed | bounded active IDs from current browser session | session + ProductQueryService | Covered |
| Search/facets | weighted product, SKU, brand, category, attribute, price, rating, stock and sale matching | ProductQueryService/Livewire | Phase 11 chunk 1 covered; performance/relevance evidence pending |
| Cart totals | line totals and subtotal | Cart/checkout services | Covered |
| Promotion | validated coupon and discount | CouponService at checkout | Covered |
| Shipping preview | configured fee/tax shown before payment | CheckoutWizard | Covered |
| Account | orders, addresses, profile, notifications, support paths | account controllers/views | Covered |
| Order tracking | recorded status and authorized detail | OrderController | Covered |
| Returns/refunds | cancellation and pending-refund truth; post-delivery guidance | OrderService/CMS/contact | Partial; dedicated return cases need approved policy |
| Back-in-stock | authenticated, explicit-consent stock-availability request with customer cancellation | BackInStockSubscription/AddToCart/AccountController | Phase 11 chunk 1 stores and manages a bounded request; cPanel scheduling remains an operations gate |
| Authenticity | only evidence-backed product/brand statements | product/admin content | Owner-content gate; no global certification claim |

## Content rules

1. “New”, “Best Seller” and “Limited” badges require a real merchandising source. Never infer scarcity from source availability or low stock.
2. Compare-at pricing appears only when above current price.
3. Ratings and testimonials require approved, policy-compliant records.
4. Do not publish fixed delivery, return, warranty, EMI or payment-method promises unless configuration and owner policy support them.
5. Acquired product media is locally managed; source hotlinks are prohibited.
6. Policy copy requires owner/legal approval before production even when technically present.

## Remaining owner content

- Approved shipping regions, service levels and escalation owner.
- Approved post-delivery return windows, eligible conditions and evidence requirements.
- Manufacturer/distributor authenticity evidence and per-product warranty terms.
- Owner confirmation of notification delivery/retention rules before the Phase 11 stock-request worker is enabled.
- Moderated testimonials linked to qualifying customer evidence.
