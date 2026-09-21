# C2 — Multi-variant depth plan (W3)

**Status:** CODE COMPLETE (2026-09-12) — run `php artisan test --filter=VariantDepthTest` on PHP host  
**Depends on:** C1 (public content hide-when-empty)  
**Goal:** One product → many variants, each with own **price, stock, images, color, specs**; storefront + cart + checkout correct.

---

## 1. Current baseline

| Layer | Have today | Gap |
|---|---|---|
| DB `product_variants` | name, sku, price_override, stock, is_active, **options JSON** | options rarely edited in admin |
| Media | `variant_gallery` collection (max 6) | PDP gallery does not swap on select |
| Attributes | Color type + `color_hex` on values; pivot to variants | **No Filament UI** to attach on product form |
| Admin Product form | Repeater: name/sku/price/stock/active/images | No color hex, no key/value specs |
| AddToCart | Selects variant; price/stock from variant; color **only** via attributeValues | Falls back if no attributes; no options JSON color |
| PDP gallery | Product-level images only (Alpine) | Variant images unused on select |
| Cart/Order | unit_price + options snapshot from variant | OK if options populated |
| Specs tab | SKU/brand/category only | Variant options not shown |

---

## 2. Target behaviour

1. Admin adds variants with: name, SKU, price override, stock, active, **color (hex + label)**, **specs (key→value)**, **images**.  
2. Storefront shows swatches (color) or chips (name).  
3. Selecting a variant updates **price, compare display, stock, qty max, images**.  
4. OOS variants disabled or hidden; if all OOS → product OOS path.  
5. Cart line shows variant name (+ color/spec summary).  
6. Checkout/order snapshots `options` including color/specs.  
7. No crash when variant has zero images (fallback product gallery).

---

## 3. Implementation chunks (this C2)

| ID | Work | Files |
|---|---|---|
| C2.1 | Admin variant fields: `color_hex`, `color_name`, KeyValue `options` specs; persist into `options` JSON on save | `ProductResource.php` (+ mutate form data if needed) |
| C2.2 | Helper on `ProductVariant`: `colorHex()`, `colorName()`, `specList()`, `galleryUrls()`, `optionSummary()` | `ProductVariant.php` |
| C2.3 | AddToCart: use helper for color/images; dispatch `variant-updated` with image list | `AddToCart.php`, blade |
| C2.4 | PDP gallery listens to `variant-updated` and swaps images | `product/show.blade.php` |
| C2.5 | Specs tab shows product + selected variant specs (optional static product attrs later) | show + AddToCart or shared |
| C2.6 | Cart drawer/page: richer variant label from options | cart blades if thin |
| C2.7 | Feature tests: two variants different price in cart; options snapshot; public color from options | new/extend tests |
| C2.8 | MEMORY + PRODUCTION_PRIORITY_PLAN status | docs |

**Out of C2 (later):** full Attribute CRUD Filament resource, filter facet admin, wishlist-per-variant.

---

## 4. Options JSON shape (canonical)

```json
{
  "color": "Sunburst",
  "color_hex": "#C4A35A",
  "finish": "Gloss",
  "scale": "25.5\""
}
```

- Reserved keys: `color`, `color_hex`  
- Other keys = free specs  
- AttributeValues color still wins if present (back-compat)

---

## 5. Acceptance

- [ ] Admin can save 2 variants with different price, stock, hex, specs, images  
- [ ] PDP select updates price + stock + gallery  
- [ ] Cart unit_price matches variant override  
- [ ] Order item options contains color/specs  
- [ ] No images on variant → product gallery remains  
- [ ] Tests green (when PHP available)

---

## 6. Risks

| Risk | Mitigation |
|---|---|
| Filament repeater + Spatie media on nested models | Already works for images; keep relationship() |
| Livewire/Alpine event name collision | Namespace `rythme-variant-updated` |
| Empty options wipe | Merge reserved color keys carefully on save |

---

*Execute C2.1→C2.8 in one sitting after C1 push.*
