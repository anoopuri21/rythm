<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OrderStatus;
use RuntimeException;

final class OrderStateMachine
{
    /** @return list<OrderStatus> */
    public function allowedFrom(OrderStatus $from): array
    {
        return match ($from) {
            OrderStatus::Pending => [OrderStatus::Confirmed, OrderStatus::Cancelled],
            OrderStatus::Confirmed => [OrderStatus::Processing, OrderStatus::Shipped, OrderStatus::Cancelled],
            OrderStatus::Processing => [OrderStatus::Shipped, OrderStatus::Cancelled],
            OrderStatus::Shipped => [OrderStatus::Delivered, OrderStatus::Cancelled],
            OrderStatus::Delivered, OrderStatus::Cancelled, OrderStatus::Refunded => [],
        };
    }

    public function assertTransition(string $from, string $to): void
    {
        $fromState = OrderStatus::tryFrom($from);
        $toState = OrderStatus::tryFrom($to);

        if ($fromState === null || $toState === null || ! in_array($toState, $this->allowedFrom($fromState), true)) {
            throw new RuntimeException("Cannot move order from '{$from}' to '{$to}'.");
        }
    }
}
