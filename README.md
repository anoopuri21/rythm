# Rythme Music Store

Rythme Music Store is a premium, cinematic e-commerce experience for musical instruments. The project includes a customer storefront, interactive product sections, Razorpay payments (test mode), and a Filament administration panel.

## Technology requirements

Install **Laragon Full** and ensure the following versions are available:

- PHP 8.2 or newer (PHP 8.3 is recommended)
- MySQL 8.0 or newer
- Composer 2
- Node.js 20 or newer and npm
- Apache or Nginx through Laragon

The required PHP extensions normally included with Laragon are: `curl`, `fileinfo`, `gd`, `intl`, `mbstring`, `mysqli`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, and `zip`.

Check the active tools from the Laragon Terminal:

```bash
php -v
composer --version
node -v
npm -v
```

If Laragon has multiple PHP versions, select one from **Menu > PHP > Version** and restart Laragon.

---

## Setup with Laragon

### 1. Place the project in Laragon

Open **Laragon Terminal**, then clone the repository into Laragon's web root:

```bash
cd C:\laragon\www
git clone <repository-url> rythm
cd rythm
```

If the project was downloaded as a ZIP, extract it to:

```text
C:\laragon\www\rythm
```

Do not place the project inside another nested `rythm` directory.

### 2. Start local services

Open Laragon and click **Start All**. Apache or Nginx and MySQL should both be running.

### 3. Create the database

Open **Menu > MySQL > HeidiSQL** (or phpMyAdmin) and create a database named `rythme_db` with `utf8mb4` encoding.

Alternatively, run:

```sql
CREATE DATABASE rythme_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

A standard Laragon installation normally uses:

```text
Host: 127.0.0.1
Port: 3306
Username: root
Password: empty
```

### 4. Install backend and frontend dependencies

From the project directory:

```bash
composer install
npm install
```

### 5. Create the environment file

Using Command Prompt:

```bash
copy .env.example .env
```

Using PowerShell:

```powershell
Copy-Item .env.example .env
```

Generate the application encryption key:

```bash
php artisan key:generate
```

### 6. Configure the local URL and database

Laragon automatically creates a local virtual host based on the folder name. For a folder named `rythm`, use:

```dotenv
APP_NAME="Rythme Music Store"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://rythm.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rythme_db
DB_USERNAME=root
DB_PASSWORD=
```

Click **Reload** in Laragon after adding or renaming the project folder. If automatic virtual hosts are disabled, enable them from **Menu > Preferences > General > Auto virtual hosts**.

You can use `http://localhost:8000` instead by setting `APP_URL` accordingly and running `php artisan serve`.

### 7. Prepare the application

Run the database migrations and create the public storage link:

```bash
php artisan migrate
php artisan storage:link
```

Create the first Filament administrator when the admin panel is needed:

```bash
php artisan make:filament-user
```

The admin panel is available at:

```text
http://rythm.test/admin
```

### 8. Run the frontend

For local development, keep this command running in a Laragon Terminal:

```bash
npm run dev
```

Then open:

```text
http://rythm.test
```

For a production-style local build, use:

```bash
npm run build
```

A Vite development terminal is not required after a successful production build.

### 9. Run background jobs

The project uses the database queue. Keep a separate terminal running when testing queued emails, webhooks, media tasks, or order processing:

```bash
php artisan queue:work
```

---

## Environment API configuration

Never add real credentials to `.env.example`, commit the `.env` file, or share API secrets in screenshots or support messages.

### Cloudinary

Create a Cloudinary account and copy the cloud name, API key, and API secret from the Cloudinary console.

```dotenv
CLOUDINARY_URL=cloudinary://API_KEY:API_SECRET@CLOUD_NAME
CLOUDINARY_UPLOAD_PRESET=rythme_unsigned
```

Create the `rythme_unsigned` upload preset in **Cloudinary Console > Settings > Upload > Upload presets**. It must be configured as unsigned only when direct browser uploads are required. Restrict allowed formats, file sizes, and target folders in the preset.

Required values:

- `API_KEY`: Cloudinary API key
- `API_SECRET`: Cloudinary API secret
- `CLOUD_NAME`: Cloudinary cloud name
- `CLOUDINARY_UPLOAD_PRESET`: the configured upload preset name

### Razorpay

Use Razorpay **Test Mode** credentials for local development:

```dotenv
RAZORPAY_KEY_ID=rzp_test_xxxxxxxxxx
RAZORPAY_KEY_SECRET=your_test_key_secret
RAZORPAY_WEBHOOK_SECRET=your_webhook_signing_secret
```

Create the API key from **Razorpay Dashboard > Account & Settings > API Keys**. Configure a separate webhook signing secret under **Webhooks**. The webhook secret is not the same as the API key secret.

Do not use live Razorpay credentials until the application is deployed over HTTPS and payment/webhook verification has been tested.

### Email provider

The default local configuration writes email to `storage/logs/laravel.log` and does not send real messages:

```dotenv
MAIL_MAILER=log
```

To send email, replace the mail settings with credentials from the chosen SMTP provider:

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your_smtp_username
MAIL_PASSWORD=your_smtp_password
MAIL_SCHEME=tls
MAIL_FROM_ADDRESS="support@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### Optional AWS storage

AWS values are only required if the filesystem is changed from local or Cloudinary storage to S3:

```dotenv
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=ap-south-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false
```

---

## Important configuration notes

### Clear cached configuration

After changing `.env`, always clear cached values:

```bash
php artisan optimize:clear
```

### Database-backed services

Sessions, cache, and queues use the database. Migrations must be completed before browsing the application:

```dotenv
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

If errors mention missing `sessions`, `cache`, or `jobs` tables, run:

```bash
php artisan migrate
```

### Writable directories

On Windows, Laragon normally handles permissions automatically. The web server must be able to write to:

```text
storage
bootstrap/cache
```

Do not delete the `.gitignore` files inside these directories.

### HTTPS and production settings

For production deployment:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
SESSION_SECURE_COOKIE=true
```

Also configure a valid TLS certificate, use a strong database password, switch Razorpay to live keys, configure a real mail provider, and run:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize
```

Use a persistent process manager for `php artisan queue:work` in production.

---

## Testing and code quality

Run the automated application tests:

```bash
php artisan test
```

Build and verify frontend assets:

```bash
npm run build
```

Format PHP code:

```bash
vendor\bin\pint
```

---

## Common Laragon problems

### `php` or `composer` is not recognized

Run commands from **Laragon Terminal**, or add Laragon's PHP and Composer directories to the Windows `PATH`.

### Database connection refused

Confirm MySQL is running in Laragon, verify port `3306`, and check the `DB_*` values in `.env`.

### Vite manifest not found

Install and build frontend dependencies:

```bash
npm install
npm run build
```

For development, leave `npm run dev` running.

### Environment changes are ignored

Run:

```bash
php artisan optimize:clear
```

### The `.test` domain does not open

Click **Reload** in Laragon, confirm auto virtual hosts are enabled, and run Laragon as Administrator once if Windows cannot update the hosts file.
