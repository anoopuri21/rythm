# Client handover — details **you** fill (optional until you are ready)

**Product:** Rhythm Exports / Rythme Music Store  
**Rule:** Nothing in this file is required for the website to run.  
**Rule:** If you leave a field empty or a toggle OFF, that detail **must not show** on the website and **must not** break cart, checkout, or admin.  
**Rule:** Engineering will not invent legal/tax/policy text for you.

---

## 1. How to use this document

1. After handover, keep this file (or a printed copy).  
2. Log in to **Admin** → `/admin` with your staff account.  
3. Fill only what your business has approved.  
4. Save. Visit the storefront and confirm what appears.  
5. If something still shows that you did not fill — report it as a bug.

---

## 2. Master switchboard (Admin → Settings)

Path: **Admin panel → Settings** (name may show as “Settings”).

| What you can set | Admin control | If empty / OFF | Website behaviour |
|---|---|---|---|
| Shipping flat fee (₹) | `Shipping flat fee` | `0` or blank | No extra shipping charge; don’t show a fake fee |
| Free shipping above (₹) | `Free shipping above` | `0` | Threshold logic inactive / treat as always free if fee is 0 |
| Tax calculation | `Enable configured tax calculation` | **OFF** (default) | **No tax line** on cart/checkout; total = goods + shipping only |
| Default tax rate % | `Approved default tax rate` | `0` or blank | Ignored while tax toggle OFF |
| Customer returns | `Enable customer return requests` | **OFF** (default) | No return request buttons/flows |
| Return window (days) | `Return window days` | unused if returns OFF | Only applies when returns ON |
| Contact email | `Contact email` | empty | Hide email in footer/contact CTAs |
| Contact phone | `Contact phone` | empty | Hide phone |
| Address line | `Address line` | empty | Hide address block |
| WhatsApp number / message | WhatsApp fields | empty | Hide WhatsApp button |
| Instagram / YouTube / Facebook / X / LinkedIn | Social URL fields | empty | Hide that icon only |

**You decide when to turn tax/returns ON** — usually after CA/lawyer approval.

---

## 3. Policy & content pages (Admin → Pages / FAQ)

These are **your** legal and help texts. The platform may keep some slugs **withheld** from the public site until you approve.

| Page / topic | Typical slug | Who writes copy | Public until you approve? |
|---|---|---|---|
| Shipping policy | `shipping` | Client | Often withheld |
| Returns / refunds policy | `returns` | Client | Often withheld + returns toggle |
| Warranty | `warranty` | Client | Often withheld |
| FAQs | `faqs` or FAQ resource | Client | Often withheld / admin FAQs |
| Terms & conditions | `terms` (if used) | Client | Publish when ready |
| Privacy policy | `privacy` | Client | Publish when ready |
| About | `about` | Client | When ready |
| Contact page body | `contact` | Client + Settings contact fields | Form can work with Settings only |

**Footer / nav rule:** Links to withheld or empty policies should **not** appear. Customers should not hit accidental 404s from the menu.

When you are ready to publish a policy:

1. Paste final lawyer-approved HTML/text in Admin → Pages.  
2. Mark page active.  
3. Ask your implementer to remove that slug from `withheld_public_pages` (or use the agreed admin control if provided).  
4. Turn on related Settings toggles (e.g. returns) only if the policy matches the product behaviour.

---

## 4. Catalogue you own (Admin → Products / Categories / Brands)

| Item | You provide | Notes |
|---|---|---|
| Categories | Names, order, icons optional | Your tree |
| Brands | Manufacturer names + logos you have rights to | Not “other sellers” — labels only |
| Products | Name, SKU, price, MRP, stock, description | Original copy preferred |
| Images | Photos you may use commercially | Rights are your responsibility |
| Variants | Color, finish, size, each price/stock/images | One product, many sellable options |
| SEO title/description | Per product optional | Better Google results if filled |
| HSN / tax class / per-product tax % | Optional | Only if your tax setup needs them; global tax toggle still gates checkout tax |

**Empty catalogue:** Shop shows an honest empty state — not fake products.

---

## 5. Payments (Razorpay) — you configure keys

| Environment | What you set | Where |
|---|---|---|
| Test | Test Key ID + Secret (+ webhook secret) | Server `.env` — **never** commit to Git |
| Live | Live Key ID + Secret + webhook secret | Production `.env` only after go-live decision |

See **`docs/RAZORPAY_SETUP_GUIDE.md`** (step-by-step).

Platform behaviour:

- Keys missing + fake disallowed → checkout explains payment unavailable (no false “paid”).  
- You do not need to fill policies before taking test payments.

---

## 6. Store identity & homepage (optional polish)

| Area | Admin | Optional? |
|---|---|---|
| Logo / brand name | Theme + config / media | Usually set at launch |
| Hero slides | Hero slides resource | Hide section if none |
| Homepage blocks / sections | Homepage resources | Empty = hide |
| Newsletter | Works with subscriber list | Optional marketing |
| Coupons | Coupon resource | Optional |

---

## 7. What engineering will NOT block on

- Your GST number, HSN list, or final tax %  
- Final return window days  
- Final shipping rate card  
- Lawyer-approved Terms/Privacy/Shipping/Returns  
- Live Razorpay KYC completion  
- Final product photography  

The site must run, accept configuration later, and sell when **you** put products + (test/live) payment keys.

---

## 8. Client checklist (print)

- [ ] Staff admin login + MFA working  
- [ ] Settings: contact methods I want public  
- [ ] Settings: shipping/tax/returns left OFF until approved **or** filled correctly  
- [ ] Categories + brands created  
- [ ] First real products + images (+ variants if needed)  
- [ ] Razorpay **test** keys on staging  
- [ ] One test order paid  
- [ ] Policy pages drafted offline  
- [ ] Lawyer/CA sign-off  
- [ ] Policies published + toggles ON if required  
- [ ] Razorpay **live** keys + webhook on production  
- [ ] HTTPS domain live  

---

## 9. Support rule after handover

| Issue | Who |
|---|---|
| “I don’t know my tax rate yet” | You — leave tax OFF; site works |
| “Footer still shows Returns but I didn’t enable it” | Engineering bug — report |
| “Checkout crashes with tax off” | Engineering bug — report |
| “Need copy for Returns policy” | You / your lawyer — not auto-generated by the app |

---

*This file is the contract: **client-owned content is optional; absence is silent and safe.***
