# Razorpay setup guide — Rhythm Exports / Rythme

**Audience:** Owner / client (non-technical OK)  
**Modes:** Test first → Live only when you decide to take real money  
**Never** paste Key Secret or Webhook Secret into GitHub, WhatsApp group chats, or this repo.

---

## A. What you need before starting

1. A Razorpay account → [https://dashboard.razorpay.com](https://dashboard.razorpay.com)  
2. Business KYC as required by Razorpay (for **Live** mode)  
3. Access to your website hosting panel (cPanel / `.env` editor) **or** a developer who can set env vars  
4. Your site URL with **HTTPS** (required for Live webhooks), e.g. `https://www.yourdomain.com`

---

## B. Test mode setup (do this first)

### Step 1 — Open Test mode

1. Log in to Razorpay Dashboard.  
2. Top switch: ensure **Test Mode** is ON (usually orange “Test Mode” badge).  
3. Everything you create now is fake money — safe to practice.

### Step 2 — Create Test API keys

1. Go to **Account & Settings** → **API Keys** (or **Developers → API Keys**).  
2. Click **Generate Test Key** if none exist.  
3. Copy:
   - **Key ID** (starts with `rzp_test_...`)  
   - **Key Secret** (shown once — store in a password manager)

### Step 3 — Put keys on the server (not in Git)

On the server `.env` file (staging or local):

```env
RAZORPAY_KEY_ID=rzp_test_xxxxxxxx
RAZORPAY_KEY_SECRET=xxxxxxxxxxxxxxxx
RAZORPAY_WEBHOOK_SECRET=
RAZORPAY_ALLOW_FAKE_PAYMENTS=false
```

Notes:

- `RAZORPAY_ALLOW_FAKE_PAYMENTS` must stay **`false`** on any shared/staging/prod host you care about.  
- Local laptop without keys may use a fake gateway for developers only — not for client UAT.

After saving `.env`:

```bash
php artisan config:clear
# if you use config cache in prod:
php artisan config:cache
```

### Step 4 — Test webhook (recommended on staging)

1. Dashboard → **Developers → Webhooks** (Test mode).  
2. **Add endpoint** URL:

```text
https://YOUR-STAGING-DOMAIN/payment/razorpay/webhook
```

3. Select at least:
   - `payment.authorized`
   - `payment.captured`
   - `order.paid`  
   (Match what the app expects; these are the primary ones.)

4. Save. Copy the **Webhook secret**.  
5. Set on server:

```env
RAZORPAY_WEBHOOK_SECRET=whsec_or_dashboard_secret_here
```

6. `php artisan config:clear` again.

### Step 5 — One real test checkout on the website

1. Open the shop as a **customer** account (register if needed).  
2. Add a product (pick a variant if shown) → Cart → Checkout.  
3. Address → Pay.  
4. Razorpay popup should open.  
5. Use Razorpay **test card**:

| Field | Value |
|---|---|
| Card | `4111 1111 1111 1111` |
| Expiry | Any future month/year |
| CVV | Any 3 digits |
| OTP/name | As Razorpay test UI asks |

6. Success page should show order.  
7. Customer **Account → Orders**: status paid/confirmed.  
8. Admin **Orders**: same order, payment paid, stock reduced.

### Step 6 — If popup does not open

| Check | Action |
|---|---|
| Key ID empty | Fix `.env`, clear config |
| Browser console errors | Note error; blocked scripts / mixed HTTP |
| Site on `http://` only | Use https staging if scripts require secure context |
| Amount 0 | Product price must be &gt; 0 |
| Not logged in | Checkout requires login |

### Step 7 — Test failed payment / retry (optional)

1. Use a Razorpay documented failure test method if available, or cancel the modal.  
2. Order should stay unpaid/failed — **Retry payment** when the app shows it.  
3. Do not click pay 20 times — rate limits exist.

---

## C. Live mode setup (real money — only when ready)

### Step 1 — Complete Razorpay activation / KYC

Dashboard must allow **Live** keys.

### Step 2 — Switch dashboard to Live

Toggle **Live Mode**. Generate **Live** API keys (`rzp_live_...`).

### Step 3 — Production `.env` only

```env
RAZORPAY_KEY_ID=rzp_live_xxxxxxxx
RAZORPAY_KEY_SECRET=xxxxxxxx
RAZORPAY_WEBHOOK_SECRET=xxxxxxxx
RAZORPAY_ALLOW_FAKE_PAYMENTS=false
APP_ENV=production
APP_DEBUG=false
```

### Step 4 — Live webhook

Endpoint:

```text
https://YOUR-PRODUCTION-DOMAIN/payment/razorpay/webhook
```

Same event types as test. New webhook secret → update `.env`.

### Step 5 — Callback URL

Customer browser return uses:

```text
https://YOUR-PRODUCTION-DOMAIN/payment/razorpay/callback
```

No extra dashboard field always required for standard Checkout.js flow; ensure HTTPS and route is live.

### Step 6 — Go-live smoke (small amount)

1. One low-price real SKU or internal test product.  
2. Pay with your real card (small amount).  
3. Confirm order + admin + Razorpay dashboard payment.  
4. Refund from admin/Finance path or Razorpay dashboard per your process if it was only a test charge.

### Step 7 — Monitor

- Razorpay Dashboard → Payments  
- App: `php artisan payments:reconcile` (read-only report) on a schedule if ops uses it  
- Failed webhooks in Razorpay webhook logs  

---

## D. Security rules (non-negotiable)

1. **Never** commit `.env` or keys to Git.  
2. **Never** put Key Secret in frontend JavaScript (only Key ID is public in checkout).  
3. Webhook signature verification is done by the app — do not disable it.  
4. Production: `RAZORPAY_ALLOW_FAKE_PAYMENTS=false`.  
5. Rotate keys if they ever leak.  
6. Staff MFA on `/admin`.  

---

## E. What the app already does (you don’t build this)

- Creates Razorpay order for the **server-calculated** total  
- Verifies payment signature on callback  
- Verifies webhook HMAC  
- Marks order paid **once**, reduces stock, clears cart  
- Supports retry and refund flows in admin (Finance)  

You only supply keys + HTTPS + webhook URL.

---

## F. Quick copy-paste checklist

**Test**

- [ ] Test mode ON in dashboard  
- [ ] Test Key ID + Secret in staging `.env`  
- [ ] `ALLOW_FAKE_PAYMENTS=false`  
- [ ] Webhook URL + secret (staging)  
- [ ] config clear  
- [ ] Test card payment success  
- [ ] Admin order paid  

**Live**

- [ ] KYC / Live enabled  
- [ ] Live keys in **production** `.env` only  
- [ ] Production webhook  
- [ ] HTTPS OK  
- [ ] Fake payments false  
- [ ] One small live smoke payment  
- [ ] Monitoring plan  

---

## G. Helpful links

- Razorpay Dashboard: https://dashboard.razorpay.com  
- Test cards docs: Razorpay “Test Cards” documentation (dashboard Help)  
- App webhook path: `POST /payment/razorpay/webhook`  
- App callback path: `POST /payment/razorpay/callback`  

---

*Questions while setting up: tell your implementer which step number failed and whether you are on Test or Live (never send the secret in chat — say “secret is set” only).*
