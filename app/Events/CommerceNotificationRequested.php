<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class CommerceNotificationRequested implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    /** @param array<string, mixed> $metadata */
    public function __construct(
        public readonly string $eventKey,
        public readonly string $eventType,
        public readonly int $orderId,
        public readonly array $metadata = [],
    ) {}
}
