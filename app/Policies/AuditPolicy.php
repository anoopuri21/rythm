<?php

declare(strict_types=1);

namespace App\Policies;

use App\Support\AdminAccess;

final class AuditPolicy extends PermissionPolicy
{
    protected string $viewPermission = AdminAccess::AUDIT_VIEW;

    protected string $managePermission = AdminAccess::AUDIT_VIEW;
}
