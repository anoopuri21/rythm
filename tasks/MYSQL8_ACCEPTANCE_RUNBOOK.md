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

## 2. Persistent project/UAT database

The owner created `rythme_acceptance` and elected to use the MySQL database as the persistent project/UAT database rather than a demo/practice target. Despite its current name, treat it as persistent data:

- Never run `migrate:fresh`, `db:wipe`, or `RefreshDatabase` tests against it.
- Apply schema forward with `php artisan migrate --force`.
- Back up before any later destructive or data-transforming migration.
- Use a least-privilege application user scoped to this database.
- Do not import sample/competitor/production catalog data until its source, rights, validation, and import workflow are approved.

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

## 4. Run the persistent UAT migration

From the project root:

```bash
php artisan config:clear
php artisan migrate --force
php artisan migrate:status
```

Do not run the automated feature suite against this persistent database because many tests intentionally reset database state. Existing automated regression continues on the isolated SQLite test stack until a separate isolated MySQL test schema is approved and available.

The standard independent code gates remain:

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
- Confirmation that forward migrations completed without erasing the persistent UAT database.

## 6. Shared-hosting rule

phpMyAdmin may create the cPanel database and import a reviewed SQL artifact if hosting lacks terminal access. This does not authorize a web-exposed migration endpoint. Production deployment and migration procedures remain inactive until Agent 10 is explicitly activated.
