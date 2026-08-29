<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\NotificationPreference;
use App\Models\User;
use InvalidArgumentException;

final class NotificationPreferenceService
{
    public function set(
        User $user,
        string $category,
        bool $emailEnabled,
        bool $databaseEnabled,
    ): NotificationPreference {
        if (! in_array($category, NotificationPreference::OPTIONAL_CATEGORIES, true)) {
            throw new InvalidArgumentException('Mandatory transactional notifications cannot be disabled or configured.');
        }

        return NotificationPreference::query()->updateOrCreate(
            ['user_id' => $user->id, 'category' => $category],
            ['email_enabled' => $emailEnabled, 'database_enabled' => $databaseEnabled],
        );
    }

    public function channelEnabled(User $user, string $category, string $channel): bool
    {
        if (! in_array($category, NotificationPreference::OPTIONAL_CATEGORIES, true)) {
            return true;
        }

        $preference = $user->notificationPreferences()->where('category', $category)->first();
        if ($preference === null) {
            return true;
        }

        return match ($channel) {
            'mail' => $preference->email_enabled,
            'database' => $preference->database_enabled,
            default => false,
        };
    }
}
