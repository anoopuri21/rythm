<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\CheckoutWizard;
use App\Models\Page;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\AddressService;
use App\Services\CartService;
use App\Support\PublicContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * W5 / C1: client-owned policy & tax details must not block the storefront.
 * Empty/withheld/disabled content stays hidden; checkout still completes.
 */
class PublicContentVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        PublicContent::forgetPageCache();
        Cache::flush();
    }

    public function test_inactive_policy_pages_are_not_public(): void
    {
        Page::query()->whereIn('slug', ['shipping', 'returns', 'warranty', 'faqs', 'refund'])->update(['is_active' => false]);
        PublicContent::forgetPageCache();
        Cache::flush();

        foreach (['shipping', 'returns', 'warranty', 'faqs', 'refund'] as $slug) {
            $this->assertNull(PublicContent::pageHref($slug));
            $this->get('/'.$slug)->assertNotFound();
        }
    }

    public function test_product_page_omits_inactive_policy_links(): void
    {
        Page::query()->whereIn('slug', ['shipping', 'returns', 'faqs'])->update(['is_active' => false]);
        PublicContent::forgetPageCache();
        Cache::flush();

        $product = Product::query()->where('is_active', true)->firstOrFail();

        $this->get(route('product.show', $product))
            ->assertOk()
            ->assertDontSee('href="/shipping"', false)
            ->assertDontSee('href="/returns"', false)
            ->assertDontSee('href="/faqs"', false)
            ->assertDontSee(route('orders.lookup'), false)
            ->assertDontSee('Track an order', false);
    }

    public function test_footer_and_nav_omit_inactive_company_links_when_missing(): void
    {
        // Deactivate optional company pages — footer + navbar must not link them.
        Page::query()->whereIn('slug', ['about', 'terms', 'privacy'])->update(['is_active' => false]);
        PublicContent::forgetPageCache();
        Cache::flush();

        $this->assertNull(PublicContent::pageHref('about'));
        $this->assertNull(PublicContent::pageHref('terms'));
        $this->assertNull(PublicContent::pageHref('privacy'));

        $this->get('/')
            ->assertOk()
            ->assertDontSee('href="/about"', false)
            ->assertDontSee('href="/terms"', false)
            ->assertDontSee('href="/privacy"', false)
            ->assertSee('Contact us');
    }

    public function test_checkout_hides_tax_when_tax_rules_disabled_even_if_rate_set(): void
    {
        $user = User::where('email', 'test@example.com')->firstOrFail();
        $this->actingAs($user);

        SiteSetting::query()->updateOrCreate(
            ['key' => 'tax_rules_enabled'],
            ['value' => '0'],
        );
        SiteSetting::query()->updateOrCreate(
            ['key' => 'tax_rate'],
            ['value' => '18'],
        );
        SiteSetting::query()->updateOrCreate(
            ['key' => 'shipping_flat_fee'],
            ['value' => '0'],
        );
        Cache::flush();

        $product = Product::where('slug', 'fender-351-shape-picks-12-pack-medium')->firstOrFail();
        app(CartService::class)->addItem($product, null, 1);

        $address = app(AddressService::class)->store($user->id, [
            'name' => 'Anoop Puri',
            'phone' => '9876543210',
            'line1' => '42, Music Lane',
            'city' => 'New Delhi',
            'state' => 'Delhi',
            'pincode' => '110001',
            'is_default' => true,
        ]);

        Livewire::test(CheckoutWizard::class)
            ->call('selectAddress', $address->id)
            ->assertSet('step', 2)
            ->assertSee('Free')
            ->assertDontSeeHtml('>Tax</dt>');
    }

    public function test_account_hides_returns_help_when_policy_unpublished_and_returns_off(): void
    {
        $user = User::where('email', 'test@example.com')->firstOrFail();
        $this->actingAs($user);

        Page::query()->whereIn('slug', ['returns', 'refund'])->update(['is_active' => false]);
        PublicContent::forgetPageCache();

        SiteSetting::query()->updateOrCreate(
            ['key' => 'returns_enabled'],
            ['value' => '0'],
        );
        Cache::flush();

        $this->get(route('account.index'))
            ->assertOk()
            ->assertDontSee('href="/returns"', false);
    }
}
