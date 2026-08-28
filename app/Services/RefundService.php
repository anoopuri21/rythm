<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\User;
use App\Payment\PaymentGateway;
use App\Support\AdminAccess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class RefundService
{
    public function requestForCancellation(Order $order): Refund
    {
        $payment = Payment::query()
            ->where('order_id', $order->id)
            ->where('status', Payment::STATUS_PAID)
            ->latest('id')
            ->first();

        if ($payment === null) {
            throw new RuntimeException('A captured payment is required before a refund can be requested.');
        }

        $refund = $this->reserve(
            $payment,
            (float) $order->total,
            'Customer cancelled order',
            'cancellation:'.$order->id,
            null,
        );

        $order->update(['payment_status' => Order::PAYMENT_REFUND_PENDING]);

        return $refund;
    }

    public function request(
        Payment $payment,
        float $amount,
        string $reason,
        User $requester,
        ?string $idempotencyKey = null,
    ): Refund {
        $this->authorizeFinance($requester);

        return $this->reserve(
            $payment,
            $amount,
            $reason,
            $idempotencyKey ?? 'finance:'.Str::uuid(),
            $requester->id,
        );
    }

    public function process(Refund $refund, PaymentGateway $gateway, User $approver): Refund
    {
        $this->authorizeFinance($approver);

        $reserved = DB::transaction(function () use ($refund, $approver): Refund {
            $locked = Refund::query()->whereKey($refund->id)->lockForUpdate()->firstOrFail();

            if ($locked->status === Refund::STATUS_REFUNDED) {
                return $locked;
            }

            if ($locked->status !== Refund::STATUS_PENDING) {
                throw new RuntimeException('Only a pending refund can be processed. Reconcile processing or failed refunds before another attempt.');
            }

            $locked->update([
                'status' => Refund::STATUS_PROCESSING,
                'approved_by' => $approver->id,
                'approved_at' => now(),
                'failure_message' => null,
            ]);

            return $locked->fresh(['payment', 'order']);
        });

        if ($reserved->status === Refund::STATUS_REFUNDED) {
            return $reserved;
        }

        $result = $gateway->refund($reserved->payment, $reserved);

        return DB::transaction(function () use ($reserved, $result): Refund {
            $locked = Refund::query()->whereKey($reserved->id)->lockForUpdate()->firstOrFail();

            if (! $result->success) {
                $locked->update([
                    'status' => Refund::STATUS_FAILED,
                    'failure_message' => Str::limit($result->message ?? 'Refund rejected by provider.', 500, ''),
                    'processed_at' => now(),
                ]);

                return $locked;
            }

            if (! in_array($result->status, ['processed', 'refunded'], true)) {
                $locked->update([
                    'gateway_refund_id' => $result->gatewayRefundId,
                    'failure_message' => null,
                ]);

                return $locked->fresh();
            }

            $locked->update([
                'gateway_refund_id' => $result->gatewayRefundId,
                'status' => Refund::STATUS_REFUNDED,
                'failure_message' => null,
                'processed_at' => now(),
            ]);

            $payment = Payment::query()->whereKey($locked->payment_id)->lockForUpdate()->firstOrFail();
            $refundedCents = $this->moneyToCents((float) $payment->refunds()
                ->where('status', Refund::STATUS_REFUNDED)
                ->sum('amount'));
            $capturedCents = $this->moneyToCents((float) $payment->amount);

            if ($refundedCents >= $capturedCents) {
                $payment->update(['status' => Payment::STATUS_REFUNDED]);
                $order = Order::query()->whereKey($payment->order_id)->lockForUpdate()->firstOrFail();
                $updates = ['payment_status' => Order::PAYMENT_REFUNDED];
                if (! $order->isCancelled()) {
                    $updates['status'] = Order::STATUS_REFUNDED;
                }
                $order->update($updates);
            }

            return $locked->fresh();
        });
    }

    private function reserve(
        Payment $payment,
        float $amount,
        string $reason,
        string $idempotencyKey,
        ?int $requestedBy,
    ): Refund {
        return DB::transaction(function () use ($payment, $amount, $reason, $idempotencyKey, $requestedBy): Refund {
            $lockedPayment = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();
            $existing = Refund::query()->where('idempotency_key', $idempotencyKey)->first();

            if ($existing !== null) {
                if ($existing->payment_id !== $lockedPayment->id) {
                    throw new RuntimeException('Refund identity belongs to another payment.');
                }

                return $existing;
            }

            if (! in_array($lockedPayment->status, [Payment::STATUS_PAID, Payment::STATUS_REFUNDED], true)) {
                throw new RuntimeException('A captured payment is required before a refund can be requested.');
            }

            $amountCents = $this->moneyToCents($amount);
            if ($amountCents <= 0) {
                throw new RuntimeException('Refund amount must be greater than zero.');
            }

            $reservedCents = $this->moneyToCents((float) $lockedPayment->refunds()
                ->whereIn('status', [Refund::STATUS_PENDING, Refund::STATUS_PROCESSING, Refund::STATUS_REFUNDED])
                ->sum('amount'));
            $capturedCents = $this->moneyToCents((float) $lockedPayment->amount);

            if ($reservedCents + $amountCents > $capturedCents) {
                throw new RuntimeException('Refund total cannot exceed the captured payment amount.');
            }

            $reason = trim($reason);
            if (mb_strlen($reason) < 5 || mb_strlen($reason) > 500) {
                throw new RuntimeException('A refund reason between 5 and 500 characters is required.');
            }

            return Refund::query()->create([
                'order_id' => $lockedPayment->order_id,
                'payment_id' => $lockedPayment->id,
                'idempotency_key' => $idempotencyKey,
                'amount' => $amountCents / 100,
                'currency' => $lockedPayment->currency,
                'status' => Refund::STATUS_PENDING,
                'reason' => $reason,
                'requested_by' => $requestedBy,
            ]);
        });
    }

    private function authorizeFinance(User $user): void
    {
        if (! $user->hasAdminPermission(AdminAccess::FINANCE_MANAGE)) {
            throw new RuntimeException('Finance permission is required to manage refunds.');
        }
    }

    private function moneyToCents(float $amount): int
    {
        return (int) round($amount * 100);
    }
}
