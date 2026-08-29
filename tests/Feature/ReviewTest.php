<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\ReviewSection;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Services\ReviewService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->user = User::where('email', 'test@example.com')->firstOrFail();
        $this->actingAs($this->user);
    }

    private function purchaseProduct(Product $product): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'status' => Order::STATUS_DELIVERED,
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
    }

    public function test_review_section_renders_on_product_page(): void
    {
        $product = Product::first();

        $this->get(route('product.show', $product))
            ->assertOk()
            ->assertSee('Customer reviews')
            ->assertSee('Write a review');
    }

    public function test_review_requires_verified_purchase(): void
    {
        $product = Product::first();

        Livewire::test(ReviewSection::class, ['product' => $product])
            ->set('rating', 5)
            ->set('comment', 'Great guitar!')
            ->call('submit')
            ->assertSet('error', 'You can only review products from paid, delivered orders.');

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_verified_purchase_can_submit_review(): void
    {
        $product = Product::first();
        $this->purchaseProduct($product);

        Livewire::test(ReviewSection::class, ['product' => $product])
            ->set('rating', 4)
            ->set('comment', 'Lovely instrument, great setup.')
            ->call('submit')
            ->assertSet('submitted', true);

        $this->assertDatabaseHas('reviews', [
            'product_id' => $product->id,
            'user_id' => $this->user->id,
            'rating' => 4,
            'is_approved' => false, // moderation first
        ]);
    }

    public function test_review_requires_both_paid_and_delivered_order(): void
    {
        $product = Product::firstOrFail();
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => Order::STATUS_DELIVERED,
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

        Livewire::test(ReviewSection::class, ['product' => $product])
            ->call('submit')
            ->assertSet('error', 'You can only review products from paid, delivered orders.');

        $order->update([
            'status' => Order::STATUS_CONFIRMED,
            'payment_status' => Order::PAYMENT_PAID,
        ]);

        Livewire::test(ReviewSection::class, ['product' => $product])
            ->call('submit')
            ->assertSet('error', 'You can only review products from paid, delivered orders.');
    }

    public function test_duplicate_review_rejected(): void
    {
        $product = Product::first();
        $this->purchaseProduct($product);

        Review::create([
            'product_id' => $product->id,
            'user_id' => $this->user->id,
            'rating' => 5,
            'is_approved' => false,
        ]);

        Livewire::test(ReviewSection::class, ['product' => $product])
            ->call('submit')
            ->assertSet('error', 'You have already reviewed this product.');
    }

    public function test_database_enforces_one_review_per_customer_and_product(): void
    {
        $product = Product::firstOrFail();
        Review::create([
            'product_id' => $product->id,
            'user_id' => $this->user->id,
            'rating' => 5,
            'status' => Review::STATUS_PENDING,
        ]);

        $this->expectException(QueryException::class);
        Review::create([
            'product_id' => $product->id,
            'user_id' => $this->user->id,
            'rating' => 4,
            'status' => Review::STATUS_PENDING,
        ]);
    }

    public function test_guest_review_redirects_to_login(): void
    {
        auth()->logout();
        $product = Product::first();

        Livewire::test(ReviewSection::class, ['product' => $product])
            ->call('submit')
            ->assertRedirect('/login');
    }

    public function test_only_approved_reviews_visible(): void
    {
        $product = Product::first();

        Review::create(['product_id' => $product->id, 'user_id' => $this->user->id, 'rating' => 5, 'comment' => 'Approved review', 'status' => Review::STATUS_APPROVED]);
        Review::create(['product_id' => $product->id, 'user_id' => User::factory()->create()->id, 'rating' => 1, 'comment' => 'Hidden review', 'status' => Review::STATUS_PENDING]);

        Livewire::test(ReviewSection::class, ['product' => $product])
            ->assertSee('Approved review')
            ->assertDontSee('Hidden review');
    }

    public function test_summary_counts_stars(): void
    {
        $product = Product::first();

        Review::create(['product_id' => $product->id, 'user_id' => $this->user->id, 'rating' => 5, 'status' => Review::STATUS_APPROVED]);
        Review::create(['product_id' => $product->id, 'user_id' => User::factory()->create()->id, 'rating' => 4, 'status' => Review::STATUS_APPROVED]);

        $summary = app(ReviewService::class)->summary($product);

        $this->assertSame(4.5, $summary['avg']);
        $this->assertSame(2, $summary['count']);
        $this->assertSame(1, $summary['stars'][5]);
        $this->assertSame(1, $summary['stars'][4]);
    }

    public function test_only_approved_review_and_merchant_reply_are_public(): void
    {
        $product = Product::firstOrFail();
        Review::create([
            'product_id' => $product->id,
            'user_id' => $this->user->id,
            'rating' => 5,
            'comment' => 'Public review',
            'merchant_reply' => 'Thank you for your feedback.',
            'status' => Review::STATUS_APPROVED,
        ]);
        Review::create([
            'product_id' => $product->id,
            'user_id' => User::factory()->create()->id,
            'rating' => 2,
            'comment' => 'Rejected review',
            'status' => Review::STATUS_REJECTED,
        ]);

        Livewire::test(ReviewSection::class, ['product' => $product])
            ->assertSee('Public review')
            ->assertSee('Thank you for your feedback.')
            ->assertDontSee('Rejected review');
    }

    public function test_admin_moderation_records_actor_and_timestamp(): void
    {
        $admin = User::where('email', 'admin@rythme.test')->firstOrFail();
        $review = Review::create([
            'product_id' => Product::firstOrFail()->id,
            'user_id' => $this->user->id,
            'rating' => 4,
            'status' => Review::STATUS_PENDING,
        ]);

        $this->actingAs($admin);
        $review->update([
            'status' => Review::STATUS_APPROVED,
            'merchant_reply' => 'Approved by the store team.',
        ]);

        $review->refresh();
        $this->assertTrue($review->is_approved);
        $this->assertSame($admin->id, $review->moderated_by);
        $this->assertSame($admin->id, $review->replied_by);
        $this->assertNotNull($review->moderated_at);
        $this->assertNotNull($review->replied_at);
    }

    public function test_admin_can_access_reviews_resource(): void
    {
        $admin = User::where('email', 'admin@rythme.test')->firstOrFail();
        $product = Product::first();
        Review::create(['product_id' => $product->id, 'user_id' => $this->user->id, 'rating' => 5, 'comment' => 'Pending', 'is_approved' => false]);

        $this->actingAs($admin)
            ->get('/admin/reviews')
            ->assertOk()
            ->assertSee('Pending');
    }
}
