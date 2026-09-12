<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\DTOs\CheckoutData;
use App\Livewire\AddToCart;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\AddressService;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * C2/W3: multi-variant price, options snapshot, and storefront selector.
 */
class VariantDepthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_variant_helpers_read_color_and_specs_from_options_json(): void
    {
        $product = Product::factory()->create(['price' => 10000, 'stock' => 0, 'is_active' => true]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'name' => 'Sunburst',
            'price_override' => 12500,
            'stock' => 4,
            'is_active' => true,
            'options' => [
                'color' => 'Sunburst',
                'color_hex' => '#C4A35A',
                'finish' => 'Gloss',
            ],
        ]);

        $this->assertSame('#C4A35A', $variant->colorHex());
        $this->assertSame('Sunburst', $variant->colorName());
        $this->assertSame(['finish' => 'Gloss'], $variant->specList());
        $this->assertStringContainsString('Sunburst', $variant->optionSummary());
        $this->assertSame('12500.00', $variant->fresh()->effectivePrice($product));
    }

    public function test_cart_uses_variant_price_override(): void
    {
        $product = Product::factory()->create(['price' => 5000, 'stock' => 10, 'is_active' => true]);
        $cheap = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'name' => 'Standard',
            'price_override' => 4500,
            'stock' => 5,
            'is_active' => true,
            'options' => ['color' => 'Black', 'color_hex' => '#111111'],
        ]);
        $premium = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'name' => 'Limited',
            'price_override' => 7900,
            'stock' => 2,
            'is_active' => true,
            'options' => ['color' => 'Gold', 'color_hex' => '#C9A227'],
        ]);

        $cart = app(CartService::class);
        $cart->addItem($product, $cheap, 1);
        $cart->addItem($product, $premium, 1);

        $items = $cart->items();
        $this->assertCount(2, $items);
        $this->assertEquals(4500.0, (float) $items->firstWhere('product_variant_id', $cheap->id)->unit_price);
        $this->assertEquals(7900.0, (float) $items->firstWhere('product_variant_id', $premium->id)->unit_price);
    }

    public function test_order_snapshots_variant_options(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $product = Product::factory()->create(['price' => 8000, 'stock' => 0, 'is_active' => true]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'name' => 'Natural',
            'price_override' => 8200,
            'stock' => 3,
            'is_active' => true,
            'options' => [
                'color' => 'Natural',
                'color_hex' => '#E8D5A3',
                'scale' => '25.5"',
            ],
        ]);

        app(CartService::class)->addItem($product, $variant, 1);
        $address = app(AddressService::class)->store($user->id, [
            'name' => 'Test Buyer',
            'phone' => '9876543210',
            'line1' => '1 Test Street',
            'city' => 'Delhi',
            'state' => 'Delhi',
            'pincode' => '110001',
            'is_default' => true,
        ]);

        $order = app(OrderService::class)->createFromCheckout(
            app(CartService::class)->getOrCreateCart(),
            new CheckoutData(
                addressId: $address->id,
                shippingAddress: app(AddressService::class)->snapshot($address),
                billingAddress: app(AddressService::class)->snapshot($address),
                currency: 'INR',
                couponCode: null,
                idempotencyKey: 'variant-depth-'.uniqid(),
            ),
            $user->id,
        );

        $line = $order->items()->first();
        $this->assertNotNull($line);
        $this->assertSame($variant->id, $line->product_variant_id);
        $this->assertEquals(8200.0, (float) $line->unit_price);
        $this->assertIsArray($line->options);
        $this->assertSame('Natural', $line->options['color'] ?? null);
        $this->assertSame('25.5"', $line->options['scale'] ?? null);
    }

    public function test_add_to_cart_livewire_exposes_color_from_options(): void
    {
        $product = Product::factory()->create(['price' => 3000, 'stock' => 0, 'is_active' => true]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'name' => 'Red',
            'price_override' => 3200,
            'stock' => 6,
            'is_active' => true,
            'options' => ['color' => 'Candy Red', 'color_hex' => '#B20202'],
        ]);

        Livewire::test(AddToCart::class, ['product' => $product->fresh()])
            ->assertSet('variantId', $variant->id)
            ->assertSee('Candy Red')
            ->assertSee('₹3,200');
    }

    public function test_product_resource_collapses_variant_form_fields_into_options(): void
    {
        $method = new \ReflectionMethod(\App\Filament\Resources\ProductResource::class, 'collapseVariantFormData');
        $method->setAccessible(true);

        $collapsed = $method->invoke(null, [
            'name' => 'Sunburst',
            'sku' => 'SKU-1',
            'color_name' => 'Sunburst',
            'color_hex' => '#c4a35a',
            'specs' => ['finish' => 'Gloss', 'color' => 'ignored'],
        ]);

        $this->assertSame('Sunburst', $collapsed['options']['color']);
        $this->assertSame('#C4A35A', $collapsed['options']['color_hex']);
        $this->assertSame('Gloss', $collapsed['options']['finish']);
        $this->assertArrayNotHasKey('color_name', $collapsed);
        $this->assertArrayNotHasKey('specs', $collapsed);
    }
}
