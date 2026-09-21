# Plan — Remove Product Q&A (complete clean)

**Goal:** PDP “Questions & answers” and the full moderated product-question stack go away (storefront → admin → DB).  
**Non-goal:** Do **not** disturb reviews, coupons, contact messages, homepage FAQs, product-page FAQ block, cart/checkout/pay.

---

## 1. What is Product Q&A (in scope)

| Layer | Path |
|---|---|
| PDP | `resources/views/product/show.blade.php` → `<livewire:product-question-section>` |
| Livewire UI | `app/Livewire/ProductQuestionSection.php` + `resources/views/livewire/product-question-section.blade.php` |
| Service | `app/Services/ProductQuestionService.php` |
| Model | `app/Models/ProductQuestion.php` |
| Relations | `Product::questions()`, `User::productQuestions()` |
| Filament | `ProductQuestionResource` + `Pages/ManageProductQuestions` (`/admin/product-questions`) |
| Policy map | `AppServiceProvider` Gate for `ProductQuestion` → `InteractionPolicy` |
| Admin ACL | `AdminAccess` model map entry for `ProductQuestion` |
| Audit | `AdminAuditableObserver` list includes `ProductQuestion` |
| Feature tests | `tests/Feature/ProductQuestionTest.php` |
| Asserts elsewhere | `ProductPageTest` expects “Questions & answers” |
| Automation | `phase11-customer-experience.test.mjs`, `security-phase12-boundaries.test.mjs` (read Q&A Livewire) |
| Schema | table `product_questions` (+ admin list index migration) |

**Copy only (soften, not feature code):** about-page default promise/stats that advertise “Moderated Q&A”.

---

## 2. Explicitly out of scope (do not change behaviour)

| Keep | Why |
|---|---|
| **Reviews** (`Review`, `ReviewSection`, Filament Review resource) | Separate Phase 5 feature; still wanted |
| **`InteractionPolicy` + `INTERACTIONS_MANAGE`** | Still gates Reviews + Contact messages |
| **Contact messages** | Unrelated support inbox |
| **Homepage / CMS `Faq` model** | Different product (“Frequently asked questions” store FAQs) |
| **PDP FAQ accordion** (if present after Q&A block) | Store-level FAQs, not customer Q&A |
| **Combined historical migration** `2026_08_26_000001_…` | Also creates review columns — **never edit past migrations**; only add a **new** drop migration for `product_questions` |
| Cart, checkout, payment, variants, wishlist | Untouched |

---

## 3. Implementation steps (order)

1. **Storefront:** Remove Livewire include from `product/show.blade.php`.  
2. **Delete feature code:** Livewire class+view, Service, Model, Filament resource+page.  
3. **Detach wiring:**  
   - `Product` / `User` relations  
   - `AppServiceProvider` policy + auditable observe list  
   - `AdminAccess` `ProductQuestion` map row  
   - `AdminAuditableObserver` model list  
4. **DB:** New migration `drop_product_questions_table` (drop indexes safely then table). Do not roll back review half of old migration.  
5. **Tests:** Delete `ProductQuestionTest.php`. Update `ProductPageTest` (no Q&A assert). Adjust automation to only assert Review rate-limits / Livewire auth (remove question file reads).  
6. **Copy:** About defaults — drop Q&A marketing lines (reviews-only wording).  
7. **Docs:** MEMORY + short note in ARCHITECTURE/PRD that Product Q&A removed; Phase 5 history may still mention it as former scope.  
8. **Verify:** `rg ProductQuestion` / `product-question` clean in app+resources+tests (except historical tasks/docs if left as archive).  
9. **Push** branch.

---

## 4. Risk controls

- Migration is **additive drop only** → safe on existing MySQL; `migrate` on owner host.  
- Fresh `migrate:fresh` still runs old create then new drop → end state = no table.  
- Reviews admin menu and PDP review section unchanged.  
- No route entries dedicated to Q&A (Livewire-only) → no `routes/web.php` change expected.

---

## 5. Owner verify after pull

```bash
php artisan migrate --force
php artisan test --filter=ProductPageTest
php artisan test --filter=Review
# ProductQuestionTest must be gone / not run
rg -n "ProductQuestion|product-question-section|Questions &amp; answers" app resources tests --glob '!tasks/**'
```

Manual: open any PDP → no “Questions & answers”; `/admin/product-questions` → 404; Reviews still work.
