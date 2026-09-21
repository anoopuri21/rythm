# Buy-path smoke checklist (C4 / W2)

**Use on:** local with fake pay **or** staging with Razorpay **test** keys  
**Do not** use live keys until Phase 18 / owner go-live.

---

## A. Empty states

- [ ] Open `/cart` with empty cart → clear empty message + **Browse the shop** (+ wishlist or sign-in).  
- [ ] Open cart drawer empty → same tone, no demo jokes required.  
- [ ] Open `/wishlist` while logged in empty → empty wishlist copy + shop CTA.  
- [ ] Guest hitting wishlist → redirected to login (existing).

## B. Add → cart → checkout gate

- [ ] Guest: PDP add to cart → badge updates.  
- [ ] Guest: cart shows **Sign in to checkout** (not a broken checkout page).  
- [ ] Login with `?intended=/checkout` → lands on checkout.  
- [ ] Guest cart merges into account cart after login.  
- [ ] Cart lines with variants show option summary + SKU.  
- [ ] OOS lines are removed/blocked with clear messaging.

## C. Checkout money display

- [ ] Tax rules OFF → no Tax row (or ₹0 not charged).  
- [ ] Shipping fee 0 → **Free**.  
- [ ] Coupon apply/remove works; error copy readable.  
- [ ] Policy links under pay only if CMS pages are public.

## D. Payment modes

| Env | Keys | `ALLOW_FAKE` | Expected UI |
|---|---|---|---|
| local | empty | true | Simulate pay button; amber dev notice |
| local/staging/prod | empty | false | Pay **disabled**; “not available” message; no charge |
| any | `rzp_test_…` | false | Razorpay window; real test charge path |
| production | `rzp_live_…` | **false** | Live money — owner only |

- [ ] Without keys + fake off: button disabled, placeOrder does not create paid order.  
- [ ] With test keys: test card path (see `RAZORPAY_SETUP_GUIDE.md`).  
- [ ] Failed payment: error on checkout; order page **Retry payment** panel for unpaid/failed pending.

## E. Success

- [ ] Paid → signed success URL.  
- [ ] Account order list shows order.  
- [ ] Admin order shows payment + items (variant options if any).  
- [ ] Stock reduced for the sold variant/product.

## F. Webhook (staging+)

- [ ] Dashboard webhook → `https://{host}/payment/razorpay/webhook`  
- [ ] Events: authorized / captured / order.paid  
- [ ] Secret in `.env` · `config:clear`

---

*Owner guide for keys: `docs/RAZORPAY_SETUP_GUIDE.md`.*
