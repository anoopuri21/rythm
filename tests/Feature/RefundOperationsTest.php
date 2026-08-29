<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\User;
use App\Payment\FakePaymentGateway;
use App\Payment\PaymentGateway;
use App\Payment\RefundResult;
use App\Services\RefundService;
use App\Support\AdminAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class RefundOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_refund_schema_supports_multiple_idempotent_audited_operations(): void
    {
        $this->assertTrue(Schema::hasColumns('refunds', [
            'idempotency_key', 'requested_by', 'approved_by', 'approved_at', 'processed_at',
        ]));
        $this->assertSame(AdminAccess::FINANCE_VIEW, AdminAccess::permissionForModelAbility(Refund::class, 'viewAny'));
        $this->assertSame(AdminAccess::FINANCE_MANAGE, AdminAccess::permissionForModelAbility(Refund::class, 'update'));

        [$order, $payment] = $this->paidOrder();
        Refund::query()->create([
            'order_id' => $order->id,
            'payment_id' => $payment->id,
            'idempotency_key' => 'schema-one',
            'amount' => 10,
            'currency' => 'INR',
            'status' => Refund::STATUS_PENDING,
            'reason' => 'First partial refund',
        ]);
        Refund::query()->create([
            'order_id' => $order->id,
            'payment_id' => $payment->id,
            'idempotency_key' => 'schema-two',
            'amount' => 10,
            'currency' => 'INR',
            'status' => Refund::STATUS_PENDING,
            'reason' => 'Second partial refund',
        ]);

        $this->assertDatabaseCount('refunds', 2);
    }

    public function test_finance_processes_the_existing_cancellation_refund_without_duplicate_reservation(): void
    {
        [$order, $payment] = $this->paidOrder();
        $finance = User::factory()->create(['role' => User::ROLE_FINANCE]);
        $service = app(RefundService::class);
        $pending = $service->requestForCancellation($order);
        $gateway = \Mockery::mock(PaymentGateway::class);
        $gateway->shouldReceive('refund')
            ->once()
            ->withArgs(fn (Payment $captured, Refund $refund): bool => $captured->is($payment) && $refund->is($pending))
            ->andReturn(new RefundResult(true, 'processed', 'refund_cancelled_order'));

        $processed = $service->processPendingForOrder($order, $gateway, $finance);

        $this->assertTrue($processed->is($pending));
        $this->assertSame(Refund::STATUS_REFUNDED, $processed->status);
        $this->assertSame('100.00', $processed->amount);
        $this->assertSame($finance->id, $processed->approved_by);
        $this->assertSame(Order::PAYMENT_REFUNDED, $order->fresh()->payment_status);
        $this->assertDatabaseCount('refunds', 1);
        $this->assertFalse($service->hasUnresolvedOperation($payment));
    }

    public function test_finance_can_process_partial_then_full_refund_with_aggregate_bound(): void
    {
        [$order, $payment] = $this->paidOrder();
        $finance = User::factory()->create(['role' => User::ROLE_FINANCE]);
        $service = app(RefundService::class);
        $gateway = app(FakePaymentGateway::class);

        $partial = $service->request($payment, 30, 'Approved partial adjustment', $finance, 'refund-partial');
        $partial = $service->process($partial, $gateway, $finance);

        $this->assertSame(Refund::STATUS_REFUNDED, $partial->status);
        $this->assertSame('30.00', $partial->amount);
        $this->assertNotNull($partial->gateway_refund_id);
        $this->assertSame($finance->id, $partial->approved_by);
        $this->assertSame(Payment::STATUS_PAID, $payment->fresh()->status);
        $this->assertSame(Order::PAYMENT_PAID, $order->fresh()->payment_status);

        $remainder = $service->request($payment->fresh(), 70, 'Approved remaining refund', $finance, 'refund-remainder');
        $service->process($remainder, $gateway, $finance);

        $this->assertSame(Payment::STATUS_REFUNDED, $payment->fresh()->status);
        $this->assertSame(Order::PAYMENT_REFUNDED, $order->fresh()->payment_status);
        $this->assertSame(Order::STATUS_REFUNDED, $order->fresh()->status);
        $this->assertSame(100.0, (float) $payment->refunds()->where('status', Refund::STATUS_REFUNDED)->sum('amount'));
    }

    public function test_refund_requests_are_idempotent_and_cannot_exceed_capture(): void
    {
        [, $payment] = $this->paidOrder();
        $finance = User::factory()->create(['role' => User::ROLE_FINANCE]);
        $service = app(RefundService::class);

        $first = $service->request($payment, 60, 'First approved refund', $finance, 'same-key');
        $same = $service->request($payment, 60, 'First approved refund', $finance, 'same-key');
        $this->assertTrue($first->is($same));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Refund total cannot exceed the captured payment amount.');
        $service->request($payment, 41, 'Excess refund attempt', $finance, 'excess-key');
    }

    public function test_non_finance_user_cannot_request_or_process_refund(): void
    {
        [, $payment] = $this->paidOrder();
        $finance = User::factory()->create(['role' => User::ROLE_FINANCE]);
        $support = User::factory()->create(['role' => User::ROLE_SUPPORT]);
        $service = app(RefundService::class);
        $refund = $service->request($payment, 10, 'Finance approved request', $finance);

        try {
            $service->request($payment, 10, 'Support refund attempt', $support);
            $this->fail('Support user unexpectedly requested a refund.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Finance permission is required to manage refunds.', $exception->getMessage());
        }

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Finance permission is required to manage refunds.');
        $service->process($refund, app(FakePaymentGateway::class), $support);
    }

    public function test_provider_pending_result_is_not_claimed_as_refunded_or_retried(): void
    {
        [, $payment] = $this->paidOrder();
        $finance = User::factory()->create(['role' => User::ROLE_FINANCE]);
        $service = app(RefundService::class);
        $refund = $service->request($payment, 20, 'Provider pending check', $finance);
        $gateway = \Mockery::mock(PaymentGateway::class);
        $gateway->shouldReceive('refund')
            ->once()
            ->andReturn(new RefundResult(true, 'pending', 'refund_pending_provider'));

        $result = $service->process($refund, $gateway, $finance);

        $this->assertSame(Refund::STATUS_PROCESSING, $result->status);
        $this->assertSame('refund_pending_provider', $result->gateway_refund_id);
        $this->assertNull($result->processed_at);
        $this->assertSame(Payment::STATUS_PAID, $payment->fresh()->status);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Reconcile processing or failed refunds before another attempt.');
        $service->process($result, $gateway, $finance);
    }

    public function test_processing_same_completed_refund_does_not_repeat_gateway_operation(): void
    {
        [, $payment] = $this->paidOrder();
        $finance = User::factory()->create(['role' => User::ROLE_FINANCE]);
        $service = app(RefundService::class);
        $refund = $service->request($payment, 25, 'Idempotent process check', $finance);
        $gateway = app(FakePaymentGateway::class);

        $first = $service->process($refund, $gateway, $finance);
        $second = $service->process($first, $gateway, $finance);

        $this->assertSame($first->gateway_refund_id, $second->gateway_refund_id);
        $this->assertSame(1, Refund::query()->count());
    }

    /** @return array{Order, Payment} */
    private function paidOrder(): array
    {
        $order = Order::factory()->create([
            'status' => Order::STATUS_CONFIRMED,
            'payment_status' => Order::PAYMENT_PAID,
            'subtotal' => 100,
            'total' => 100,
            'currency' => 'INR',
        ]);
        $payment = Payment::query()->create([
            'order_id' => $order->id,
            'gateway' => 'fake',
            'gateway_order_id' => 'order_'.$order->id,
            'gateway_payment_id' => 'payment_'.$order->id,
            'amount' => 100,
            'currency' => 'INR',
            'status' => Payment::STATUS_PAID,
        ]);

        return [$order, $payment];
    }
}
