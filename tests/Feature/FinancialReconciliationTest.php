<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentEvent;
use App\Models\Refund;
use App\Models\User;
use App\Services\FinancialReconciliationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialReconciliationTest extends TestCase
{
    use RefreshDatabase;

    public function test_consistent_capture_and_partial_refund_have_no_findings(): void
    {
        [$order, $payment] = $this->paidOrder();
        Refund::query()->create([
            'order_id' => $order->id,
            'payment_id' => $payment->id,
            'idempotency_key' => 'partial-clean',
            'gateway_refund_id' => 'refund_clean',
            'amount' => 20,
            'currency' => 'INR',
            'status' => Refund::STATUS_REFUNDED,
            'reason' => 'Approved partial refund',
            'processed_at' => now(),
        ]);

        $report = app(FinancialReconciliationService::class)->scan();

        $this->assertSame(1, $report['scanned']);
        $this->assertFalse($report['truncated']);
        $this->assertSame([], $report['findings']);
        $this->artisan('payments:reconcile')->expectsOutputToContain('findings: 0')->assertSuccessful();
    }

    public function test_reconciliation_reports_financial_and_unresolved_outcome_mismatches(): void
    {
        [$order, $payment] = $this->paidOrder();
        $payment->update([
            'amount' => 90,
            'currency' => 'USD',
            'gateway_payment_id' => null,
        ]);
        Refund::query()->create([
            'order_id' => $order->id,
            'payment_id' => $payment->id,
            'idempotency_key' => 'processing-refund',
            'gateway_refund_id' => 'refund_pending',
            'amount' => 20,
            'currency' => 'USD',
            'status' => Refund::STATUS_PROCESSING,
            'reason' => 'Provider outcome pending',
        ]);
        PaymentEvent::query()->create([
            'order_id' => $order->id,
            'payment_id' => $payment->id,
            'gateway' => 'razorpay',
            'gateway_event_id' => 'failed-event',
            'event_type' => 'payment.captured',
            'status' => PaymentEvent::STATUS_FAILED,
            'payload_hash' => hash('sha256', 'failed-event'),
            'failure_message' => 'Amount mismatch',
            'received_at' => now(),
        ]);

        $codes = collect(app(FinancialReconciliationService::class)->scan()['findings'])->pluck('code');

        $this->assertContains('PAYMENT_AMOUNT_MISMATCH', $codes);
        $this->assertContains('PAYMENT_CURRENCY_MISMATCH', $codes);
        $this->assertContains('PAYMENT_PROVIDER_ID_MISSING', $codes);
        $this->assertContains('REFUND_RECONCILIATION_REQUIRED', $codes);
        $this->assertContains('PAYMENT_EVENT_FAILED', $codes);
        $this->artisan('payments:reconcile', ['--json' => true])->assertFailed();
    }

    public function test_report_is_read_only_bounded_and_validates_limit(): void
    {
        foreach (range(1, 3) as $number) {
            $this->paidOrder('RYM-RECON-'.$number);
        }

        $before = [Order::query()->count(), Payment::query()->count()];
        $report = app(FinancialReconciliationService::class)->scan(2);

        $this->assertSame(2, $report['scanned']);
        $this->assertTrue($report['truncated']);
        $this->assertSame($before, [Order::query()->count(), Payment::query()->count()]);
        $this->artisan('payments:reconcile', ['--limit' => 0])->assertExitCode(2);
        $this->artisan('payments:reconcile', ['--limit' => 501])->assertExitCode(2);
    }

    public function test_customer_timeline_shows_statuses_without_provider_identifiers(): void
    {
        $user = User::factory()->create();
        [$order, $payment] = $this->paidOrder(user: $user);
        Refund::query()->create([
            'order_id' => $order->id,
            'payment_id' => $payment->id,
            'idempotency_key' => 'customer-timeline',
            'amount' => 25,
            'currency' => 'INR',
            'status' => Refund::STATUS_PENDING,
            'reason' => 'Customer timeline check',
        ]);

        $this->actingAs($user)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Payment and refund history')
            ->assertSee('Payment attempt')
            ->assertSee('Refund')
            ->assertSee('₹25.00')
            ->assertDontSee((string) $payment->gateway_payment_id)
            ->assertDontSee((string) $payment->gateway_order_id);
    }

    /** @return array{Order, Payment} */
    private function paidOrder(?string $number = null, ?User $user = null): array
    {
        $order = Order::factory()->create([
            'order_number' => $number ?? 'RYM-RECON-BASE',
            'user_id' => $user?->id,
            'status' => Order::STATUS_CONFIRMED,
            'payment_status' => Order::PAYMENT_PAID,
            'subtotal' => 100,
            'total' => 100,
            'currency' => 'INR',
        ]);
        $payment = Payment::query()->create([
            'order_id' => $order->id,
            'gateway' => 'razorpay',
            'gateway_order_id' => 'gateway-order-secret-'.$order->id,
            'gateway_payment_id' => 'gateway-payment-secret-'.$order->id,
            'amount' => 100,
            'currency' => 'INR',
            'status' => Payment::STATUS_PAID,
        ]);

        return [$order, $payment];
    }
}
