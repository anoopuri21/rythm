# C6 — Razorpay **Test** checkout on your real domain

**Goal:** One full buy path with **real Razorpay Test API** (fake money, real integration).  
**Not yet:** Live keys / real customer charges (that is **C7**, explicit owner go-live only).

You chose: **Test mode** · account exists · keys not generated yet · **production domain**.

---

## 0. Safety rules (read once)

| Rule | Why |
|---|---|
| Use **`rzp_test_…` keys only** on the domain until C7 | Live keys move real money |
| `RAZORPAY_ALLOW_FAKE_PAYMENTS=false` | Never simulate paid on prod |
| Never paste Key Secret into GitHub / chat / this repo | Rotate if leaked |
| Only **Key ID** is public (checkout.js) | Secret stays server `.env` |
| Webhook needs **HTTPS** public URL | Matches your production domain |

App already implements: server order create, signature verify, webhook HMAC, markPaid once, stock decrement, cart clear, retry payment.

---

## 1. Generate Test API keys (Dashboard)

1. Open [https://dashboard.razorpay.com](https://dashboard.razorpay.com) and sign in.  
2. Top bar: turn **Test Mode ON** (orange “Test Mode”).  
3. **Account & Settings** → **API Keys** (or **Developers → API Keys**).  
4. **Generate Test Key** (if none).  
5. Copy into a password manager:
   - **Key ID** → starts with `rzp_test_`
   - **Key Secret** → shown **once**

Do **not** generate Live keys for this step.

---

## 2. Put keys on the **production host** `.env`

SSH / cPanel file manager → project root `.env` (never commit):

```env
RAZORPAY_KEY_ID=rzp_test_xxxxxxxx
RAZORPAY_KEY_SECRET=your_test_secret_here
RAZORPAY_WEBHOOK_SECRET=
RAZORPAY_ALLOW_FAKE_PAYMENTS=false
```

Also confirm:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://YOUR-REAL-DOMAIN
SESSION_SECURE_COOKIE=true
```

Apply config:

```bash
cd /path/to/rythm
php artisan config:clear
php artisan config:cache   # if you normally cache config in prod
php artisan razorpay:verify
php artisan razorpay:verify --ping
```

Expected:

- Key ID prefix `rzp_test_…`
- `ALLOW_FAKE: false`
- `isConfigured: yes` / `Payment mode: razorpay`
- `--ping` → **API auth OK**

If verify fails: wrong secret, Test/Live mismatch, or config still cached old values.

---

## 3. Webhook (strongly recommended)

1. Dashboard still in **Test Mode**.  
2. **Developers → Webhooks → Add endpoint**:

```text
https://YOUR-REAL-DOMAIN/payment/razorpay/webhook
```

3. Enable events (minimum):
   - `payment.authorized`
   - `payment.captured`
   - `order.paid`
4. Save → copy **Webhook secret** → `.env`:

```env
RAZORPAY_WEBHOOK_SECRET=paste_from_dashboard
```

5. `php artisan config:clear` (and `config:cache` if used).  
6. `php artisan razorpay:verify` → Webhook secret: **yes**.

App routes (already live in code):

- `POST /payment/razorpay/callback` — browser return  
- `POST /payment/razorpay/webhook` — async HMAC  

---

## 4. Full-flow manual smoke (customer path)

Use a **customer** account (not admin), real catalogue product with stock & price &gt; 0.

| # | Step | Pass if |
|---|---|---|
| 1 | Shop → PDP → pick variant if any → Add to cart | Badge / cart line correct |
| 2 | Cart → Proceed to checkout (logged in) | Checkout wizard |
| 3 | Address → continue | Pay step shows Razorpay copy (not “Payment unavailable”) |
| 4 | **Pay ₹… securely** | Razorpay Checkout window opens |
| 5 | Test card (below) → success | Signed success page + order number |
| 6 | Account → Orders | Paid / confirmed |
| 7 | Admin → Orders | Same order, payment captured, line options if variant |
| 8 | Stock | Variant or product stock decreased by qty |
| 9 | Razorpay Dashboard (Test) → Payments | Payment appears |
| 10 | (Optional) Cancel modal once → order unpaid → **Retry payment** | Second attempt works |

### Razorpay test card

| Field | Value |
|---|---|
| Card | `4111 1111 1111 1111` |
| Expiry | Any future |
| CVV | Any 3 digits |
| OTP / name | As Test UI asks |

UPI/netbanking test methods: see Razorpay docs “Test payment methods” for current values.

---

## 5. Failure triage

| Symptom | Check |
|---|---|
| Pay button disabled / “not available” | Keys empty or config not cleared |
| Popup never opens | Console errors; mixed content; checkout.js blocked; Key ID wrong |
| “Invalid signature” | Key Secret mismatch vs Key ID |
| Webhook 400 | Wrong webhook secret; body altered by proxy |
| Amount mismatch | Server total vs gateway — don’t trust client amounts |
| Order unpaid after success UI | Webhook/callback; run reconcile if ops uses it |
| Fake gateway on prod | `ALLOW_FAKE` must be false; fix env |

Logs: `storage/logs/laravel.log` — search `Razorpay` (no secrets logged by design).

---

## 6. What we do **not** do in C6

- ❌ `rzp_live_` keys  
- ❌ Real customer charges  
- ❌ Committing `.env`  
- ❌ Disabling webhook signature verification  
- ❌ Phase 18 full launch programme without owner command  

When Test smoke is green, **C7** = Live keys + go-live checklist (separate explicit step).

---

## 7. Related docs

- Long form: `docs/RAZORPAY_SETUP_GUIDE.md`  
- Buy path smoke: `docs/BUY_PATH_SMOKE_CHECKLIST.md`  
- Security model: `docs/payment-security.md`  
- Env templates: `.env.production.example`, `.env.staging.example`  

---

*Owner: paste secrets only into server `.env` / host secrets store — never into Arena chat or Git.*
