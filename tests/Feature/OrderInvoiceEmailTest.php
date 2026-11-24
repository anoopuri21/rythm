<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\OrderInvoiceMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\User;
use App\Payment\PaymentResult;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderInvoiceEmailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->user = User::where('email', 'test@example.com')->firstOrFail();
    }

    private function makePaidOrder(): Order
    {
        $product = Product::where('slug', 'fender-351-shape-picks-12-pack-medium')->firstOrFail();

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'status' => Order::STATUS_PENDING,
            'payment_status' => Order::PAYMENT_UNPAID,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'unit_price' => $product->price,
            'qty' => 1,
            'total' => (float) $product->price,
        ]);

        $order->payments()->create([
            'gateway' => 'razorpay',
            'gateway_order_id' => 'order_test_123',
            'amount' => $order->total,
            'currency' => $order->currency,
            'status' => Payment::STATUS_INITIATED,
        ]);

        return $order;
    }

    public function test_invoice_email_sent_to_customer_on_payment_confirmation(): void
    {
        Mail::fake();

        $order = $this->makePaidOrder();
        $result = new PaymentResult(true, 'paid', 'pay_test_123');

        app(OrderService::class)->markPaid($order, $result);

        Mail::assertSent(OrderInvoiceMail::class, function ($mail) use ($order) {
            return $mail->hasTo($this->user->email)
                && $mail->order->id === $order->id;
        });
    }

    public function test_invoice_email_bcc_to_owner_when_configured(): void
    {
        Mail::fake();

        $ownerEmail = 'owner@rythme.test';
        SiteSetting::updateOrCreate(['key' => 'order_notification_email'], ['value' => $ownerEmail]);

        // Clear settings cache
        cache()->forget('site.settings');

        $order = $this->makePaidOrder();
        $result = new PaymentResult(true, 'paid', 'pay_test_bcc');

        app(OrderService::class)->markPaid($order, $result);

        Mail::assertSent(OrderInvoiceMail::class, function ($mail) use ($ownerEmail) {
            return $mail->hasBcc($ownerEmail);
        });
    }

    public function test_invoice_email_not_bcc_when_owner_email_empty(): void
    {
        Mail::fake();

        // Ensure no owner email is set
        SiteSetting::where('key', 'order_notification_email')->delete();
        cache()->forget('site.settings');

        $order = $this->makePaidOrder();
        $result = new PaymentResult(true, 'paid', 'pay_test_no_bcc');

        app(OrderService::class)->markPaid($order, $result);

        Mail::assertSent(OrderInvoiceMail::class, function ($mail) {
            // No BCC recipients
            return empty($mail->bcc);
        });
    }

    public function test_invoice_email_contains_order_details(): void
    {
        Mail::fake();

        $order = $this->makePaidOrder();
        $result = new PaymentResult(true, 'paid', 'pay_test_content');

        app(OrderService::class)->markPaid($order, $result);

        Mail::assertSent(OrderInvoiceMail::class, function ($mail) use ($order) {
            $rendered = $mail->render();

            return str_contains($rendered, $order->order_number)
                && str_contains($rendered, 'Tax Invoice')
                && str_contains($rendered, 'Billed to');
        });
    }

    public function test_invoice_email_not_sent_when_no_customer_email(): void
    {
        Mail::fake();

        $order = Order::factory()->create([
            'user_id' => null,
            'email' => null,
            'status' => Order::STATUS_PENDING,
            'payment_status' => Order::PAYMENT_UNPAID,
        ]);

        $order->payments()->create([
            'gateway' => 'razorpay',
            'gateway_order_id' => 'order_no_email',
            'amount' => $order->total,
            'currency' => $order->currency,
            'status' => Payment::STATUS_INITIATED,
        ]);

        $result = new PaymentResult(true, 'paid', 'pay_no_email');

        // This should not throw even though there's no email
        app(OrderService::class)->markPaid($order, $result);

        Mail::assertNotSent(OrderInvoiceMail::class);
    }

    public function test_invoice_email_failure_does_not_block_order_confirmation(): void
    {
        Mail::fake();
        Mail::shouldReceive('to')
            ->andThrow(new \Exception('SMTP connection failed'));

        $order = $this->makePaidOrder();
        $result = new PaymentResult(true, 'paid', 'pay_test_failure');

        // Should NOT throw — email failure is logged but doesn't block payment
        app(OrderService::class)->markPaid($order, $result);

        $order->refresh();
        $this->assertSame(Order::PAYMENT_PAID, $order->payment_status);
        $this->assertSame(Order::STATUS_CONFIRMED, $order->status);
    }
}
