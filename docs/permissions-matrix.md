# Permissions matrix

`super_admin` and the temporary legacy `admin` alias have all permissions. All other roles are deny-by-default.

| Capability | Super admin | Legacy admin | Catalogue manager | Order manager | Support | Marketing | Finance | Customer |
|---|---:|---:|---:|---:|---:|---:|---:|---:|
| Filament access | Yes | Yes | Yes | Yes | Yes | Yes | Yes | No |
| Catalogue view | Yes | Yes | Yes | Yes | Yes | Yes | No | No |
| Catalogue manage/media | Yes | Yes | Yes | No | No | No | No | No |
| Orders view | Yes | Yes | No | Yes | Yes | No | Yes | Own only |
| Orders manage/fulfilment | Yes | Yes | No | Yes | No | No | No | No |
| Customers view | Yes | Yes | No | Yes | Yes | No | Yes | Self only |
| Reviews/questions/contact manage | Yes | Yes | No | No | Yes | No | No | Own submissions |
| Content/homepage manage | Yes | Yes | No | No | No | Yes | No | No |
| Marketing/coupons/newsletter | Yes | Yes | No | No | No | Yes | No | No |
| Finance view | Yes | Yes | No | No | No | No | Yes | No |
| Refund/finance manage | Yes | Yes | No | No | No | No | Yes | No |
| Settings manage | Yes | Yes | No | No | No | No | No | No |
| Staff manage | Yes | Yes | No | No | No | No | No | No |
| Audit log view | Yes | Yes | No | No | No | No | No | No |
| Notification delivery view | Yes | Yes | No | No | Yes | No | No | No |

## Enforcement map

| Boundary | Enforcement |
|---|---|
| `/admin` panel | Auth middleware, known staff role, required MFA |
| Filament resource lists/records | Strict authorization plus registered model policy |
| Resource actions | Policy and/or explicit permission check; sensitive service invariants remain authoritative |
| Storefront account/order routes | Auth/ownership, signed URLs, or scoped guest lookup grant |
| Livewire cart/wishlist writes | Auth where required, validation, server-owned models and inventory checks |
| Refunds | `finance.manage`, confirmation/reason, captured payment and refund-service guards |
| Imported product activation | `catalogue.manage`, review attestations, real positive stock and activation service |
| Audit records | `audit.view`; immutable/read-only resource |

## Role-review procedure

Quarterly and before each production release: export staff users, identify dormant or shared accounts, confirm each role with the owner, demote excess privilege, and inspect recent audit events. Never use `admin` for a new account. The legacy alias is removed only after every owner account is confirmed as `super_admin` and the change is separately tested so the store cannot lose its final privileged operator.