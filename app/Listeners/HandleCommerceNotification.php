<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\CommerceNotificationRequested;
use App\Models\Order;
use App\Notifications\CommerceOrderNotification;
use App\Services\CommerceNotificationService;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

final class HandleCommerceNotification
{
    public function __construct(private readonly CommerceNotificationService $notifications) {}

    public function handle(CommerceNotificationRequested $requested): void
    {
        $order = Order::query()->with('user')->find($requested->orderId);
        if ($order === null || ($order->user === null && blank($order->email))) {
            return;
        }

        $event = $this->notifications->recordEvent(
            $requested->eventKey,
            $requested->eventType,
            'order',
            $order->id,
            [
                'order_number' => $order->order_number,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'amount' => $requested->metadata['amount'] ?? null,
                'currency' => $requested->metadata['currency'] ?? $order->currency,
                'refund_id' => $requested->metadata['refund_id'] ?? null,
            ],
        );
        [$title, $message] = $this->copy($requested->eventType, $order->order_number);
        $url = URL::temporarySignedRoute('orders.show', now()->addDays(7), ['order' => $order]);
        $email = (string) ($order->user?->email ?? $order->email);
        $mailDelivery = $this->notifications->reserveDelivery(
            $event,
            $order->user,
            'mail',
            CommerceOrderNotification::class,
            $email,
        );

        if ($mailDelivery->wasRecentlyCreated) {
            $notification = new CommerceOrderNotification($mailDelivery, ['mail'], $title, $message, $url);
            if ($order->user !== null) {
                $order->user->notify($notification);
            } else {
                Notification::route('mail', $email)->notify($notification);
            }
        }

        if ($order->user !== null) {
            $databaseDelivery = $this->notifications->reserveDelivery(
                $event,
                $order->user,
                'database',
                CommerceOrderNotification::class,
                (string) $order->user->id,
            );
            if ($databaseDelivery->wasRecentlyCreated) {
                $order->user->notify(new CommerceOrderNotification(
                    $databaseDelivery,
                    ['database'],
                    $title,
                    $message,
                    $url,
                ));
            }
        }
    }

    /** @return array{string, string} */
    private function copy(string $eventType, string $orderNumber): array
    {
        return match ($eventType) {
            'order.confirmed' => ['Order confirmed', "Payment for order {$orderNumber} was confirmed."],
            'order.processing' => ['Order processing', "Order {$orderNumber} is being prepared."],
            'order.shipped' => ['Order shipped', "Order {$orderNumber} has shipped."],
            'order.delivered' => ['Order delivered', "Order {$orderNumber} was delivered."],
            'order.cancelled' => ['Order cancelled', "Order {$orderNumber} was cancelled."],
            'payment.failed' => ['Payment failed', "Payment for order {$orderNumber} was not completed."],
            'refund.requested' => ['Refund requested', "A refund request was recorded for order {$orderNumber}."],
            'refund.completed' => ['Refund completed', "A refund for order {$orderNumber} was completed."],
            'refund.failed' => ['Refund needs attention', "A refund for order {$orderNumber} could not be completed."],
            default => ['Order update', "There is an update for order {$orderNumber}."],
        };
    }
}
