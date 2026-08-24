# RYTHME — Scale-Up Architecture Roadmap (Large-Scale E-Commerce)

> Senior-engineer gap analysis · 2026-08-13 · Applies to `feature/dev` (PR #22)
> Goal: full scenario coverage — user journey, admin operations, infrastructure —
> like a production Indian e-commerce platform (Bajaao/Amazon-scale thinking).

---

## 1. Current state (audit result)

### ✅ Done
Auth (login/register/logout, throttle, intended redirect) · Account dashboard (profile/password/addresses/orders list) · Catalog (products/categories/brands/variants) · Shop (Livewire filters) · Product detail (gallery/price box/variants/related/JSON-LD) · Cart (session+user, drawer/badge/page, merge-on-login) · Checkout 2-step (address → Razorpay, fake-gateway fallback) · Order creation (snapshots, stock lock, audit history, confirmation email) · Wishlist · CMS (dynamic pages + polymorphic SEO) · Homepage sections manager · Admin: Product/Category/Brand/HomepageSection/Page resources · Security (headers, crypto verify, audits) · Footer 5-col global

### ❌ Gaps (large-scale e-commerce ke liye missing)

| # | Gap | Impact | Priority |
|---|---|---|---|
| 1 | **Order tracking page** (timeline) | User ko pata nahi order kahan hai — core UX | 🔴 HIGH |
| 2 | **Admin OrderResource** | Admin order manage nahi kar sakta — operations dead | 🔴 HIGH |
| 3 | **Status update emails** (shipped/delivered) | User notified nahi hota | 🔴 HIGH |
| 4 | **Password reset** (forgot password) | Login stuck users locked out | 🔴 HIGH |
| 5 | **Order detail page + invoice** | User order contents dekh nahi sakta | 🟠 MED |
| 6 | **Order cancellation** (user) | Returns/cancel flow missing | 🟠 MED |
| 7 | **ContactMessage admin resource** | Messages stored but never seen | 🟠 MED |
| 8 | **CustomerResource** | Admin customers nahi dekh sakta | 🟠 MED |
| 9 | **Reviews/ratings** | Social proof missing | 🟠 MED |
| 10 | **Coupons** | Promotions impossible | 🟡 LOW |
| 11 | **Email verification** | Account security incomplete | 🟡 LOW |
| 12 | **Dashboard widgets** (revenue/low-stock) | Admin blind to business health | 🟠 MED |
| 13 | **Sitemap.xml** | SEO crawl gap | 🟠 MED |
| 14 | **Newsletter admin resource** | Subscribers unmanaged | 🟡 LOW |
| 15 | **GST/tax + shipping rules** | Money correctness | 🟠 MED |
| 16 | **Queue worker for prod** | Emails blocked if sync | 🟠 MED |

---

## 2. Target architecture (scenarios — admin se user tak)

```
┌────────────────────────────── STOREFRONT (user) ──────────────────────────────┐
│ Browse (shop/filters/drawer) → Product (gallery/variants/reviews)             │
│ → Cart (drawer/page) → Checkout (address → payment) → Order placed            │
│ → Order detail + TRACKING timeline (placed→confirmed→shipped→delivered)       │
│ → Invoice download · Cancel (if pending) · Review (verified purchase)         │
│ Account: profile · password reset/change · addresses · orders · wishlist      │
│ Email journey: confirm → shipped → delivered → review invite                  │
└───────────────────────────────────────────────────────────────────────────────┘
┌────────────────────────────── ADMIN (Filament) ───────────────────────────────┐
│ SHOP: Products · Categories · Brands                                          │
│ HOME: Dashboard (revenue/orders/low-stock widgets) · Homepage sections        │
│ CONTENT: Pages (+SEO) · Media library                                         │
│ COMMERCE: Orders (list/filter/detail/status actions/print) · Customers        │
│           Coupons · Reviews (moderation)                                      │
│ COMMUNICATION: Contact messages (new/read/replied) · Newsletter subscribers   │
│ SETTINGS: Site settings (shipping rules, GST, payment keys, SEO defaults)     │
└───────────────────────────────────────────────────────────────────────────────┘
┌────────────────────────────── INFRASTRUCTURE ─────────────────────────────────┐
│ Queue worker (database driver) · failed_jobs retry · sitemap.xml + robots.txt │
│ Caching (shop pages, catalog tree, settings) · error pages · audits clean     │
│ Security headers · CSRF · crypto-verified webhooks · rate limits              │
└───────────────────────────────────────────────────────────────────────────────┘
```

---

## 3. Phased task plan (dependency-ordered)

### PHASE 1 — Order Management Core 🔴 (user ka main concern)
| Task | Deliverable | DoD |
|---|---|---|
| **order-tracking** | `/orders/{order}/track` — timeline from `order_status_history` (placed/confirmed/shipped/delivered icons), order summary, address, payment status, login-required + owner-guarded; order detail view (items table + totals); guest lookup via order number + email (signed) | tracking page renders real history; tests |
| **order-status-emails** | `OrderStatusMail` (shipped/delivered/cancelled) queued via `Queue::route` on admin status change | mail assertions in tests |
| **admin-orders-resource** | OrderResource: list (order no/date/customer/total/status badges), filters (status/payment/date), detail view (items, addresses, payments, history timeline), actions (processing→shipped→delivered→cancelled) with history + emails, print/invoice view | CRUD/actions tested, emails queued |

### PHASE 2 — Account & Auth Completion 🔴
| Task | Deliverable | DoD |
|---|---|---|
| **password-reset** | Forgot password → email link → reset form (Laravel built-ins, design-system UI, throttled) | reset flow tests |
| **email-verification** | Register → verification email → verified badge; verified-only reviews later | verify flow tests |
| **user-order-cancel** | Cancel button on pending orders (policy: owner + status pending), stock restore, email | cancel tests |
| **order-invoice** | Printable invoice page (HTML, order snapshots) + download | invoice renders |

### PHASE 3 — Reviews & Social Proof 🟠
| Task | Deliverable | DoD |
|---|---|---|
| **reviews-system** | `reviews` table (polymorphic or product-scoped), star rating + comment, verified-purchase gate, product page section + summary (avg, count), paginated | review tests |
| **admin-reviews** | ReviewResource — moderation (approve/reject/delete), badges, filters | moderation tests |

### PHASE 4 — Promotions 🟠
| Task | Deliverable | DoD |
|---|---|---|
| **coupons-system** | `coupons` table (code, type percent/fixed, min order, max discount, expiry, usage limit), CouponService (validate/apply server-side), checkout apply UI + order discount snapshot | coupon tests |
| **admin-coupons** | CouponResource CRUD + usage stats | admin tests |

### PHASE 5 — Admin Operations Complete 🟠
| Task | Deliverable | DoD |
|---|---|---|
| **admin-customers** | CustomerResource: users list (orders count, spend, joined), addresses relation, order history | tests |
| **admin-contact-messages** | ContactMessageResource: list/filter (new/read/replied), mark workflow, delete | tests |
| **admin-newsletter** | NewsletterSubscriberResource: list + export CSV | tests |
| **admin-dashboard-widgets** | StatsOverview (revenue today/7d, orders, AOV), low-stock table, latest orders | widgets render |
| **site-settings** | Settings model + resource (shipping fee, free-above threshold, GST %, contact info, social links) consumed via cached config | settings tests |

### PHASE 6 — Infrastructure & Scale 🟡
| Task | Deliverable | DoD |
|---|---|---|
| **seo-sitemap** | Dynamic sitemap.xml (pages/products/categories) + robots.txt routes | sitemap tests |
| **prod-queue** | QUEUE_CONNECTION=database docs + supervisor command, `queue:work` in `composer run dev`, failed-jobs retry docs | verified |
| **shop-caching** | Tagged cache for shop page queries (per-category), invalidate on product save (observer) | cache tests |
| **gst-shipping** | GST% + shipping rules from settings applied in OrderService totals | totals tests |
| **error-pages** | Custom 404/500 pages (design system) | pages render |
| **final-review** | Full regression: 150+ tests, build, security scan, handover doc | all green |

---

## 4. Automation rules (har task ke baad)

1. `npm run build` + `APP_BASE_PATH=... php artisan test` → green
2. `git commit` + `git push origin feature/dev` (PR #22 auto-updates — NO new branches/PRs)
3. tasks.json: status done + note
4. Security checklist per AGENT_RULES §11 (ownership checks, validation, crypto)
5. Screenshot for user (rythme-design-snapshots/)
