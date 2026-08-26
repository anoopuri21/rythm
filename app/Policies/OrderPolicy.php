<?php

declare(strict_types=1);

namespace App\Policies;

use App\Support\AdminAccess;

final class OrderPolicy extends PermissionPolicy
{
    protected string $viewPermission = AdminAccess::ORDERS_VIEW;

    protected string $managePermission = AdminAccess::ORDERS_MANAGE;
}
