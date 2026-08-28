<?php

declare(strict_types=1);

namespace App\Policies;

use App\Support\AdminAccess;

final class NotificationDeliveryPolicy extends PermissionPolicy
{
    protected string $viewPermission = AdminAccess::NOTIFICATIONS_VIEW;

    protected string $managePermission = AdminAccess::NOTIFICATIONS_VIEW;
}
