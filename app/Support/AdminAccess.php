<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Brand;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Coupon;
use App\Models\Faq;
use App\Models\HeroSlide;
use App\Models\HomepageBlock;
use App\Models\HomepageCategoryRow;
use App\Models\HomepageSection;
use App\Models\NewsletterSubscriber;
use App\Models\Order;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductQuestion;
use App\Models\Review;
use App\Models\User;

final class AdminAccess
{
    public const CATALOGUE_VIEW = 'catalogue.view';

    public const CATALOGUE_MANAGE = 'catalogue.manage';

    public const ORDERS_VIEW = 'orders.view';

    public const ORDERS_MANAGE = 'orders.manage';

    public const CUSTOMERS_VIEW = 'customers.view';

    public const INTERACTIONS_MANAGE = 'interactions.manage';

    public const CONTENT_MANAGE = 'content.manage';

    public const MARKETING_MANAGE = 'marketing.manage';

    public const FINANCE_VIEW = 'finance.view';

    public const FINANCE_MANAGE = 'finance.manage';

    public const SETTINGS_MANAGE = 'settings.manage';

    public const STAFF_MANAGE = 'staff.manage';

    public const AUDIT_VIEW = 'audit.view';

    /** @var array<string, list<string>> */
    private const ROLE_PERMISSIONS = [
        User::ROLE_SUPER_ADMIN => ['*'],
        User::ROLE_ADMIN => ['*'], // Controlled legacy alias until every owner account is migrated.
        User::ROLE_CATALOGUE_MANAGER => [self::CATALOGUE_VIEW, self::CATALOGUE_MANAGE],
        User::ROLE_ORDER_MANAGER => [self::ORDERS_VIEW, self::ORDERS_MANAGE, self::CUSTOMERS_VIEW, self::CATALOGUE_VIEW],
        User::ROLE_SUPPORT => [self::ORDERS_VIEW, self::CUSTOMERS_VIEW, self::INTERACTIONS_MANAGE, self::CATALOGUE_VIEW],
        User::ROLE_MARKETING => [self::CATALOGUE_VIEW, self::CONTENT_MANAGE, self::MARKETING_MANAGE],
        User::ROLE_FINANCE => [self::ORDERS_VIEW, self::CUSTOMERS_VIEW, self::FINANCE_VIEW, self::FINANCE_MANAGE],
    ];

    /** @var array<class-string, array{view:string,manage:string}> */
    private const MODEL_PERMISSIONS = [
        Product::class => ['view' => self::CATALOGUE_VIEW, 'manage' => self::CATALOGUE_MANAGE],
        Category::class => ['view' => self::CATALOGUE_VIEW, 'manage' => self::CATALOGUE_MANAGE],
        Brand::class => ['view' => self::CATALOGUE_VIEW, 'manage' => self::CATALOGUE_MANAGE],
        Order::class => ['view' => self::ORDERS_VIEW, 'manage' => self::ORDERS_MANAGE],
        User::class => ['view' => self::CUSTOMERS_VIEW, 'manage' => self::STAFF_MANAGE],
        Review::class => ['view' => self::INTERACTIONS_MANAGE, 'manage' => self::INTERACTIONS_MANAGE],
        ProductQuestion::class => ['view' => self::INTERACTIONS_MANAGE, 'manage' => self::INTERACTIONS_MANAGE],
        ContactMessage::class => ['view' => self::INTERACTIONS_MANAGE, 'manage' => self::INTERACTIONS_MANAGE],
        Coupon::class => ['view' => self::MARKETING_MANAGE, 'manage' => self::MARKETING_MANAGE],
        NewsletterSubscriber::class => ['view' => self::MARKETING_MANAGE, 'manage' => self::MARKETING_MANAGE],
        Page::class => ['view' => self::CONTENT_MANAGE, 'manage' => self::CONTENT_MANAGE],
        Faq::class => ['view' => self::CONTENT_MANAGE, 'manage' => self::CONTENT_MANAGE],
        HeroSlide::class => ['view' => self::CONTENT_MANAGE, 'manage' => self::CONTENT_MANAGE],
        HomepageBlock::class => ['view' => self::CONTENT_MANAGE, 'manage' => self::CONTENT_MANAGE],
        HomepageCategoryRow::class => ['view' => self::CONTENT_MANAGE, 'manage' => self::CONTENT_MANAGE],
        HomepageSection::class => ['view' => self::CONTENT_MANAGE, 'manage' => self::CONTENT_MANAGE],
    ];

    /** @return list<string> */
    public static function staffRoles(): array
    {
        return [
            User::ROLE_SUPER_ADMIN,
            User::ROLE_CATALOGUE_MANAGER,
            User::ROLE_ORDER_MANAGER,
            User::ROLE_SUPPORT,
            User::ROLE_MARKETING,
            User::ROLE_FINANCE,
            User::ROLE_ADMIN,
        ];
    }

    public static function has(string $role, string $permission): bool
    {
        $permissions = self::ROLE_PERMISSIONS[$role] ?? [];

        return in_array('*', $permissions, true) || in_array($permission, $permissions, true);
    }

    public static function permissionForModelAbility(string $model, string $ability): ?string
    {
        $permissions = self::MODEL_PERMISSIONS[$model] ?? null;
        if ($permissions === null) {
            return null;
        }

        return in_array($ability, ['viewAny', 'view'], true) ? $permissions['view'] : $permissions['manage'];
    }
}
