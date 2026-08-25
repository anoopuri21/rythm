# Phase 0B — Shared-Hosting / cPanel Operations

**Status:** Architecture baseline; deployment remains inactive  
**Target:** Hosting where SSH and persistent Supervisor/systemd workers are not guaranteed

## Required production environment

```dotenv
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=mysql
QUEUE_CONNECTION=database
CACHE_STORE=file
SESSION_DRIVER=database
```

The final database host, name, username and password belong in cPanel environment configuration and must never be committed. Exact MySQL 8.x remains mandatory.

## Locked database operating model

- The Laravel application connects directly to MySQL 8.x through `DB_*` environment variables.
- Shared hosting is not expected to provide Docker or Podman, and the project must not depend on either.
- phpMyAdmin is an administration interface for creating/importing/inspecting the cPanel database; it is not the database engine and does not determine acceptance.
- HeidiSQL is an administration client for the user's Laragon database; it is not the database engine and does not determine acceptance.
- The server must identify itself as Oracle MySQL 8.x using:

```sql
SELECT VERSION() AS server_version, @@version_comment AS version_comment;
```

- MariaDB, even when accessible through phpMyAdmin or HeidiSQL and largely protocol-compatible, does not satisfy the exact MySQL 8 gate.
- Phase 0B qualification uses an empty disposable MySQL 8 database. Production/cPanel data must never be targeted by `migrate:fresh` or destructive tests.

## Cron contract

Configure one cPanel cron entry to execute Laravel's scheduler every minute:

```cron
* * * * * /usr/local/bin/php /absolute/path/to/rythm/artisan schedule:run >> /absolute/path/to/rythm/storage/logs/scheduler.log 2>&1
```

The PHP binary and application path must be replaced with values supplied by the host. The application schedule starts a bounded queue worker with `--stop-when-empty`, `--max-time=50`, `--tries=3`, and `--timeout=45`. `withoutOverlapping()` prevents concurrent workers. No persistent daemon is required.

## Queue requirements

- Use the database queue connection on shared hosting.
- The existing jobs and failed-jobs migration must be applied.
- Transactional order email is queued and therefore depends on the minute cron.
- The worker is bounded so cPanel cannot leave an unmanaged process running indefinitely.
- Failed jobs must be inspected through `php artisan queue:failed`; retry only after the cause is corrected.
- A cron health monitor should alert if `storage/logs/scheduler-heartbeat.log` is not updated for more than five minutes. Monitoring provider selection is deferred to the operations phase.

## Coupon reservation recovery requirement

Coupon use is reserved immediately before payment initiation. A future recovery command must release only reservations that meet all of these conditions under row locks:

1. `coupon_usage_recorded_at` is older than the approved payment retry window.
2. `coupon_usage_released_at` is null.
3. The order is not paid and has no paid payment record.
4. The order is cancelled, failed, or explicitly expired by an approved order-expiry transition.
5. Coupon `used_count` is decremented at most once.

Phase 0B does not invent the payment retry/expiry window because it materially affects customer rights and coupon availability. That business value must be approved during the domain/payment phase.

## Pending refund operations requirement

A cancellation with captured payment creates a `refunds.status=pending` record and leaves the captured payment truthful. Before production, a cron-safe processor must:

1. Claim one refund under a database lock and set `processing`.
2. Call Razorpay with an idempotency/reconciliation identifier.
3. Persist gateway refund ID and verified amount/currency.
4. Mark the refund and order payment state `refunded` only after gateway confirmation.
5. On timeout/failure, retain retryable state and a redacted error message.
6. Reconcile pending/processing records against Razorpay before retrying ambiguous calls.

Live refund execution remains Phase 2 and requires Razorpay test credentials; this document only locks the shared-hosting-safe operational shape.

## cPanel verification gate

Before deployment sign-off, verify in a staging cPanel account:

- PHP CLI version and required extensions match the web runtime.
- `schedule:run` executes every minute.
- One queued test mail moves from jobs to completion.
- Failed-job retention and retry work.
- Storage and cache directories are writable without broad permissions.
- Cron output/log rotation does not exhaust the account quota.
