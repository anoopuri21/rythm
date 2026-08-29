# Homepage Discovery and Database Recovery Plan

**Status:** IMPLEMENTED — owner database correction and PHP/rendered regression pending
**Date:** 29 August 2026
**Accountable:** Agent 0

## Diagnosis

1. Popular Categories is constrained by a legacy hard-coded slug list and hard-coded category image paths, so newly imported/activated categories can be omitted or show missing imagery.
2. New Arrivals is a hard-coded legacy product-slug list, so current active products are absent even when correctly activated.
3. Trending is queried but never rendered by the homepage template. In addition, `is_trending` and `featured_rank` are missing from Product mass-assignment metadata, and product changes do not invalidate the one-hour homepage cache.
4. The existing curated Deals Of The Day pool uses legacy slugs; a truthful Best Deals section should instead select active products only when `compare_at_price > price`.
5. The exception is an environment/database mismatch, not a missing application migration: `rythm.test` is connected to `maverick_academy`, while this project database is `rhythm_db`. Application code must not silently create Rythme tables in the unrelated database.
6. The navbar view composer queries categories while rendering error pages, causing the wrong-database failure to obscure the original response.

## Simple implementation

- Make Popular Categories data-driven, bounded and image-safe, using active categories with active products.
- Make New Arrivals the latest active products, bounded for homepage performance.
- Render a dedicated Trending Products section from explicitly marked active products.
- Add a Best Deals section sourced only from active products with a real lower current price than compare-at price.
- Make `is_trending` and `featured_rank` persist correctly and flush homepage cache after product/category changes.
- Make navbar category composition degrade to an empty navigation list when the categories table is unavailable, so error pages remain renderable.
- Require the owner to correct `DB_DATABASE=rhythm_db`, clear configuration/cache and verify migration status; do not mutate `maverick_academy`.

## Safety and regression gates

- All storefront queries remain active-product-only and bounded.
- No fake sale, stock, popularity or arrival claims are generated.
- Existing category rows, Shop, product activation and admin controls remain intact.
- Empty sections remain hidden truthfully.
- Homepage query/presentation, admin product persistence, error rendering and full regression tests must pass before acceptance.
