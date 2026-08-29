# Permissions matrix

Deny by default applies. A checkmark means the role has the named permission; it does not bypass record ownership, workflow invariants, validation, or required audit reasons.

| Capability | Super Admin | Admin (legacy) | Catalogue Manager | Order Manager | Support | Marketing | Finance |
|---|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| Enter authenticated admin panel | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Catalogue view | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | — |
| Catalogue manage | ✓ | ✓ | ✓ | — | — | — | — |
| Orders view | ✓ | ✓ | — | ✓ | ✓ | — | ✓ |
| Orders manage | ✓ | ✓ | — | ✓ | — | — | — |
| Customers view | ✓ | ✓ | — | ✓ | ✓ | — | ✓ |
| Interactions manage | ✓ | ✓ | — | — | ✓ | — | — |
| Content manage | ✓ | ✓ | — | — | — | ✓ | — |
| Marketing manage | ✓ | ✓ | — | — | — | ✓ | — |
| Finance view/manage | ✓ | ✓ | — | — | — | — | ✓ |
| Notification delivery view | ✓ | ✓ | — | — | ✓ | — | — |
| Settings manage | ✓ | ✓ | — | — | — | — | — |
| Staff access manage | ✓ | ✓ | — | — | — | — | — |
| Audit ledger view | ✓ | ✓ | — | — | — | — | — |

## Resource mapping

| Resource/model | View permission | Mutation permission |
|---|---|---|
| Product, Category, Brand | `catalogue.view` | `catalogue.manage` |
| Order | `orders.view` | `orders.manage` |
| Refund | `finance.view` | `finance.manage` |
| Customer/User | `customers.view` | staff mutations use `staff.manage` |
| Review, ProductQuestion, ContactMessage | `interactions.manage` | `interactions.manage` |
| Coupon, NewsletterSubscriber | `marketing.manage` | `marketing.manage` |
| Page, FAQ, HeroSlide, HomepageBlock, HomepageCategoryRow, HomepageSection | `content.manage` | `content.manage` |
| NotificationDelivery | `notifications.view` | delivery inspection only |
| AdminAuditLog | `audit.view` | immutable/no normal mutation |

## Important boundaries

- `customer` is not a staff role and cannot access Filament.
- `admin` remains an all-permissions compatibility alias; migrate owner accounts to `super_admin` before retiring it.
- Finance cannot change order fulfilment state. Order managers cannot perform refund operations.
- Support can moderate interactions and inspect orders/customers but cannot mutate orders, catalogue or finance state.
- Marketing can manage content/coupons but cannot inject arbitrary head scripts.
- Imported product activation performs its own `catalogue.manage` and policy authorization inside the service, requires review attestations and real stock, and writes an audit event.
- Staff deletion is disabled. Staff role/MFA changes require `staff.manage`, confirmation where applicable, and audit evidence.

## Enforcement points

1. `User::canAccessPanel()` gates the panel.
2. Filament `strictAuthorization()` makes missing policy authorization fail closed.
3. `AppServiceProvider` explicitly registers model policies.
4. `AdminAccess::permissionForModelAbility()` supplies the central model/ability mapping.
5. Sensitive custom actions authorize again in their service/action body; visibility is not treated as authorization.
