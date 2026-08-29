<?php

declare(strict_types=1);

namespace App\Policies;

use App\Support\AdminAccess;

final class MarketingPolicy extends PermissionPolicy
{
    protected string $viewPermission = AdminAccess::MARKETING_MANAGE;

    protected string $managePermission = AdminAccess::MARKETING_MANAGE;
}
