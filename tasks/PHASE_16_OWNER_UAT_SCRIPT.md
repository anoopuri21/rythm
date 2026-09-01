# Phase 16 — Owner UAT Script (Copy-Safe, Numbered)

**Purpose:** Focused client UAT for the MVP release candidate — browse, search, cart, checkout, test payment, order, invoice, account and admin essentials.
**Who:** Owner (non-technical friendly). Do every step in order and tick the checkbox as you finish it.
**Rule:** This script verifies behavior; it is not production sign-off. Agent 0 records go/no-go only after this evidence plus the Phase 12 owner gates (AS-H011/AS-H012) report green.
**Time needed:** about 45–60 minutes. Use an incognito/private window for the customer journeys.

## 0. Preconditions (owner confirms once)

- [ ] Site runs on the UAT database (`rhythm_db`, MySQL 8.4.3) with `APP_ENV=local`/UAT settings.
- [ ] Razorpay is in **test mode** (`RAZORPAY_ALLOW_FAKE_PAYMENTS` stays false; real keys are never pasted anywhere except the server `.env`).
- [ ] You have one test customer account (register one below if needed) and the admin/staff login with MFA.

## 1. Browse and search (customer)

1. [ ] Open `/` — hero, categories, offer strip, products and footer all render; no browser console errors (press F12 → Console).
2. [ ] Open `/shop` — products load in a grid; try a category filter, a brand filter and the price sort; pagination appears past 12 products.
3. [ ] Use the search box in the header with `guitar` — suggestions/results appear; open a result.
4. [ ] Open any product page — gallery images, price, stock text, description, reviews and Q&A sections render.

## 2. Cart and checkout (customer)

5. [ ] On the product page choose a variant (if shown), set quantity 2, press **Add to cart** — cart badge updates.
6. [ ] Open the cart drawer; change quantity to 1; confirm the total updates immediately.
7. [ ] Press checkout — you are redirected to login (guest checkout is intentionally disabled). Log in with the test customer.
8. [ ] In checkout, add a **new address** with a valid 6-digit pincode and continue.
9. [ ] Try an invalid coupon code — a clear rejection message appears and totals do not change.
10. [ ] Continue to payment — the Razorpay test checkout opens with the correct order amount (₹ amount ×100 paise is handled internally).

## 3. Test payment and order (customer)

11. [ ] Pay with Razorpay **test card** `4111 1111 1111 1111`, any future expiry, any CVV — success redirects to the order success page.
12. [ ] Open **My Account → Orders** — the order shows as paid/confirmed with correct items and totals.
13. [ ] Open the invoice from the order page — it renders within 15 minutes of the link being created (links expire; regenerate from the order page).
14. [ ] Press **Cancel order** on the order page — cancellation succeeds and a "refund pending" style message appears (no instant refund is claimed).

## 4. Account surfaces (customer)

15. [ ] Notifications page lists the order confirmation entry; mark one as read/unread works.
16. [ ] Addresses: set another address as default; delete the first address; both succeed.
17. [ ] Change the profile name and save; change the password and log in again with the new password.

## 5. Admin essentials (staff)

18. [ ] Log in to `/admin` with the staff account — MFA prompt appears and passes.
19. [ ] Open the order created above — status, payment and items match the customer view.
20. [ ] Edit one product's price by ₹1, save, and confirm the storefront shows the new price (change it back afterwards).
21. [ ] Confirm a customer account **cannot** open `/admin` (logout of admin, log in as customer, visit `/admin` — access is denied).

## 6. Responsive viewports (customer + admin)

Open browser device toolbar (F12 → device icon) and repeat journey steps 1, 2, 6, 10 quickly at each size; note console errors, horizontal scrolling or overlapping elements:

- [ ] 1440×900 (desktop)
- [ ] 768×1024 (tablet)
- [ ] 390×844 (mobile)
- [ ] 360×800 (small mobile)

## 7. Result recording

22. [ ] Record for each journey: PASS / FAIL + one-line note and screenshot of any failure.
23. [ ] Send the results to Agent 0 with the tested commit SHA (`git rev-parse HEAD` output).

**If anything fails:** stop, note the exact step and screenshot, and report — do not retry payments repeatedly and do not change data directly in the database.
