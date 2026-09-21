# Seed data policy (C5 / W4.3)

## Rule

**Production must never run catalogue/user seeders.**

`DatabaseSeeder` throws if `app()->environment('production')` — known local credentials and demonstration SKUs must stay off live hosts.

## Environments

| Env | `migrate` | `db:seed` / `migrate --seed` |
|---|---|---|
| **local** | Yes | Yes — demo catalogue for development |
| **testing** (PHPUnit) | Yes (RefreshDatabase) | Via `$this->seed()` in tests |
| **staging** | Yes | Optional **only** if the host is disposable UAT; wipe before client UAT with real SKUs |
| **production** | Yes (`migrate --force`) | **Never** |

## What seeders contain (local/dev)

- Admin `admin@rythme.test` / customer `test@example.com` (not real people)
- Categories, brands, products inspired by public catalogue structure (copy rewritten)
- Homepage sections/blocks, FAQs, CMS pages (about/contact shell)
- Contact page seeds **empty** contact cards + WhatsApp off (owner fills real details)

## Production launch path

1. `php artisan migrate --force`  
2. Create the first Super Admin via owner-approved process (not seeder passwords)  
3. Import/create real products in Filament (see `ADMIN_PRODUCT_UPLOAD_RUNBOOK.md`)  
4. Fill Settings + Contact page cards with **client** phone/email/address/WhatsApp  
5. Configure Razorpay **test** then **live** keys (owner; see `RAZORPAY_SETUP_GUIDE.md`)

## CI / deploy check

- Deploy scripts must call `migrate --force` only.  
- If a pipeline ever adds `--seed`, fail the job on `APP_ENV=production`.

## Related

- `database/seeders/DatabaseSeeder.php` — production guard  
- `docs/BUY_PATH_SMOKE_CHECKLIST.md` — commerce smoke after real catalogue  
- `docs/PRODUCTION_PRIORITY_PLAN.md` — W4 exit criteria  
