<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\ReturnReason;
use App\Models\ReturnRequest;
use App\Models\User;
use App\Services\ReturnRequestService;
use App\Services\SiteSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

final class ReturnRequestDomainTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_are_disabled_without_explicit_configuration(): void
    {
        [$order, $item, $customer, $reason] = $this->deliveredOrder();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Customer returns are not currently enabled.');
        app(ReturnRequestService::class)->create($order, $reason, [$item->id => 1], 'disabled-return', $customer);
    }

    public function test_owner_can_create_and_replay_a_partial_return_without_over_requesting(): void
    {
        [$order, $item, $customer, $reason] = $this->deliveredOrder();
        $this->enableReturns();
        $service = app(ReturnRequestService::class);

        $request = $service->create($order, $reason, [$item->id => 1], 'return-identity-1', $customer, 'Item needs review');
        $replay = $service->create($order, $reason, [$item->id => 1], 'return-identity-1', $customer, 'Item needs review');

        $this->assertTrue($request->is($replay));
        $this->assertSame(ReturnRequest::STATUS_REQUESTED, $request->status);
        $this->assertSame($reason->name, $request->reason_snapshot);
        $this->assertSame(1, $request->items->sum('quantity'));
        $this->assertDatabaseCount('return_requests', 1);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Return quantity cannot exceed the remaining eligible order quantity.');
        $service->create($order, $reason, [$item->id => 2], 'return-identity-2', $customer);
    }

    public function test_request_rejects_foreign_customer_item_and_inactive_reason(): void
    {
        [$order, $item, $customer, $reason] = $this->deliveredOrder();
        $this->enableReturns();
        $service = app(ReturnRequestService::class);
        $foreign = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        try {
            $service->create($order, $reason, [$item->id => 1], 'foreign-owner', $foreign);
            $this->fail('Foreign owner unexpectedly created a return.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Only the order owner can request a return.', $exception->getMessage());
        }

        $reason->update(['is_active' => false]);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The selected return reason is not available.');
        $service->create($order, $reason->fresh(), [$item->id => 1], 'inactive-reason', $customer);
    }

    public function test_support_triages_but_order_manager_controls_approval_and_receipt(): void
    {
        [$order, $item, $customer, $reason] = $this->deliveredOrder();
        $this->enableReturns();
        $service = app(ReturnRequestService::class);
        $request = $service->create($order, $reason, [$item->id => 1], 'transition-return', $customer);
        $support = User::factory()->create(['role' => User::ROLE_SUPPORT]);
        $manager = User::factory()->create(['role' => User::ROLE_ORDER_MANAGER]);

        $request = $service->transition($request, ReturnRequest::STATUS_UNDER_REVIEW, 'Support completed initial triage', $support);
        try {
            $service->transition($request, ReturnRequest::STATUS_APPROVED, 'Support attempted approval', $support);
            $this->fail('Support unexpectedly approved a return.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Order-management permission is required for this return transition.', $exception->getMessage());
        }

        $request = $service->transition($request, ReturnRequest::STATUS_APPROVED, 'Order manager approved logistics', $manager);
        $this->assertNotNull($request->approved_at);
        $this->assertDatabaseCount('refunds', 0);
        $request = $service->transition($request, ReturnRequest::STATUS_RECEIVED, 'Returned parcel was received', $manager);
        $request = $service->transition($request, ReturnRequest::STATUS_CLOSED, 'Return logistics were closed', $manager);

        $this->assertSame(ReturnRequest::STATUS_CLOSED, $request->status);
        $this->assertNotNull($request->received_at);
        $this->assertNotNull($request->closed_at);
        $this->assertCount(5, $request->events);
        $this->assertDatabaseCount('refunds', 0);
    }

    public function test_approved_return_can_reserve_but_not_process_a_phase_8_refund(): void
    {
        [$order, $item, $customer, $reason] = $this->deliveredOrder();
        $this->enableReturns();
        $payment = Payment::query()->create([
            'order_id' => $order->id,
            'gateway' => 'fake',
            'gateway_order_id' => 'return_order_'.$order->id,
            'gateway_payment_id' => 'return_payment_'.$order->id,
            'amount' => 200,
            'currency' => 'INR',
            'status' => Payment::STATUS_PAID,
        ]);
        $service = app(ReturnRequestService::class);
        $request = $service->create($order, $reason, [$item->id => 1], 'refund-link-return', $customer);
        $manager = User::factory()->create(['role' => User::ROLE_ORDER_MANAGER]);
        $finance = User::factory()->create(['role' => User::ROLE_FINANCE]);
        $request = $service->transition($request, ReturnRequest::STATUS_UNDER_REVIEW, 'Order manager started review', $manager);
        $request = $service->transition($request, ReturnRequest::STATUS_APPROVED, 'Logistical return was approved', $manager);

        $refund = $service->requestPendingRefund($request, 50, 'Finance reviewed return amount', $finance);
        $replay = $service->requestPendingRefund($request->fresh(), 50, 'Replay returns existing refund', $finance);

        $this->assertTrue($refund->is($replay));
        $this->assertSame(Refund::STATUS_PENDING, $refund->status);
        $this->assertSame($payment->id, $refund->payment_id);
        $this->assertSame($refund->id, $request->fresh()->refund_id);
        $this->assertNull($refund->gateway_refund_id);
        $this->assertDatabaseCount('refunds', 1);
    }

    public function test_customer_cancellation_releases_quantity_for_a_new_request(): void
    {
        [$order, $item, $customer, $reason] = $this->deliveredOrder();
        $this->enableReturns();
        $service = app(ReturnRequestService::class);
        $cancelled = $service->create($order, $reason, [$item->id => 2], 'cancel-return', $customer);
        $service->cancelByCustomer($cancelled, $customer);

        $replacement = $service->create($order, $reason, [$item->id => 2], 'replacement-return', $customer);

        $this->assertSame(2, $replacement->items->sum('quantity'));
    }

    public function test_configured_window_is_measured_from_recorded_delivery(): void
    {
        [$order, $item, $customer, $reason, $history] = $this->deliveredOrder();
        $this->enableReturns(7);
        $history->update(['created_at' => now()->subDays(8), 'updated_at' => now()->subDays(8)]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('This order is outside the configured return eligibility window.');
        app(ReturnRequestService::class)->create($order, $reason, [$item->id => 1], 'expired-return', $customer);
    }

    public function test_customer_form_and_submission_are_owner_only_and_explicitly_enabled(): void
    {
        [$order, $item, $customer, $reason] = $this->deliveredOrder();

        $this->actingAs($customer)
            ->get(route('returns.create', $order))
            ->assertRedirect(route('orders.show', $order));

        $this->enableReturns();
        $this->actingAs($customer)
            ->get(route('returns.create', $order))
            ->assertOk()
            ->assertSee('Submitting a request does not approve a return or initiate a refund.');

        $foreign = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $this->actingAs($foreign)->get(route('returns.create', $order))->assertForbidden();

        $this->actingAs($customer)
            ->post(route('returns.store', $order), [
                'request_token' => 'web-return-request',
                'return_reason_id' => $reason->id,
                'items' => [$item->id => 1],
                'customer_note' => 'Submitted from customer form',
            ])
            ->assertRedirect(route('orders.show', $order));

        $this->assertDatabaseHas('return_requests', [
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'idempotency_key' => 'web-return-request',
            'status' => ReturnRequest::STATUS_REQUESTED,
        ]);
    }

    public function test_admin_return_pages_enforce_separate_review_and_configuration_permissions(): void
    {
        [$order, $item, $customer, $reason] = $this->deliveredOrder();
        $this->enableReturns();
        $request = app(ReturnRequestService::class)->create($order, $reason, [$item->id => 1], 'admin-return', $customer);
        $support = User::factory()->create(['role' => User::ROLE_SUPPORT]);
        $catalogue = User::factory()->create(['role' => User::ROLE_CATALOGUE_MANAGER]);
        $superAdmin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);

        $this->actingAs($support)->get('/admin/return-requests')->assertOk();
        $this->actingAs($support)->get('/admin/return-requests/'.$request->id)->assertOk();
        $this->actingAs($support)->get('/admin/return-reasons')->assertForbidden();
        $this->actingAs($catalogue)->get('/admin/return-requests')->assertForbidden();
        $this->actingAs($superAdmin)->get('/admin/return-reasons')->assertOk();
    }

    private function enableReturns(int $days = 30): void
    {
        app(SiteSettingsService::class)->saveAll([
            'returns_enabled' => '1',
            'return_window_days' => (string) $days,
        ]);
    }

    /** @return array{Order, OrderItem, User, ReturnReason, OrderStatusHistory} */
    private function deliveredOrder(): array
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'status' => Order::STATUS_DELIVERED,
            'payment_status' => Order::PAYMENT_PAID,
        ]);
        $item = OrderItem::query()->create([
            'order_id' => $order->id,
            'name' => 'Guitar',
            'sku' => 'GTR-RETURN',
            'unit_price' => 100,
            'qty' => 2,
            'total' => 200,
        ]);
        $history = OrderStatusHistory::query()->create([
            'order_id' => $order->id,
            'from' => Order::STATUS_SHIPPED,
            'to' => Order::STATUS_DELIVERED,
            'note' => 'Recorded delivery',
            'actor' => 'system',
        ]);
        $reason = ReturnReason::query()->create([
            'name' => 'Configured test reason',
            'customer_guidance' => 'Test-only guidance',
            'is_active' => true,
        ]);

        return [$order, $item, $customer, $reason, $history];
    }
}
