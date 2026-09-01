# Production release checklist

This checklist makes deployment repeatable; it is not deployment authorization. Phase 18 and Agent 10 remain inactive until the owner explicitly approves a specific commit, environment and window.

## 1. Release decision

- [ ] Owner names the approved commit SHA, release window, operator and rollback decision-maker.
- [ ] Agent 0 technical sign-off references a completed `docs/qa-checklist.md` with zero unresolved critical blockers.
- [ ] Branch and remote SHA match; working tree is clean; release tag/release notes are prepared.
- [ ] Scope, schema changes, environment changes, expected downtime and customer impact are documented.
- [ ] Support, Operations and Finance know the window and payment/refund handling procedure.

## 2. Backups and rollback readiness

- [ ] Fresh encrypted MySQL backup completed outside webroot and checksum recorded.
- [ ] Managed media/storage backup completed and sampled.
- [ ] Latest backup restore has been tested in isolation.
- [ ] Previous known-good application artifact/commit and built assets are available.
- [ ] Rollback operator has read `docs/rollback-plan.md`; go/no-go checkpoints and irreversible migrations are identified.

## 3. Production configuration

Verify values without printing secrets:

- [ ] PHP 8.3 and required extensions; webroot points to `public/`.
- [ ] `APP_ENV=production`, `APP_DEBUG=false`, correct HTTPS `APP_URL`, application key present.
- [ ] MySQL 8 points only to the Rythme production database; charset/timezone verified.
- [ ] Database session/cache/queue tables exist; `SESSION_SECURE_COOKIE=true`, encrypted sessions, HTTP-only and SameSite policy verified.
- [ ] Canonical `RAZORPAY_KEY_ID`, `RAZORPAY_KEY_SECRET`, `RAZORPAY_WEBHOOK_SECRET` are production values; `RAZORPAY_ALLOW_FAKE_PAYMENTS=false`.
- [ ] Razorpay production webhook URL/events are correct and HTTPS certificate is valid.
- [ ] Mail sender/domain and safe queue settings are correct; SPF/DKIM/DMARC status known.
- [ ] Filesystem disks, storage permissions, storage link, log path and backup destination are writable but not publicly listable.
- [ ] One-minute scheduler cron uses the correct PHP binary; no unmanaged persistent worker is assumed.

## 4. Build and package

- [ ] `composer install --no-dev --prefer-dist --optimize-autoloader` succeeds in the release environment.
- [ ] `composer audit` has no unaccepted critical/high finding.
- [ ] `npm ci --no-audit --no-fund`, dependency review and `npm run build` succeed from the lockfile.
- [ ] Built manifest/assets are packaged with the exact commit; no source map or temporary acquisition/upload fixture is published unintentionally.
- [ ] No `.env`, credential, private key, database dump, logs, test artifacts, `node_modules`, or development cache enters public webroot/release archive.

## 5. Deployment execution

- [ ] Record start time and current health; pause if unexpected background financial work exists.
- [ ] Enable maintenance mode only if required, with a tested bypass for the operator.
- [ ] Publish code/assets atomically where hosting permits; preserve environment and persistent storage.
- [ ] Run `php artisan migrate --force` only after reviewing pending SQL and confirming backup.
- [ ] Run `php artisan optimize:clear`, then supported `config:cache`, `route:cache`, `view:cache` commands.
- [ ] Verify storage link/permissions and run a single bounded queue drain if approved.
- [ ] Disable maintenance mode and record completion time.

## 6. Immediate smoke gate

Run without real financial side effects unless the owner approved a controlled low-value transaction:

- [ ] `/`, `/shop`, representative search/category/product, `/cart`, login and contact return expected status/content.
- [ ] Guest checkout redirects to login; authenticated checkout renders server-derived totals.
- [ ] Admin login/MFA works; one least-privilege direct URL denial and one permitted workflow pass.
- [ ] Sitemap, robots, CSS/JS, local product image, responsive image and error page return correctly.
- [ ] Scheduler is visible; queue/failed jobs, application log and disk usage are healthy.
- [ ] Razorpay test/control procedure verifies callback/webhook signature handling; if a live transaction is approved, reconcile order, captured payment, inventory and notification exactly once.
- [ ] No elevated 4xx/5xx, JavaScript exception, CSP violation blocking checkout, or database connection error.

## 7. Observation and close

- [ ] Observe errors, latency, queue, payment events, refunds and notifications continuously for the first 30 minutes, then at 2 and 24 hours.
- [ ] Compare paid orders with Razorpay/provider settlement view; investigate initiated/authorized states without manual paid mutation.
- [ ] Confirm first scheduled job and first backup after release.
- [ ] Record final commit/tag, migrations, config-cache status, smoke evidence, incidents and go/no-go decision.
- [ ] If any rollback trigger occurs, stop further writes where safe and execute `docs/rollback-plan.md`.
- [ ] Owner closes the release only after Finance/Operations confirmation.