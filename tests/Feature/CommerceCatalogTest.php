<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\NewsletterSubscriber;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommerceCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_builds_full_bajaao_catalog(): void
    {
        $this->seed();

        // 6 parents + 25 children
        $this->assertGreaterThanOrEqual(30, Category::count());
        // brands 8+
        $this->assertGreaterThanOrEqual(15, Brand::count());
        // products 30+
        $this->assertGreaterThanOrEqual(30, Product::count());

        // every product belongs to a category and a brand
        $this->assertSame(0, Product::whereNull('category_id')->count());
        $this->assertSame(0, Product::whereNull('brand_id')->count());

        // slugs and skus are unique
        $this->assertSame(Product::count(), Product::distinct('slug')->count('slug'));
        $this->assertSame(Product::count(), Product::distinct('sku')->count('sku'));
    }

    public function test_category_tree_and_variants_exist(): void
    {
        $this->seed();

        $guitars = Category::where('slug', 'guitars')->firstOrFail();
        $this->assertGreaterThanOrEqual(5, $guitars->children()->count());

        $acoustic = Category::where('slug', 'acoustic-guitars')->firstOrFail();
        $this->assertSame($guitars->id, $acoustic->parent_id);

        // variants exist (finish options)
        $this->assertGreaterThanOrEqual(5, ProductVariant::count());
        $variant = ProductVariant::first();
        $this->assertNotNull($variant->product);
        $this->assertArrayHasKey('finish', $variant->options ?? []);
    }

    public function test_featured_and_discount_products_exist(): void
    {
        $this->seed();

        $this->assertGreaterThanOrEqual(5, Product::featured()->count());
        $this->assertGreaterThan(0, Product::whereNotNull('compare_at_price')->count());

        $discounted = Product::whereNotNull('compare_at_price')->first();
        $this->assertGreaterThan(0, $discounted->discountPercent());
    }

    public function test_product_factory_relations(): void
    {
        $product = Product::factory()->create();

        $this->assertInstanceOf(Category::class, $product->category);
        $this->assertInstanceOf(Brand::class, $product->brand);
        $this->assertTrue($product->is_active);
    }

    public function test_admin_can_access_catalog_resources(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@rythme.test')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/products')
            ->assertOk()
            ->assertSee('Yamaha F310 Acoustic Guitar');

        $this->actingAs($admin)
            ->get('/admin/categories')
            ->assertOk()
            ->assertSee('Guitars');

        $this->actingAs($admin)
            ->get('/admin/brands')
            ->assertOk()
            ->assertSee('Fender');
    }

    public function test_guest_is_redirected_from_admin(): void
    {
        $this->get('/admin/products')->assertRedirect('/admin/login');
    }

    public function test_customer_is_forbidden_from_admin_panel(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->get('/admin/products')
            ->assertForbidden();

        $this->assertFalse($customer->canAccessPanel(Filament::getDefaultPanel()));
    }

    public function test_only_explicit_admin_role_can_access_panel(): void
    {
        $admin = User::factory()->admin()->create();

        $this->assertTrue($admin->canAccessPanel(Filament::getDefaultPanel()));
        $this->assertSame(User::ROLE_ADMIN, $admin->role);
    }

    public function test_role_cannot_be_mass_assigned(): void
    {
        $customer = User::create([
            'name' => 'Customer',
            'email' => 'customer-role-test@example.com',
            'password' => 'Password123!',
            'role' => User::ROLE_ADMIN,
        ]);

        $customer->refresh();

        $this->assertSame(User::ROLE_CUSTOMER, $customer->role);
        $this->assertFalse($customer->isAdmin());
    }

    public function test_mass_assignment_protection_blocks_unknown_attributes(): void
    {
        $this->seed();

        $product = Product::create([
            'name' => 'Guard Test',
            'slug' => 'guard-test',
            'sku' => 'RYM-GUARD-001',
            'price' => 100,
            'stock' => 5,
            'is_active' => false,
            'hacked_column' => 'injected',
            'id' => 999999,
        ]);

        $this->assertArrayNotHasKey('hacked_column', $product->getAttributes());
        $this->assertNull($product->getAttribute('hacked_column'));
        $this->assertNotSame(999999, $product->id);
        // fillable columns still writable via attributes
        $this->assertFalse($product->is_active);
    }

    public function test_hidden_attributes_not_serialized(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@rythme.test')->firstOrFail();

        $serialized = $admin->toArray();

        $this->assertArrayNotHasKey('password', $serialized);
        $this->assertArrayNotHasKey('remember_token', $serialized);
        $this->assertArrayHasKey('email', $serialized);
    }

    public function test_table_attribute_resolves_correct_model_tables(): void
    {
        $this->assertSame('products', (new Product)->getTable());
        $this->assertSame('categories', (new Category)->getTable());
        $this->assertSame('order_status_history', (new OrderStatusHistory)->getTable());
        $this->assertSame('newsletter_subscribers', (new NewsletterSubscriber)->getTable());
    }
}
