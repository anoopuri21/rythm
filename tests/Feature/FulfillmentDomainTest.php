<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Shipment;
use App\Models\User;
use App\Services\FulfillmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class FulfillmentDomainTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_manager_creates_and_safely_replays_a_partial_shipment(): void
    {
        [$order, $first] = $this->orderWithItems();
        $actor = User::factory()->create(['role' => User::ROLE_ORDER_MANAGER]);
        $service = app(FulfillmentService::class);

        $shipment = $service->create($order, [$first->id => 1], 'fulfillment-001', $actor);
        $replayed = $service->create($order, [$first->id => 1], 'fulfillment-001', $actor);

        $this->assertTrue($shipment->is($replayed));
        $this->assertSame(1, $shipment->items->sum('quantity'));
        $this->assertSame(Shipment::STATUS_DRAFT, $shipment->status);
        $this->assertSame($actor->id, $shipment->events->first()->actor_id);
        $this->assertDatabaseCount('shipments', 1);
    }

    public function test_replayed_identity_rejects_different_items(): void
    {
        [$order, $first] = $this->orderWithItems();
        $actor = User::factory()->create(['role' => User::ROLE_ORDER_MANAGER]);
        $service = app(FulfillmentService::class);
        $service->create($order, [$first->id => 1], 'same-identity', $actor);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Fulfillment identity was replayed with different items.');
        $service->create($order, [$first->id => 2], 'same-identity', $actor);
    }

    public function test_allocation_cannot_exceed_ordered_quantity_or_cross_orders(): void
    {
        [$order, $first] = $this->orderWithItems();
        [, $foreign] = $this->orderWithItems();
        $actor = User::factory()->create(['role' => User::ROLE_ORDER_MANAGER]);
        $service = app(FulfillmentService::class);
        $service->create($order, [$first->id => 2], 'allocation-one', $actor);

        try {
            $service->create($order, [$first->id => 1], 'allocation-two', $actor);
            $this->fail('Over-allocation unexpectedly succeeded.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Shipment quantity cannot exceed the unallocated order quantity.', $exception->getMessage());
        }

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Every shipment item must belong to the order.');
        $service->create($order, [$foreign->id => 1], 'foreign-item', $actor);
    }

    public function test_cancelled_draft_releases_its_allocation(): void
    {
        [$order, $first] = $this->orderWithItems();
        $actor = User::factory()->create(['role' => User::ROLE_ORDER_MANAGER]);
        $service = app(FulfillmentService::class);
        $cancelled = $service->create($order, [$first->id => 2], 'cancel-me', $actor);
        $service->transition($cancelled, Shipment::STATUS_CANCELLED, 'Packing was cancelled', $actor);

        $replacement = $service->create($order, [$first->id => 2], 'replacement', $actor);

        $this->assertSame(2, $replacement->items->sum('quantity'));
    }

    public function test_transition_machine_requires_carrier_and_replay_is_harmless(): void
    {
        [$order, $first] = $this->orderWithItems();
        $actor = User::factory()->create(['role' => User::ROLE_ORDER_MANAGER]);
        $service = app(FulfillmentService::class);
        $shipment = $service->create($order, [$first->id => 1], 'transition-one', $actor);
        $shipment = $service->transition($shipment, Shipment::STATUS_READY, 'Parcel checked and ready', $actor);

        try {
            $service->transition($shipment, Shipment::STATUS_DISPATCHED, 'Handed over for dispatch', $actor);
            $this->fail('Dispatch without carrier unexpectedly succeeded.');
        } catch (RuntimeException $exception) {
            $this->assertSame('A carrier reference is required before dispatch.', $exception->getMessage());
        }

        $dispatched = $service->transition($shipment, Shipment::STATUS_DISPATCHED, 'Handed over manually', $actor, [
            'carrier' => 'Manual carrier',
            'awb' => 'TEST-REFERENCE',
            'tracking_url' => 'https://example.test/track/reference',
        ]);
        $replayed = $service->transition($dispatched, Shipment::STATUS_DISPATCHED, 'Replay is harmless', $actor);

        $this->assertTrue($dispatched->is($replayed));
        $this->assertNotNull($dispatched->dispatched_at);
        $this->assertSame(Order::STATUS_SHIPPED, $order->fresh()->status);
        $this->assertCount(3, $dispatched->events);
    }

    public function test_only_complete_delivery_marks_the_order_delivered(): void
    {
        [$order, $first, $second] = $this->orderWithItems();
        $actor = User::factory()->create(['role' => User::ROLE_ORDER_MANAGER]);
        $service = app(FulfillmentService::class);

        $firstShipment = $this->deliver($service->create($order, [$first->id => 2], 'first-parcel', $actor), $service, $actor);
        $this->assertSame(Shipment::STATUS_DELIVERED, $firstShipment->status);
        $this->assertSame(Order::STATUS_SHIPPED, $order->fresh()->status);

        $this->deliver($service->create($order, [$second->id => 1], 'second-parcel', $actor), $service, $actor);
        $this->assertSame(Order::STATUS_DELIVERED, $order->fresh()->status);
    }

    public function test_unpaid_and_unauthorized_fulfillment_is_rejected(): void
    {
        [$order, $first] = $this->orderWithItems();
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $service = app(FulfillmentService::class);

        try {
            $service->create($order, [$first->id => 1], 'unauthorized', $customer);
            $this->fail('Customer unexpectedly created fulfillment.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Order-management permission is required to manage fulfillment.', $exception->getMessage());
        }

        $actor = User::factory()->create(['role' => User::ROLE_ORDER_MANAGER]);
        $order->update(['payment_status' => Order::PAYMENT_UNPAID]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Only a paid, active order can be fulfilled.');
        $service->create($order->fresh(), [$first->id => 1], 'unpaid', $actor);
    }

    private function deliver(Shipment $shipment, FulfillmentService $service, User $actor): Shipment
    {
        $shipment = $service->transition($shipment, Shipment::STATUS_READY, 'Parcel ready to leave', $actor);
        $shipment = $service->transition($shipment, Shipment::STATUS_DISPATCHED, 'Parcel left facility', $actor, ['carrier' => 'Manual carrier']);

        return $service->transition($shipment, Shipment::STATUS_DELIVERED, 'Recipient confirmed delivery', $actor);
    }

    /** @return array{Order, OrderItem, OrderItem} */
    private function orderWithItems(): array
    {
        $order = Order::factory()->create([
            'status' => Order::STATUS_PROCESSING,
            'payment_status' => Order::PAYMENT_PAID,
        ]);
        $first = OrderItem::query()->create([
            'order_id' => $order->id,
            'name' => 'Guitar',
            'sku' => 'GTR-1',
            'unit_price' => 100,
            'qty' => 2,
            'total' => 200,
        ]);
        $second = OrderItem::query()->create([
            'order_id' => $order->id,
            'name' => 'Cable',
            'sku' => 'CBL-1',
            'unit_price' => 20,
            'qty' => 1,
            'total' => 20,
        ]);

        return [$order, $first, $second];
    }
}
