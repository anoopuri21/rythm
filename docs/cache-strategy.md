# Cache Strategy

## Production optimization commands

After each release and only after environment configuration and non-destructive migrations are verified:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan event:cache
php artisan route:cache
php artisan view:cache
php artisan cache:clear
```

`cache:clear` is intentionally after configuration caching and before traffic validation so application data caches rebuild from the new release. Do not cache config before `.env` and environment-only secrets are final. Rollback starts with `php artisan optimize:clear` against the restored release.

For shared hosting, cPanel cron runs `php artisan schedule:run` each minute. The scheduler invokes a bounded `queue:work --stop-when-empty --max-time=50`; no persistent daemon is assumed.

## Application caches

| Key/domain | TTL | Source | Invalidation |
|---|---:|---|---|
| `homepage.data` observer key | 1 hour | hero, blocks, FAQ, curated catalogue | homepage/content/product observers |
| `homepage.sections` | 1 hour | section heading configuration | `HomepageSectionObserver` |
| `homepage.seo` | 1 hour | home Page/SEO entry | content update path |
| `categories.tree` | forever | two-level active navigation | `CategoryObserver` |
| `brands.with_counts` | 1 hour | active footer/filter brands and counts | `BrandObserver` |
| site settings key | forever | operational settings | settings service mutation |

Homepage cold-cache work is bounded. Popular-category images use local managed assets and no longer execute one product/media fallback query per category.

## What must not be shared-cached

- authenticated account content;
- carts, checkout totals, coupon eligibility or addresses;
- payment, refund or retry state;
- stock decisions and inventory balances;
- signed URLs or authorization results;
- personalized recently viewed IDs.

These remain request/session/database derived. Product and category page HTML should not be edge-cached without a purge design for price and stock.

## Stampede and failure behavior

At current shared-hosting scale, `Cache::remember` with bounded one-hour TTL is sufficient. If cold-cache contention becomes measurable, adopt Laravel `Cache::flexible` stale-while-revalidate only after verifying the selected cache driver and bounded worker. Never add distributed locks that the configured shared-hosting cache driver cannot support.

Database-unavailable/error pages must remain renderable. Category navigation already checks schema availability; additional global view composers should follow the same defensive pattern.

## Driver guidance

- Production preference: Redis only when the host provides a managed, persistent service.
- Portable default: database cache with indexed key/expiration columns.
- File cache is acceptable for one web node but requires correct shared-host permissions.
- Never use `array` cache outside tests.
- Queue driver may be database; failed jobs require bounded operational review.

## Verification

Record cache driver, cold/warm query counts, hit behavior, invalidation after admin edits, queue drain result and permissions. Do not log cache values containing customer or secret data.
