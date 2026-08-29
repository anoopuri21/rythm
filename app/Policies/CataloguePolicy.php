<?php

declare(strict_types=1);

namespace App\Policies;

use App\Support\AdminAccess;

final class CataloguePolicy extends PermissionPolicy
{
    protected string $viewPermission = AdminAccess::CATALOGUE_VIEW;

    protected string $managePermission = AdminAccess::CATALOGUE_MANAGE;
}
