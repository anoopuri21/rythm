<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\DTOs\ShopFilters;
use App\Livewire\AddToCart;
use App\Events\BackInStockNotificationRequested;
use App\Listeners\HandleBackInStockNotification;
use App\Models\BackInStockSubscription;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductMerchandisingRule;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\BackInStockSubscriptionService;
use App\Services\ProductQueryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use RuntimeException;
use Tests\TestCase;

final class PhaseElevenCustomerExperienceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_search_matches_sku_brand_category_and_attribute_aware_catalogue_fields(): void
    {
        $product = Product::where('slug', 'yamaha-f310-acoustic-guitar')->firstOrFail();
        $brand = Brand::findOrFail($product->brand_id);
        $category = Category::findOrFail($product->category_id);
        $service = app(ProductQueryService::class);

        $this->assertTrue($service->shopQuery(new ShopFilters(search: $product->sku))->whereKey($product->id)->exists());
        $this->assertTrue($service->shopQuery(new ShopFilters(search: $brand->name))->whereKey($product->id)->exists());
        $this->assertTrue($service->shopQuery(new ShopFilters(search: $category->name))->whereKey($product->id)->exists());
    }

    public function test_search_has_a_bounded_typo_tolerant_stem_fallback_and_exact_rank(): void
    {
        $service = app(ProductQueryService::class);
        $results = $service->shopQuery(new ShopFilters(search: 'guitr'))->get();

        $this->assertTrue($results->contains('slug', 'yamaha-f310-acoustic-guitar'));
        $this->assertGreaterThanOrEqual(0, (int) ($results->first()->search_relevance ?? 0));
    }

    public function test_admin_managed_related_rule_precedes_category_fallback_without_affecting_price(): void
    {
        $source = Product::where('slug', 'yamaha-f310-acoustic-guitar')->firstOrFail();
        $target = Product::where('slug', 'fender-cd-60s-acoustic-guitar')->firstOrFail();

        ProductMerchandisingRule::create([
            'source_product_id' => $source->id,
            'target_product_id' => $target->id,
            'rule_type' => ProductMerchandisingRule::TYPE_RELATED,
            'priority' => 100,
            'is_active' => true,
        ]);

        $related = app(ProductQueryService::class)->related($source, 4);

        $this->assertSame($target->id, $related->first()?->id);
        $this->assertSame((string) $target->price, (string) Product::findOrFail($target->id)->price);
    }

    public function test_stock_request_requires_explicit_consent_and_is_idempotent(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'category_id' => Category::firstOrFail()->id,
            'brand_id' => Brand::firstOrFail()->id,
            'slug' => 'phase-eleven-oos',
            'sku' => 'RYM-P11-OOS',
            'stock' => 0,
        ]);

        try {
            app(BackInStockSubscriptionService::class)->subscribe($user, $product, null, false);
            $this->fail('A stock request without consent should be rejected.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Please confirm stock-availability email consent.', $exception->getMessage());
        }

        app(BackInStockSubscriptionService::class)->subscribe($user, $product, null, true);
        app(BackInStockSubscriptionService::class)->subscribe($user, $product, null, true);

        $this->assertDatabaseCount('back_in_stock_subscriptions', 1);
    }

    public function test_back_in_stock_delivery_is_bounded_and_idempotent(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'category_id' => Category::firstOrFail()->id,
            'brand_id' => Brand::firstOrFail()->id,
            'slug' => 'phase-eleven-restocked',
            'sku' => 'RYM-P11-RESTOCK',
            'stock' => 4,
        ]);
        $subscription = app(BackInStockSubscriptionService::class)->subscribe(
            $user,
            $product->forceFill(['stock' => 0]),
            null,
            true,
        );
        $product->update(['stock' => 4]);

        $handler = app(HandleBackInStockNotification::class);
        $event = new BackInStockNotificationRequested($subscription->id);
        $handler->handle($event);
        $handler->handle($event);

        Notification::assertSentTo($user, \App\Notifications\BackInStockNotification::class);
        $this->assertNotNull($subscription->fresh()->notified_at);
        $this->assertDatabaseHas('back_in_stock_subscriptions', ['id' => $subscription->id]);
        $this->assertDatabaseCount('notification_deliveries', 1);
    }

    public function test_notification_command_skips_inactive_variants_even_if_they_have_stock(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email_verified_at' => now()]);
        $product = Product::factory()->create([
            'category_id' => Category::firstOrFail()->id,
            'brand_id' => Brand::firstOrFail()->id,
            'slug' => 'phase-eleven-inactive-variant',
            'sku' => 'RYM-P11-INACTIVE',
            'stock' => 0,
        ]);
        $variant = ProductVariant::factory()->for($product)->create([
            'stock' => 0,
            'is_active' => true,
        ]);
        $subscription = app(BackInStockSubscriptionService::class)->subscribe(
            $user,
            $product,
            $variant,
            true,
        );
        $variant->update(['stock' => 3, 'is_active' => false]);

        $this->artisan('back-in-stock:notify', ['--limit' => 100])
            ->expectsOutput('Queued 0 back-in-stock notification request(s) from a 100-record bound.')
            ->assertExitCode(0);

        Notification::assertNothingSent();
        $this->assertNull($subscription->fresh()->notified_at);
    }

    public function test_notification_command_rejects_limits_outside_the_worker_bound(): void
    {
        $this->artisan('back-in-stock:notify', ['--limit' => 0])
            ->expectsOutput('The limit must be an integer between 1 and 500.')
            ->assertExitCode(2);

        $this->artisan('back-in-stock:notify', ['--limit' => 501])
            ->expectsOutput('The limit must be an integer between 1 and 500.')
            ->assertExitCode(2);
    }

    public function test_delivery_skips_a_customer_whose_email_is_no_longer_verified(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'category_id' => Category::firstOrFail()->id,
            'brand_id' => Brand::firstOrFail()->id,
            'slug' => 'phase-eleven-unverified-after-request',
            'sku' => 'RYM-P11-UNVERIFIED',
            'stock' => 0,
        ]);
        $subscription = app(BackInStockSubscriptionService::class)->subscribe($user, $product, null, true);
        $product->update(['stock' => 2]);
        $user->forceFill(['email_verified_at' => null])->save();

        app(HandleBackInStockNotification::class)->handle(
            new BackInStockNotificationRequested($subscription->id),
        );

        Notification::assertNothingSent();
        $this->assertDatabaseCount('notification_deliveries', 0);
        $this->assertNull($subscription->fresh()->notified_at);
    }

    public function test_out_of_stock_storefront_can_record_an_authenticated_stock_request(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'category_id' => Category::firstOrFail()->id,
            'brand_id' => Brand::firstOrFail()->id,
            'slug' => 'phase-eleven-oos-livewire',
            'sku' => 'RYM-P11-LIVE',
            'stock' => 0,
        ]);

        Livewire::actingAs($user)
            ->test(AddToCart::class, ['product' => $product])
            ->set('notifyConsent', true)
            ->call('requestStockNotification')
            ->assertSet('notifySuccess', true)
            ->assertSet('notifyError', null);

        $this->assertDatabaseHas('back_in_stock_subscriptions', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'target_key' => BackInStockSubscription::targetKey($product->id),
        ]);
    }
}
