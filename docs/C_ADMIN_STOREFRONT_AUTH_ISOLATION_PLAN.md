# Plan — Isolate admin panel auth from storefront

## Verification (current behaviour)

| Fact | Evidence |
|---|---|
| Single guard | `config/auth.php` only defines `web` |
| Same user table | `User` model; staff = `role` in staffRoles |
| Filament default | `AdminPanelProvider` → no `authGuard()`, uses default `web` |
| Storefront login | `Auth::attempt` on default `web` |
| Shared session cookie | One Laravel session; one `login_web_*` auth key |

**Result:** Logging into `/admin` authenticates the same session that powers `/account`, checkout, wishlist. The admin identity becomes a storefront “customer” session (can place orders as staff, see admin name in navbar, etc.). The reverse is blocked for pure customers by `canAccessPanel()`, but **admin → storefront leak is real**.

## Is that correct?

| Lens | Verdict |
|---|---|
| Small solo-dev convenience | Sometimes intentional |
| **Security / ops for a shop** | **Not ideal** — privilege boundary should not bleed into commerce identity |
| Desired posture | **Strict separation**: admin session ≠ customer session |

Staff who need to *buy* should use a **separate customer account** (or a second browser profile), not the admin panel identity.

## Fix (this change)

1. Add session guard **`admin`** (same `users` provider, separate session auth key).
2. Filament panel: `->authGuard('admin')`.
3. Middleware `UseAdminAuthGuard`: `Auth::shouldUse('admin')` on all admin panel requests so `auth()->user()` / policies / widgets resolve the admin guard.
4. Storefront stays on default `web` only.
5. Logout: storefront logout only clears `web`; Filament logout clears `admin` (framework default per guard).
6. Tests: `actingAs($admin, 'admin')` for Filament HTTP tests; add isolation feature test.

## Non-goals

- Separate `admins` table (optional future).
- Force MFA changes.
- Impersonation features.

## Owner verify

1. Browser A: login `/admin` as staff → open `/account` in same browser → must be **guest** (redirect login), not staff name.
2. Browser B: login storefront as customer → `/admin` → Filament login, not auto-in.
3. Admin logout does not need to clear a customer session they never had.
