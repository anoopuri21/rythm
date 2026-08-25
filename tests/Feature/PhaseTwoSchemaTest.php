<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\PaymentEvent;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductVariant;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class PhaseTwoSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_phase_two_tables_and_order_idempotency_column_exist(): void
    {
        foreach ([
            'product_attributes',
            'product_attribute_values',
            'category_product_attribute',
            'product_attribute_value_product',
            'product_attribute_value_product_variant',
            'inventory_movements',
            'payment_events',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing Phase 2 table: {$table}");
        }

        $this->assertTrue(Schema::hasColumn('orders', 'idempotency_key'));
        $this->assertTrue(Schema::hasColumns('inventory_movements', [
            'quantity_delta',
            'balance_after',
            'idempotency_key',
            'occurred_at',
        ]));
    }

    public function test_category_product_and_variant_attribute_relations_are_normalized(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->for($category)->create();
        $variant = ProductVariant::factory()->for($product)->create();

        $attribute = ProductAttribute::create([
            'name' => 'Finish',
            'slug' => 'finish',
            'type' => 'color',
            'is_filterable' => true,
            'is_variant' => true,
        ]);
        $value = ProductAttributeValue::create([
            'product_attribute_id' => $attribute->id,
            'value' => 'Sunburst',
            'slug' => 'sunburst',
            'color_hex' => '#B20202',
        ]);

        $category->productAttributes()->attach($attribute, [
            'is_required' => false,
            'is_filterable' => true,
            'sort_order' => 1,
        ]);
        $product->attributeValues()->attach($value);
        $variant->attributeValues()->attach($value);

        $this->assertTrue($category->productAttributes->contains($attribute));
        $this->assertTrue($product->attributeValues->contains($value));
        $this->assertTrue($variant->attributeValues->contains($value));
        $this->assertTrue($attribute->values->contains($value));
    }

    public function test_payment_gateway_event_identity_is_unique(): void
    {
        PaymentEvent::create([
            'gateway' => 'razorpay',
            'gateway_event_id' => 'evt_unique_1',
            'event_type' => 'payment.captured',
            'payload_hash' => hash('sha256', 'redacted-event'),
        ]);

        $this->expectException(QueryException::class);

        PaymentEvent::create([
            'gateway' => 'razorpay',
            'gateway_event_id' => 'evt_unique_1',
            'event_type' => 'payment.captured',
            'payload_hash' => hash('sha256', 'duplicate-event'),
        ]);
    }
}
