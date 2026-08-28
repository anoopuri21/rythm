<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentEvent;
use App\Models\Refund;

final class FinancialReconciliationService
{
    /**
     * @return array{scanned:int, limit:int, truncated:bool, findings:list<array{code:string,order:string,payment_id:?int,detail:string}>}
     */
    public function scan(int $limit = 100): array
    {
        $limit = min(500, max(1, $limit));
        $orders = Order::query()
            ->with(['payments.refunds', 'paymentEvents'])
            ->orderBy('id')
            ->limit($limit + 1)
            ->get();
        $truncated = $orders->count() > $limit;
        $orders = $orders->take($limit);
        $findings = [];

        foreach ($orders as $order) {
            $captured = $order->payments->filter(fn (Payment $payment): bool => in_array(
                $payment->status,
                [Payment::STATUS_PAID, Payment::STATUS_REFUNDED],
                true,
            ));

            if (in_array($order->payment_status, [Order::PAYMENT_PAID, Order::PAYMENT_REFUND_PENDING, Order::PAYMENT_REFUNDED], true)
                && $captured->isEmpty()) {
                $findings[] = $this->finding('ORDER_CAPTURE_MISSING', $order, null, 'Order financial state has no captured payment.');
            }

            foreach ($order->payments as $payment) {
                if (in_array($payment->status, [Payment::STATUS_PAID, Payment::STATUS_REFUNDED], true)) {
                    if ($this->cents((float) $payment->amount) !== $this->cents((float) $order->total)) {
                        $findings[] = $this->finding('PAYMENT_AMOUNT_MISMATCH', $order, $payment, 'Captured payment does not equal the order total.');
                    }
                    if (strtoupper($payment->currency) !== strtoupper($order->currency)) {
                        $findings[] = $this->finding('PAYMENT_CURRENCY_MISMATCH', $order, $payment, 'Captured payment currency differs from the order.');
                    }
                    if ($payment->gateway_payment_id === null || $payment->gateway_payment_id === '') {
                        $findings[] = $this->finding('PAYMENT_PROVIDER_ID_MISSING', $order, $payment, 'Captured payment has no provider payment ID.');
                    }
                }

                if ($payment->status === Payment::STATUS_INITIATED
                    && str_starts_with((string) $payment->gateway_order_id, 'pending_retry_')) {
                    $findings[] = $this->finding('PAYMENT_RETRY_RECONCILIATION_REQUIRED', $order, $payment, 'Provider-order creation has an unresolved outcome.');
                }

                $refunded = $payment->refunds
                    ->where('status', Refund::STATUS_REFUNDED)
                    ->sum(fn (Refund $refund): float => (float) $refund->amount);
                if ($this->cents($refunded) > $this->cents((float) $payment->amount)) {
                    $findings[] = $this->finding('REFUND_TOTAL_EXCEEDS_CAPTURE', $order, $payment, 'Completed refunds exceed the captured payment.');
                }
                if ($payment->status === Payment::STATUS_REFUNDED
                    && $this->cents($refunded) !== $this->cents((float) $payment->amount)) {
                    $findings[] = $this->finding('REFUND_TOTAL_MISMATCH', $order, $payment, 'Refunded payment does not have an equal completed-refund total.');
                }

                foreach ($payment->refunds as $refund) {
                    if ($refund->status === Refund::STATUS_PROCESSING) {
                        $findings[] = $this->finding('REFUND_RECONCILIATION_REQUIRED', $order, $payment, "Refund {$refund->id} has an unresolved provider outcome.");
                    } elseif ($refund->status === Refund::STATUS_FAILED) {
                        $findings[] = $this->finding('REFUND_FAILED', $order, $payment, "Refund {$refund->id} failed and requires review.");
                    }
                }
            }

            foreach ($order->paymentEvents as $event) {
                if ($event->status === PaymentEvent::STATUS_RECEIVED) {
                    $findings[] = $this->finding('PAYMENT_EVENT_UNPROCESSED', $order, $event->payment_id ? $order->payments->firstWhere('id', $event->payment_id) : null, "Payment event {$event->id} remains unprocessed.");
                } elseif ($event->status === PaymentEvent::STATUS_FAILED) {
                    $findings[] = $this->finding('PAYMENT_EVENT_FAILED', $order, $event->payment_id ? $order->payments->firstWhere('id', $event->payment_id) : null, "Payment event {$event->id} was rejected.");
                }
            }
        }

        return [
            'scanned' => $orders->count(),
            'limit' => $limit,
            'truncated' => $truncated,
            'findings' => $findings,
        ];
    }

    /** @return array{code:string,order:string,payment_id:?int,detail:string} */
    private function finding(string $code, Order $order, ?Payment $payment, string $detail): array
    {
        return [
            'code' => $code,
            'order' => $order->order_number,
            'payment_id' => $payment?->id,
            'detail' => $detail,
        ];
    }

    private function cents(float $amount): int
    {
        return (int) round($amount * 100);
    }
}
