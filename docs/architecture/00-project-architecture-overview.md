# SOUNDSCAPE — Rythme Music Store · Architecture Overview

> Codename **SOUNDSCAPE**. Public brand: **Rythme Music Store**. Reference: bajaao.com (inspiration only — content is unique).
> Repo: `anoopuri21/rythm` · Branch: `arena/019fe1bf-rythm`

---

## 1. Stack (locked)

Laravel 13.24.0 (PHP 8.4) · Blade · Tailwind 4 · Alpine.js · GSAP/Lenis/Swiper/CountUp
Filament v3.3.54 (admin) · Livewire 3.8.3 · Mary UI 2.9.9 (legacy components)
TiptapEditor 3.5 · Spatie MediaLibrary 11.23 · Razorpay (test mode, future)

## 2. Page flow

```
Home → Admin Home (Filament dashboard) → Shop (list + filters) → Product Detail
     → Cart → Checkout → Payment (Razorpay) → Wishlist → About / Contact
```

## 3. Homepage — 10 base + 5 cinematic sections

| # | Section | Blade partial | Status |
|---|---|---|---|
| 1 | Hero (Swiper slider, dark, parallax) | `_hero` | ✅ done |
| 2 | Featured Categories (10 cards) | `_categories` | ✅ done |
| 3 | Best Sellers (dark, filter tabs) | `_bestsellers` | ✅ done |
| 4 | Why Rythme (6 USP cards, pinned media) | `_why-rythme` | ✅ done |
| 5 | Brand Showcase (marquee) | `_brands` | ✅ done |
| 6 | Numbers (CountUp counters, parallax) | `_numbers` | ✅ done |
| 7 | New Arrivals (bento grid) | `_new-arrivals` | ✅ done |
| 8 | Deals (countdown banner) | `_deals` | ✅ done |
| 9 | Latest Stories (journal cards) | `_stories` | ✅ done |
| 10 | Footer + Newsletter (locked) | `_footer` | ✅ done |
| 11 | s11 · Video Showcase (dark, modal) | `_video-showcase` | ✅ done (2026-08-09) |
| 12 | s12 · Testimonials (Swiper, dark) | `_testimonials` | ✅ done |
| 13 | s13 · Comparison table | `_comparison` | ✅ done (2026-08-09) |
| 14 | s14 · UGC gallery (#RythmeFamily) | `_ugc` | ✅ done (2026-08-09) |
| 15 | s15 · FAQ accordion + JSON-LD | `_faq` | ✅ done (2026-08-09) |
| — | s16 · Cinematic scroll FX (progress bar, reveals, parallax, Lenis) | layout + `js/modules/motion.js` | ✅ done |

Cinematic system: Lenis smooth scroll · GSAP ScrollTrigger reveals (`reveal-section`, `[data-reveal]`) · image clip reveals (`image-reveal`) · parallax (`parallax-media`, `[data-parallax]`) · scroll progress bar (`.scroll-progress`) · hero parallax · pinned why-section media · `prefers-reduced-motion` respected.

## 4. Navigation & Drawer (Amazon-style) — PENDING TASK

Target (task: `shop-list`): Amazon-style drawer/mega menu — "Shop by Category" drawer from navbar:
- Drawer left-side panel, 2-level nav: 10 categories → subcategories (Guitars → Acoustic/Electric/Bass/Classical/Ukulele, etc.)
- Backdrop blur, keyboard (Esc) close, focus trap, aria-expanded
- Toggle in navbar; keep existing links working

## 5. Footer — 5-column target (currently 4 + newsletter strip; LOCKED until redesign task)

Current: brand + socials · Shop · Customer care · About Rythme + newsletter strip on top.
Target 5-column (future task, NOT ad-hoc): Shop · Customer care · Company · Top brands · Help + payment/trust badges row.

## 6. Admin (Filament v3, multi-group sidebar)

Existing: `app/Providers/Filament/AdminPanelProvider.php` (panel: `admin`).
Target groups (task: `admin-homepage-filament` + follow-ups):
```
HOME     → Dashboard, Homepage sections (10+5, TiptapEditor content)
SHOP     → Products, Categories, Brands, Deals, Stories
COMMERCE → Orders, Customers, Reviews, Wishlists
MEDIA    → Media library (Spatie), Banners
SETTINGS → Settings (site, shipping, payment)
```
Rules: heading/title → TextInput; body/content → TiptapEditor; images → SpatieMediaLibrary.

## 7. Directory map (current)

```
app/Http/Controllers/          HomeController, NewsletterSubscriptionController
app/Models/                    User, NewsletterSubscriber
app/Providers/Filament/        AdminPanelProvider
resources/views/components/    navbar, product-card
resources/views/home/          _*.blade.php (16 partials) + index
resources/views/layouts/       app.blade.php
resources/js/modules/          carousels.js, motion.js, ui.js
config/rythme.php              site config (video URL, etc.)
docs/                          AGENT_RULES_STRICT.md, architecture/
tasks/tasks.json               task source of truth
```

## 8. Data & media (current state)

- Products/categories/deals/stories/testimonials: hardcoded arrays in Blade partials (DB models = future tasks).
- DB: SQLite local (tests `:memory:`), MySQL for prod (README).
- Newsletter: validated + honeypot + rate-limited POST.
- Razorpay + Cloudinary: planned (Razorpay already installed; Cloudinary removed from composer — illuminate 13 incompatible — re-add only when media pipeline lands via MediaLibrary).
