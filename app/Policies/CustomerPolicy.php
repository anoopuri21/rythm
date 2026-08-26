<?php

declare(strict_types=1);

namespace App\Policies;

use App\Support\AdminAccess;

final class CustomerPolicy extends PermissionPolicy
{
    protected string $viewPermission = AdminAccess::CUSTOMERS_VIEW;

    protected string $managePermission = AdminAccess::STAFF_MANAGE;
}
