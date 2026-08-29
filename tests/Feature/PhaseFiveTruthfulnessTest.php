<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\HomepageBlock;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PhaseFiveTruthfulnessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_seeded_content_contains_no_synthetic_testimonials_or_unsupported_policy_claims(): void
    {
        $content = HomepageBlock::query()
            ->get(['section_key', 'title', 'subtitle', 'content'])
            ->toJson();

        foreach ([
            'Free expert setup',
            '1-year warranty',
            'Free insured shipping',
            '35,000+',
            '4.8★',
            'Arjun Mehta',
            'Priya Sharma',
            'Rahul Verma',
        ] as $claim) {
            $this->assertStringNotContainsString($claim, $content);
        }

        $this->assertDatabaseMissing('homepage_blocks', ['section_key' => 'testimonial']);
    }

    public function test_policy_pages_use_bounded_operational_language(): void
    {
        foreach (['shipping', 'returns', 'warranty', 'faqs', 'terms', 'privacy'] as $slug) {
            $page = Page::where('slug', $slug)->firstOrFail();
            $content = (string) $page->content;

            $this->assertNotEmpty($content);
            $this->assertStringNotContainsString('Free shipping, everywhere in India', $content);
            $this->assertStringNotContainsString('7-day easy returns', $content);
            $this->assertStringNotContainsString('1-year warranty on every instrument', $content);
            $this->assertStringNotContainsString('5–7 business days', $content);
            $this->assertStringNotContainsString('We never sell your data', $content);

            if (in_array($slug, ['terms', 'privacy'], true)) {
                $this->get(route('page.show', $slug))->assertOk();
            } else {
                $this->get(route('page.show', $slug))->assertNotFound();
            }
        }
    }

    public function test_claim_remediation_migration_updates_only_untouched_seeded_content(): void
    {
        $shipping = Page::where('slug', 'shipping')->firstOrFail();
        DB::table('pages')->where('id', $shipping->id)->update([
            'content' => 'Unsupported fixture shipping promise',
            'created_at' => $shipping->created_at,
            'updated_at' => $shipping->created_at,
        ]);

        $warranty = Page::where('slug', 'warranty')->firstOrFail();
        DB::table('pages')->where('id', $warranty->id)->update([
            'content' => 'Owner-edited warranty copy',
            'updated_at' => now()->addMinute(),
        ]);

        $migration = require database_path('migrations/2026_08_26_000002_replace_unsupported_seeded_claims.php');
        $migration->up();

        $this->assertStringContainsString('Shipping charges', (string) $shipping->fresh()->content);
        $this->assertSame('Owner-edited warranty copy', $warranty->fresh()->content);
    }

    public function test_about_page_has_no_synthetic_business_metrics(): void
    {
        $this->get(route('page.show', 'about'))
            ->assertOk()
            ->assertSee('Checkout totals')
            ->assertDontSee('35,000+')
            ->assertDontSee('4.8★')
            ->assertDontSee('Years of craft');
    }
}
