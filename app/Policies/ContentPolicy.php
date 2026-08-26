<?php

declare(strict_types=1);

namespace App\Policies;

use App\Support\AdminAccess;

final class ContentPolicy extends PermissionPolicy
{
    protected string $viewPermission = AdminAccess::CONTENT_MANAGE;

    protected string $managePermission = AdminAccess::CONTENT_MANAGE;
}
