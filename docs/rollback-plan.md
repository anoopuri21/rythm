# Production rollback plan

Rollback restores a known-good service safely; it must not erase valid orders, captured payments, refunds, inventory movements or customer uploads created after release.

## Authority and triggers

Only the owner-designated rollback decision-maker authorizes production rollback. Trigger rollback or maintenance containment for:

- sustained checkout/admin 5xx or database errors;
- payment state granted without captured evidence, duplicate financial/inventory writes, or refund ambiguity;
- authorization bypass, secret exposure or executable upload/content vulnerability;
- migration integrity failure or material data corruption;
- unusable storefront/admin with no safe forward fix inside the agreed window.

Minor presentation defects with safe workarounds normally use a reviewed forward fix, not database rollback.

## Before changing anything

- [ ] Announce incident, operator, time, release SHA and trigger.
- [ ] Stop deployment and nonessential queue/scheduler work; do not blindly retry financial jobs.
- [ ] Preserve application/web/PHP/MySQL logs and take a fresh incident database snapshot when safe.
- [ ] Reconcile any in-flight Razorpay payments/refunds and record provider/local identifiers without secrets.
- [ ] Decide rollback layer: config/cache only, application/assets, migration, or database restore.

## A. Configuration/cache rollback

Use when code is good but environment/cache is wrong.

1. Restore the previous approved environment values through the host secret manager; never copy `.env` through Git.
2. Run `php artisan optimize:clear`, then rebuild production caches.
3. Restart the supported PHP process/service if the host requires it.
4. Run the immediate smoke gate and verify sessions, queue, webhook and mail behavior.

## B. Application/assets rollback

Use an atomic symlink/release switch where available.

1. Enable maintenance mode if state consistency or user errors require it.
2. Switch code and built assets to the exact previous known-good artifact/commit.
3. Preserve current `.env`, `storage`, customer media, logs and database.
4. Clear/rebuild Laravel caches using the rolled-back code.
5. Do **not** run old destructive migrations automatically.
6. Disable maintenance mode after home, product, cart, login and admin smoke checks pass.

## C. Migration rollback

Prefer a forward-compatible fix. Run `migrate:rollback` only when the specific migration has a reviewed, data-safe `down()` path and no post-release data depends on the new schema.

1. Review `migrate:status`, exact SQL/down logic, foreign keys and rows written since release.
2. Back up current database again.
3. In maintenance mode, roll back only the intended batch/step.
4. Restore compatible application code, clear caches, run integrity queries and smoke tests.

Never use `migrate:fresh`, `db:wipe`, broad rollback, or test commands against UAT/production.

## D. Database restore

Last resort for confirmed corruption. A full restore discards post-backup writes and requires explicit owner approval.

1. Capture current corrupt/incident database for forensics.
2. Restore the qualified backup to an isolated database first; verify checksum, migrations, table counts, latest orders/payments/refunds and media references.
3. Determine the delta since backup. Reconcile/replay legitimate orders and provider events through reviewed idempotent procedures—not ad-hoc paid-state SQL.
4. Put production in maintenance, restore to the correct Rythme database, deploy schema-compatible code/assets, clear caches and run integrity checks.
5. Reconcile Razorpay and notifications before reopening.

## Media rollback

- Preserve all post-release customer/admin uploads unless proven malicious.
- Restore missing/corrupt managed files from backup while retaining database media IDs/paths.
- Delete malicious media through quarantine/application workflow; never mass-delete a shared storage directory.
- Regenerate conversions with a bounded queue only after originals and disk capacity are verified.

## Financial integrity checks

Before reopening and again after 30 minutes:

- [ ] Every paid local order has matching captured provider order/payment, amount and currency.
- [ ] No provider capture exists without a local order requiring reconciliation.
- [ ] Authorized-only events are not paid and did not capture inventory.
- [ ] Payment event IDs, gateway payment IDs, checkout idempotency keys and refund idempotency keys remain unique.
- [ ] Stock movement count/quantity matches each captured/cancelled order exactly once.
- [ ] Refund totals do not exceed capture and uncertain processing refunds were not blindly retried.

## Post-rollback validation

- [ ] Home, search, product, cart, checkout auth boundary and signed order success smoke tests pass.
- [ ] Correct least-privilege admin account can operate; forbidden role remains denied.
- [ ] Scheduler, bounded queue, mail, logs, backups and storage are healthy.
- [ ] Error/latency/payment dashboards return to baseline.
- [ ] Owner, Agent 0, Finance and Operations receive the incident result.

## Closeout

Record failed and restored SHA, migration/config changes, backup identifiers/checksums, provider reconciliation, timeline, customer impact and follow-up owner. Rotate any exposed secret. Keep deployment disabled until root cause, regression test and a new approved release checklist are complete.