# Exact MySQL 8 Acceptance Runbook

**Locked route:** Direct Laravel connection via environment variables  
**Local provider:** Laragon MySQL, administered with HeidiSQL  
**Hosting provider:** cPanel MySQL, administered with phpMyAdmin  
**Containers:** Not used or required

## 1. Confirm the database engine

Run this in HeidiSQL or phpMyAdmin:

```sql
SELECT VERSION() AS server_version, @@version_comment AS version_comment;
```

Acceptance requires Oracle MySQL 8.x. Typical acceptable output identifies `MySQL Community Server - GPL` with an `8.x` version. Output identifying MariaDB is not acceptable, regardless of client compatibility.

## 2. Create a disposable database

Create an empty database used only for destructive automated qualification:

```sql
CREATE DATABASE rythme_acceptance
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
```

Create or select a least-privilege test user that has full rights only on `rythme_acceptance`. Do not use a production database, production credentials, or a database containing user data.

## 3. Inject credentials locally

Preferred options:

1. Put the test connection in the local ignored `.env` file, or
2. Set `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in the current terminal process.

Never commit credentials, paste them into task documents, or expose them in screenshots/logs. `DB_DATABASE` must be `rythme_acceptance` (or another clearly disposable database explicitly approved for this gate).

Example PowerShell shape for Laragon—replace values locally and do not commit them:

```powershell
$env:DB_CONNECTION = "mysql"
$env:DB_HOST = "127.0.0.1"
$env:DB_PORT = "3306"
$env:DB_DATABASE = "rythme_acceptance"
$env:DB_USERNAME = "<local-test-user>"
$env:DB_PASSWORD = "<local-secret>"
```

## 4. Run the destructive acceptance gate

From the project root, after confirming the selected database is disposable:

```bash
php artisan config:clear
php artisan migrate:fresh --seed --force
php artisan test --testsuite=Feature
```

Tests must run sequentially. Do not use parallel test workers against one shared acceptance database.

The final Agent 0 gate will additionally run:

```bash
php artisan test
composer audit --locked
npm audit --omit=dev --audit-level=high
npm run build
```

## 5. Evidence to retain

- Output of `SELECT VERSION(), @@version_comment` with credentials redacted.
- Migration and seeder result.
- Exact test and assertion counts.
- Any MySQL-specific SQL errors and their fixes.
- Confirmation that the target database was disposable.

## 6. Shared-hosting rule

phpMyAdmin may create the cPanel database and import a reviewed SQL artifact if hosting lacks terminal access. This does not authorize a web-exposed migration endpoint. Production deployment and migration procedures remain inactive until Agent 10 is explicitly activated.
