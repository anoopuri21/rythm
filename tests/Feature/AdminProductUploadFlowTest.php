<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\ProductResource;
use App\Models\Page;
use App\Models\Product;
use App\Support\PublicContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use ReflectionClass;
use Tests\TestCase;

/**
 * C3/W1: admin catalogue upload flow helpers + nav gates.
 */
class AdminProductUploadFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        PublicContent::forgetPageCache();
        Cache::flush();
    }

    public function test_product_resource_registers_ops_filters(): void
    {
        $source = file_get_contents((new ReflectionClass(ProductResource::class))->getFileName());

        $this->assertIsString($source);
        foreach (['out_of_stock', 'has_variants', 'no_gallery', 'imported_pending', 'view_storefront'] as $needle) {
            $this->assertStringContainsString($needle, $source);
        }
        $this->assertStringContainsString('createOptionForm', $source);
        $this->assertStringContainsString('createOptionUsing', $source);
    }

    public function test_edit_product_page_defines_storefront_preview_action(): void
    {
        $path = app_path('Filament/Resources/ProductResource/Pages/EditProduct.php');
        $source = file_get_contents($path);

        $this->assertIsString($source);
        $this->assertStringContainsString('view_storefront', $source);
        $this->assertStringContainsString('View on storefront', $source);
        $this->assertStringContainsString('product.show', $source);
    }

    public function test_active_product_storefront_url_is_reachable_from_slug(): void
    {
        $product = Product::query()->where('is_active', true)->whereNotNull('slug')->firstOrFail();

        $this->get(route('product.show', $product))
            ->assertOk()
            ->assertSee($product->name, false);
    }

    public function test_navbar_hides_about_when_page_inactive(): void
    {
        Page::query()->where('slug', 'about')->update(['is_active' => false]);
        PublicContent::forgetPageCache();
        Cache::flush();

        $this->assertNull(PublicContent::pageHref('about'));

        $this->get('/')
            ->assertOk()
            ->assertDontSee('href="/about"', false)
            ->assertDontSee('>Our Story</a>', false);
    }
}
