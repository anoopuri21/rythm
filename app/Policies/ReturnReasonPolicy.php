<?php

declare(strict_types=1);

namespace App\Policies;

use App\Support\AdminAccess;

final class ReturnReasonPolicy extends PermissionPolicy
{
    protected string $viewPermission = AdminAccess::SETTINGS_MANAGE;

    protected string $managePermission = AdminAccess::SETTINGS_MANAGE;
}
