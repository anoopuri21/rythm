# MilesWeb Hosting Deployment Guide

This guide details the steps to deploy **Rhythm Exports** (Laravel 13 + Blade + Tailwind 4 + Filament v3 + MySQL) on **MilesWeb Business Hosting** (cPanel).

---

## 📌 Phase 1: Deployment on Sub-domain (`https://rhythm.vsinfosys.in`)

### Step 1: Sub-domain Creation in cPanel
1. Log in to your **MilesWeb cPanel**.
2. Go to **Domains** -> **Subdomains**.
3. Create sub-domain: `rhythm.vsinfosys.in`.
4. Set Document Root: `public_html/rhythm/public` (or `/home/username/rhythm/public`).
   > ⚠️ **CRITICAL:** The document root MUST point directly to the Laravel `public/` folder, NOT the root directory of the repository.

### Step 2: Database Setup
1. In cPanel, navigate to **MySQL® Databases**.
2. Create a database: e.g. `vsinfosys_rhythm_uat`.
3. Create a MySQL user and set a strong password.
4. Assign the user to the database with **ALL PRIVILEGES**.

### Step 3: Code Upload
1. Clone or upload the repository to the folder specified (e.g., `/home/username/rhythm`).
2. If uploading via FTP/FileManager:
   - Exclude `node_modules`, `vendor`, `.git`, `.env`, and local storage/logs.

### Step 4: Environment Configuration (`.env`)
Create `.env` in the root folder (`/home/username/rhythm/.env`):

```ini
APP_NAME="Rhythm Exports"
APP_ENV=production
APP_KEY=base64:... # Generate via 'php artisan key:generate' or paste key
APP_DEBUG=false
APP_URL=https://rhythm.vsinfosys.in

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vsinfosys_rhythm_uat
DB_USERNAME=vsinfosys_rhythm_user
DB_PASSWORD="your_secure_password"

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

CACHE_STORE=file
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public

MAIL_MAILER=smtp
MAIL_SCHEME=tls
MAIL_HOST=mail.vsinfosys.in
MAIL_PORT=587
MAIL_USERNAME=noreply@vsinfosys.in
MAIL_PASSWORD="mail_password"
MAIL_FROM_ADDRESS="noreply@vsinfosys.in"
MAIL_FROM_NAME="${APP_NAME}"

RAZORPAY_KEY_ID=your_test_key_id
RAZORPAY_KEY_SECRET=your_test_key_secret
RAZORPAY_WEBHOOK_SECRET=your_test_webhook_secret
RAZORPAY_ALLOW_FAKE_PAYMENTS=false

VITE_APP_NAME="${APP_NAME}"
```

### Step 5: Terminal / SSH Setup Commands
If SSH access is enabled on MilesWeb:
```bash
cd /home/username/rhythm
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
*If SSH is disabled*, compile assets locally (`npm run build`), install composer vendor locally (`composer install --no-dev`), zip, upload, and run migration/cache via cPanel Terminal or PHP scripts.

### Step 6: SSL & HTTPS Setup
1. In cPanel, open **SSL/TLS Status** or **Let's Encrypt SSL**.
2. Run AutoSSL for `rhythm.vsinfosys.in`.
3. Ensure HTTPS redirect is enabled.

### Step 7: Cron Job Setup (Scheduler & Bounded Queue)
In cPanel **Cron Jobs**, add a job to run every minute:
```bash
* * * * * /usr/local/bin/php /home/username/rhythm/artisan schedule:run >> /dev/null 2>&1
```
*(Replace `/usr/local/bin/php` with the exact path to PHP 8.3/8.4 binary on MilesWeb server, e.g., `/usr/bin/php83`)*

---

## 🚀 Phase 2: Production Domain Cutover (`https://rhythmeports.com`)

Once all testing passes on `https://rhythm.vsinfosys.in`:

1. **Domain Setup:**
   - Add/Point `rhythmeports.com` to MilesWeb primary domain or Addon Domain.
   - Set Document Root to `/home/username/rhythm_prod/public`.
2. **Production Database:**
   - Create production DB (e.g., `rhythm_prod_db`) & user.
3. **Environment Update (`.env`):**
   - Update `APP_URL=https://rhythmeports.com`.
   - Update `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.
   - Set live `RAZORPAY_KEY_ID`, `RAZORPAY_KEY_SECRET`, `RAZORPAY_WEBHOOK_SECRET`.
4. **Optimization:**
   - Run `php artisan optimize:clear` and re-cache (`config:cache`, `route:cache`, `view:cache`).
5. **SSL:**
   - Issue SSL for `rhythmeports.com` via cPanel AutoSSL.

---

## 🛠 Handover Credentials & Panel Access
- **Admin Panel URL:** `https://rhythm.vsinfosys.in/admin`
- **Default Admin Login:** `admin@rythme.test` / `admin1234` (Change password upon initial login)
