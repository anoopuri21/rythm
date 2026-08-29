<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\HomepageCategoryRow;
use App\Models\User;
use App\Observers\HomepageDataObserver;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class HomepageCategoryRowAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_additive_schema_contains_unique_category_and_bounded_configuration_fields(): void
    {
        $this->assertTrue(Schema::hasColumns('homepage_category_rows', [
            'id', 'category_id', 'title', 'product_limit', 'sort_order', 'is_active', 'created_at', 'updated_at',
        ]));

        $category = Category::factory()->create();
        HomepageCategoryRow::query()->create([
            'category_id' => $category->id,
            'product_limit' => 4,
        ]);

        $this->expectException(QueryException::class);
        HomepageCategoryRow::query()->create([
            'category_id' => $category->id,
            'product_limit' => 6,
        ]);
    }

    public function test_row_belongs_to_category_and_clamps_legacy_or_direct_limits(): void
    {
        $category = Category::factory()->create();
        $row = HomepageCategoryRow::query()->create([
            'category_id' => $category->id,
            'title' => 'Explore guitars',
            'product_limit' => 255,
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $this->assertTrue($row->is_active);
        $this->assertSame(8, $row->boundedProductLimit());
        $this->assertTrue($row->category->is($category));
        $this->assertTrue($category->homepageCategoryRow()->firstOrFail()->is($row));

        $row->update(['product_limit' => 1]);
        $this->assertSame(4, $row->refresh()->boundedProductLimit());
    }

    public function test_row_changes_flush_cached_homepage_data(): void
    {
        Cache::put(HomepageDataObserver::CACHE_KEY, ['stale' => true], 3600);

        $row = HomepageCategoryRow::query()->create([
            'category_id' => Category::factory()->create()->id,
            'product_limit' => 4,
        ]);

        $this->assertFalse(Cache::has(HomepageDataObserver::CACHE_KEY));

        Cache::put(HomepageDataObserver::CACHE_KEY, ['stale' => true], 3600);
        $row->update(['title' => 'Updated title']);
        $this->assertFalse(Cache::has(HomepageDataObserver::CACHE_KEY));
    }

    public function test_only_content_managers_can_manage_category_rows(): void
    {
        $marketing = User::factory()->create(['role' => User::ROLE_MARKETING]);
        $catalogue = User::factory()->create(['role' => User::ROLE_CATALOGUE_MANAGER]);

        $this->assertTrue(Gate::forUser($marketing)->allows('viewAny', HomepageCategoryRow::class));
        $this->assertTrue(Gate::forUser($marketing)->allows('create', HomepageCategoryRow::class));
        $this->assertFalse(Gate::forUser($catalogue)->allows('viewAny', HomepageCategoryRow::class));
    }

    public function test_authorized_admin_can_open_category_row_manager(): void
    {
        $marketing = User::factory()->create(['role' => User::ROLE_MARKETING]);

        $this->actingAs($marketing)
            ->get('/admin/homepage-category-rows')
            ->assertOk()
            ->assertSee('Category rows');
    }

    public function test_guest_cannot_open_category_row_manager(): void
    {
        $this->get('/admin/homepage-category-rows')->assertRedirect('/admin/login');
    }
}
