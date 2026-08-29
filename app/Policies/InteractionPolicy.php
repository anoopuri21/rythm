<?php

declare(strict_types=1);

namespace App\Policies;

use App\Support\AdminAccess;

final class InteractionPolicy extends PermissionPolicy
{
    protected string $viewPermission = AdminAccess::INTERACTIONS_MANAGE;

    protected string $managePermission = AdminAccess::INTERACTIONS_MANAGE;
}
