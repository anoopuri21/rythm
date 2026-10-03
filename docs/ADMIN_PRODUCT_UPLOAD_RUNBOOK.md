# Admin product upload runbook (flowless)

**Audience:** Catalogue staff / client  
**Panel:** `/admin` → **SHOP → Products**  
**Goal:** Publish a sellable SKU without leaving the product form mid-flow.

---

## 0. Before you start

1. Log in as staff with **catalogue** access.  
2. Have ready: product name, selling price (₹), stock count, ≥1 photo you may use commercially, category, manufacturer brand.  
3. Variants (optional): each colour/finish needs its own SKU, stock, and ideally its own photos.

---

## 1. Simple product (no variants) — under 5 minutes

1. **Products → Create**.  
2. **Details**
   - **Name** (required)  
   - **Slug** (required; keep stable after go-live)  
   - **SKU** — optional; leave blank to auto-generate a unique code  
   - **Category** — pick or **Create** inline if missing  
   - **Brand** — manufacturer label; create inline if missing  
   - **Price** (₹)  
   - **Compare-at / MRP** only if higher than price  
   - **Stock** (base stock)  
   - **Active** = ON to show on shop  
   - Short description + full description (original copy)  
3. **Optional tax** — leave blank until CA-approved values exist.  
4. **Variants** — leave empty for simple SKUs.  
5. **Media** — upload gallery (and optional social OG image). Drag the thumbnails to order them: **the first image is the card/hero image** on the website.  
6. **SEO** tab — title/description if you care about Google.  
7. **Save**.  
8. Header / row action **View on storefront** (only when Active) → confirm page.  
9. Open `/shop` → filter category → card appears.

**If it does not show:** Active off? Stock 0 with no purchasable path? Wrong category filter? Cache — hard refresh.

**If an image preview keeps "loading" after you save and reopen, or the photo is missing on the site:** this is a server storage problem, not the photo. Ask whoever runs the server to run `php artisan storage:link` and `php artisan media:relocate` (see `docs/media-architecture.md` → Troubleshooting).

**Where the image URL lives:** every product/variant/brand/category/hero/homepage image has its resolved URL stored in the database next to the record (`thumbnail_url`, `gallery_urls`, `og_image_url`, …). The website and the admin preview both read *that* value — so what you see in the panel is exactly what the site serves. If a list thumbnail looks empty while the image actually exists, the server owner runs `php artisan media:sync-urls` (see `docs/media-architecture.md` → M-7).

**If a photo is rejected or the upload silently does nothing:** check the file. Allowed: JPG, PNG, WebP (galleries also AVIF), max 5 MB each (social image 3 MB, hero 8 MB), max 6000×6000 px. Server-side, the host must allow a single 12 MB upload (`upload_max_filesize` ≥ 12M, `post_max_size` ≥ 16M) and give PHP enough memory for the WebP conversions (`memory_limit` ≥ 256M).

---

## 2. Product with variants (colour / finish / size)

1. Create base product as above. Base **stock** can be 0 if every sale is via variants.  
2. Open **Variants** section → **Add item** for each option:
   - Variant **name** (e.g. Sunburst)  
   - **SKU** — leave blank to auto-generate  
   - **Price override** (blank = base product price)  
   - **Stock** for that option only  
   - **Active**  
   - **Color name** + **color swatch** (hex) for storefront circles  
   - **Other specs** key/value (Finish, Scale, …)  
   - **Variant images** (up to 6) — storefront gallery swaps when shopper picks this option  
3. Save.  
4. View storefront → click each option → price, stock, images, specs update.  
5. Add to cart → cart line shows option summary.

---

## 3. List tools (find problems fast)

On **Products** list, use filters:

| Filter | Use |
|---|---|
| Published | Active on/off |
| Out of stock (base) | Base stock ≤ 0 |
| Has variants | Multi-option SKUs |
| Missing gallery image | No product gallery media |
| Imported — pending activation | Import pipeline waiting review |

Row action **View** opens live PDP for active products.

---

## 4. Imported products (bulk pipeline)

1. Imports arrive **inactive** with provenance.  
2. Check copy, price, stock, local gallery rights.  
3. Row/bulk **Approve & activate** (checkboxes + reason required).  
4. Do not flip Active manually on import-sourced rows.

---

## 5. Do / don’t

| Do | Don’t |
|---|---|
| Original descriptions | Paste competitor legal claims |
| Real stock numbers | Publish with zero stock if you expect sales |
| Stable slugs | Change slug after ads/SEO index |
| Rights-cleared photos | Hotlink random web images |
| Leave tax blank until approved | Invent GST% on the product |

---

## 6. After upload checklist

- [ ] Active ON  
- [ ] At least one gallery image (product or every variant); after **Save**, reopen the product — thumbnails must still show  
- [ ] Price > 0  
- [ ] Stock path exists (base or variant)  
- [ ] Category + brand set  
- [ ] Storefront View looks correct on phone width  
- [ ] Test add-to-cart once  

---

*Related: `docs/CLIENT_HANDOVER_DETAILS.md` · `docs/C2_VARIANT_DEPTH_PLAN.md` · `docs/PRODUCTION_PRIORITY_PLAN.md` · `docs/media-architecture.md`*
