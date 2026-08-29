<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentEvent;
use App\Services\PaymentEventService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentEventLedgerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('rythme.razorpay.key_id', 'rzp_test_key');
        config()->set('rythme.razorpay.key_secret', 'test_secret');
        config()->set('rythme.razorpay.webhook_secret', 'webhook_secret');
    }

    public function test_verified_webhook_is_ledgered_processed_and_replay_safe(): void
    {
        [$order, $payment] = $this->pendingPayment();
        $payload = $this->capturedPayload($payment);
        $body = json_encode($payload, JSON_THROW_ON_ERROR);
        $headers = $this->headers($body, 'evt_payment_captured_1');

        $this->withHeaders($headers)->postJson(route('payment.razorpay.webhook'), $payload)
            ->assertOk()
            ->assertJson(['status' => 'ok']);

        $this->assertSame(Order::PAYMENT_PAID, $order->fresh()->payment_status);
        $this->assertSame(Payment::STATUS_PAID, $payment->fresh()->status);
        $this->assertDatabaseHas('payment_events', [
            'gateway_event_id' => 'evt_payment_captured_1',
            'order_id' => $order->id,
            'payment_id' => $payment->id,
            'status' => PaymentEvent::STATUS_PROCESSED,
        ]);
        $event = PaymentEvent::firstOrFail();
        $this->assertSame([
            'entity_id' => 'pay_test_1',
            'gateway_order_id' => 'order_test_1',
            'amount' => 10000,
            'currency' => 'INR',
            'status' => 'captured',
        ], $event->redacted_metadata);

        $this->withHeaders($headers)->postJson(route('payment.razorpay.webhook'), $payload)
            ->assertOk()
            ->assertJson(['status' => 'ok', 'replayed' => true]);

        $conflictingPayload = $this->capturedPayload($payment, ['amount' => 9999]);
        $conflictingBody = json_encode($conflictingPayload, JSON_THROW_ON_ERROR);
        $this->withHeaders($this->headers($conflictingBody, 'evt_payment_captured_1'))
            ->postJson(route('payment.razorpay.webhook'), $conflictingPayload)
            ->assertConflict();

        $this->assertDatabaseCount('payment_events', 1);
        $this->assertSame(1, $order->statusHistory()->where('to', Order::STATUS_CONFIRMED)->count());
    }

    public function test_amount_currency_and_order_mismatches_are_rejected_and_recorded(): void
    {
        foreach ([
            'amount' => ['amount' => 9999],
            'currency' => ['currency' => 'USD'],
            'order' => ['order_id' => 'order_other'],
        ] as $suffix => $change) {
            [$order, $payment] = $this->pendingPayment('order_test_'.$suffix);
            $payload = $this->capturedPayload($payment, $change);
            $body = json_encode($payload, JSON_THROW_ON_ERROR);

            $this->withHeaders($this->headers($body, 'evt_'.$suffix))
                ->postJson(route('payment.razorpay.webhook'), $payload)
                ->assertStatus($suffix === 'order' ? 404 : 422);

            $this->assertSame(Order::PAYMENT_UNPAID, $order->fresh()->payment_status);
            $this->assertSame(Payment::STATUS_INITIATED, $payment->fresh()->status);
            $this->assertDatabaseHas('payment_events', [
                'gateway_event_id' => 'evt_'.$suffix,
                'status' => PaymentEvent::STATUS_FAILED,
            ]);
        }
    }

    public function test_invalid_signature_stores_no_untrusted_event(): void
    {
        [, $payment] = $this->pendingPayment();
        $payload = $this->capturedPayload($payment);

        $this->withHeaders([
            'X-Razorpay-Signature' => 'invalid',
            'X-Razorpay-Event-Id' => 'evt_untrusted',
        ])->postJson(route('payment.razorpay.webhook'), $payload)->assertBadRequest();

        $this->assertDatabaseCount('payment_events', 0);
    }

    public function test_missing_provider_event_id_uses_deterministic_payload_hash(): void
    {
        [, $payment] = $this->pendingPayment();
        $payload = $this->capturedPayload($payment);
        $body = json_encode($payload, JSON_THROW_ON_ERROR);
        $receipt = app(PaymentEventService::class)->receive($body, $payload, $payment, null);

        $this->assertTrue($receipt['is_new']);
        $this->assertSame('sha256:'.hash('sha256', $body), $receipt['event']->gateway_event_id);
    }

    /** @return array{Order, Payment} */
    private function pendingPayment(string $gatewayOrderId = 'order_test_1'): array
    {
        $order = Order::factory()->create([
            'email' => null,
            'status' => Order::STATUS_PENDING,
            'payment_status' => Order::PAYMENT_UNPAID,
            'subtotal' => 100,
            'total' => 100,
            'currency' => 'INR',
        ]);
        $payment = Payment::query()->create([
            'order_id' => $order->id,
            'gateway' => 'razorpay',
            'gateway_order_id' => $gatewayOrderId,
            'amount' => 100,
            'currency' => 'INR',
            'status' => Payment::STATUS_INITIATED,
        ]);

        return [$order, $payment];
    }

    /** @param array<string, mixed> $changes */
    private function capturedPayload(Payment $payment, array $changes = []): array
    {
        return [
            'event' => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => array_merge([
                        'id' => 'pay_test_1',
                        'order_id' => $payment->gateway_order_id,
                        'amount' => 10000,
                        'currency' => 'INR',
                        'status' => 'captured',
                        'email' => 'must-not-be-retained@example.test',
                        'contact' => '9999999999',
                    ], $changes),
                ],
            ],
        ];
    }

    /** @return array<string, string> */
    private function headers(string $body, string $eventId): array
    {
        return [
            'X-Razorpay-Signature' => hash_hmac('sha256', $body, 'webhook_secret'),
            'X-Razorpay-Event-Id' => $eventId,
        ];
    }
}
