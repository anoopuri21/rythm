# Operations runbook

Designed for Laravel 12 / PHP 8.3 / MySQL 8 on shared hosting or cPanel without a persistent worker daemon.

## Release preflight

- Confirm the approved commit/hash, clean tree, environment-only secrets and no uploaded temporary files.
- Back up database and user-managed storage; verify backup files are outside webroot.
- Set production `APP_ENV`, `APP_DEBUG=false`, HTTPS URL, secure session cookie, mail, database, queue and canonical `RAZORPAY_*` values.
- Run tests/build in an isolated QA copy. Never run destructive tests or migrations against persistent UAT/production.

## Deploy sequence (human authorization required)

1. Enable maintenance mode where appropriate.
2. Publish approved code and dependencies using the host's supported atomic/release method.
3. Run `php artisan migrate --force` only after backup and migration review.
4. Run `php artisan optimize:clear` then `php artisan config:cache`, `route:cache`, and `view:cache` as supported.
5. Publish built frontend assets; link storage if required.
6. Disable maintenance mode and smoke-test home, product, cart, login, admin MFA and a test-mode payment.
7. Record commit, migration result, operator and time. Roll back code/database only via the reviewed release plan.

## Scheduler and bounded queue

Create one cron entry, using the host PHP path:

`* * * * * cd /path/to/app && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1`

The application schedules a bounded queue worker suitable for shared hosting. Do not start an unmanaged permanent daemon. Check `php artisan schedule:list`, failed jobs and host cron logs. Retry only idempotent jobs after cause review; do not blindly retry financial jobs.

## Backups

At minimum: daily encrypted MySQL backup and daily storage backup, retained 7 daily / 4 weekly / 6 monthly (adjust to policy). Keep copies off-host, access-controlled and outside webroot. Example (credentials supplied securely by the environment/client config, never command history):

`mysqldump --single-transaction --routines --triggers DATABASE | gzip > /secure-backups/rythme-YYYYMMDD.sql.gz`

Back up `storage/app/public` or the configured media disk. Monthly, restore the latest backup into an isolated database/storage location and document table counts, media sampling and restore duration. A backup is not qualified until a restore test passes.

## Logs and monitoring

Daily: inspect Laravel errors, scheduler/failed jobs, failed payment events, initiated/failed payments, pending refunds and notification failures. Weekly: check disk/inode use, backup completion, stale admin users, low stock and product health. Redact credentials, signatures and sensitive payload data. Rotate logs through Laravel/host configuration; never make logs web-accessible.

## Incident playbooks

**Payment mismatch:** stop manual mutation; compare local order/payment/event IDs, paise amount, currency and provider dashboard. Reconcile before any retry.

**Compromised secret:** disable/rotate at provider and host, clear/rebuild config cache, inspect audit/access/payment logs, send signed test event, document scope. Do not paste the old/new value into reports.

**Queue backlog:** confirm cron runs and DB connectivity, inspect first failure, fix cause, retry only bounded idempotent jobs. Run a bounded `queue:work --stop-when-empty` manually only when approved.

**Storage pressure:** pause large uploads/imports, inspect media/orphan candidates, back up, then delete through application workflows. Never mass-delete storage paths directly.

**Database issue:** place app in maintenance if integrity is at risk, preserve logs, take a snapshot when safe, restore to isolation first, and escalate to owner/host. Never point migrations/tests at an unrelated database.

## Current human gate

`rythm.test` is reported to target unrelated `maverick_academy`. The owner must switch it to `rhythm_db`, clear cached configuration, and confirm before any PHP/MySQL runtime qualification. Deployment/Phase 18 remains inactive without explicit authorization.