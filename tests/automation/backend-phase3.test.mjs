import test from 'node:test';
import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';

const read = (path) => readFile(new URL(`../../${path}`, import.meta.url), 'utf8');

test('order and payment states have explicit backed enums', async () => {
    const [orderStatus, orderPayment, payment, orderModel, paymentModel] = await Promise.all([
        read('app/Enums/OrderStatus.php'),
        read('app/Enums/OrderPaymentStatus.php'),
        read('app/Enums/PaymentStatus.php'),
        read('app/Models/Order.php'),
        read('app/Models/Payment.php'),
    ]);
    for (const state of ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded']) {
        assert.ok(orderStatus.includes(`'${state}'`), `missing order state ${state}`);
    }
    assert.match(orderPayment, /refund_pending/);
    assert.match(payment, /case Initiated/);
    assert.match(orderModel, /OrderStatus::Pending->value/);
    assert.match(paymentModel, /PaymentStatus::Initiated->value/);
});

test('order state changes use one state machine and fulfillment delegates', async () => {
    const [machine, orders, fulfillment] = await Promise.all([
        read('app/Services/OrderStateMachine.php'),
        read('app/Services/OrderService.php'),
        read('app/Services/FulfillmentService.php'),
    ]);
    assert.match(machine, /function assertTransition/);
    assert.match(orders, /\$this->states->assertTransition/);
    assert.match(orders, /function recordInitialStatus/);
    assert.match(orders, /'from' => null/);
    assert.doesNotMatch(fulfillment, /\$order->update\(\['status'/);
    assert.match(fulfillment, /\$this->orders->changeStatus/);
});

test('inventory writes are atomic, conditional, ledgered and idempotent', async () => {
    const inventory = await read('app/Services/InventoryService.php');
    assert.match(inventory, /DB::transaction/);
    assert.match(inventory, /lockForUpdate/);
    assert.match(inventory, /where\('stock', '>='/);
    assert.match(inventory, /movementExists/);
    assert.match(inventory, /InventoryMovement::create/);
});

test('commerce indexes and realistic fixture invariants are present', async () => {
    const [migration, productFactory, orderFactory, seeder] = await Promise.all([
        read('database/migrations/2026_08_29_000005_add_commerce_operational_indexes.php'),
        read('database/factories/ProductFactory.php'),
        read('database/factories/OrderFactory.php'),
        read('database/seeders/DatabaseSeeder.php'),
    ]);
    for (const index of ['orders_customer_timeline_idx', 'orders_payment_operations_idx', 'payments_order_state_idx', 'variants_product_stock_idx']) {
        assert.ok(migration.includes(index), `missing ${index}`);
    }
    assert.match(productFactory, /\$price \+ fake\(\)->numberBetween/);
    assert.match(orderFactory, /'total' => \$subtotal \+ \$shippingFee \+ \$tax/);
    assert.match(seeder, /environment\('production'\)/);
});

test('Phase 3 maintenance documentation exists', async () => {
    const [domain, states, database] = await Promise.all([
        read('docs/domain-model.md'),
        read('docs/state-machine.md'),
        read('docs/database-optimization.md'),
    ]);
    assert.match(domain, /Display versus internal commerce fields/);
    assert.match(states, /Prohibited transitions/);
    assert.match(database, /Atomic write rules/);
});
