<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Filament\Schemas\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Guards against admin panel crashes from Filament API drift — e.g. calling
 * form-component methods that do not exist in the locked Filament version
 * (RichEditor::profile(), TextInput::uppercase()…). Form/infolist schemas are
 * only built when a create/edit modal opens, so plain index-page tests never
 * catch these; this suite builds every resource schema explicitly.
 */
class AdminFormSchemaSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * @return list<class-string>
     */
    private function resourceClasses(): array
    {
        $classes = [];

        foreach (glob(base_path('app/Filament/Resources/*Resource.php')) ?: [] as $file) {
            $classes[] = 'App\\Filament\\Resources\\'.basename($file, '.php');
        }

        $this->assertNotEmpty($classes, 'No Filament resources discovered.');

        return $classes;
    }

    public function test_every_resource_form_schema_builds(): void
    {
        foreach ($this->resourceClasses() as $class) {
            try {
                $class::form(Schema::make());
            } catch (\Throwable $e) {
                $this->fail("{$class}::form() failed to build: ".$e->getMessage());
            }
        }

        $this->assertTrue(true);
    }

    public function test_every_resource_infolist_schema_builds(): void
    {
        foreach ($this->resourceClasses() as $class) {
            if (! method_exists($class, 'infolist')) {
                continue;
            }

            try {
                $class::infolist(Schema::make());
            } catch (\Throwable $e) {
                $this->fail("{$class}::infolist() failed to build: ".$e->getMessage());
            }
        }

        $this->assertTrue(true);
    }

    public function test_every_resource_index_page_renders_for_admin(): void
    {
        $admin = User::where('email', 'admin@rythme.test')->firstOrFail();

        foreach ($this->resourceClasses() as $class) {
            $url = $class::getUrl();

            $status = $this->actingAs($admin)->get($url)->getStatusCode();

            // 200 = renders (table schema builds); 403 = access intentionally
            // restricted for this role — anything else is a crash.
            $this->assertContains(
                $status,
                [200, 403],
                "{$class} index ({$url}) returned unexpected status {$status}.",
            );
        }
    }

    public function test_settings_page_form_schema_builds(): void
    {
        try {
            (new \App\Filament\Pages\Settings)->form(Schema::make());
        } catch (\Throwable $e) {
            $this->fail('Settings page form failed to build: '.$e->getMessage());
        }

        $this->assertTrue(true);
    }

    public function test_admin_can_create_coupon_with_lowercase_code(): void
    {
        $admin = User::where('email', 'admin@rythme.test')->firstOrFail();

        \Livewire\Livewire::actingAs($admin)
            ->test(\App\Filament\Resources\CouponResource\Pages\ManageCoupons::class)
            ->callAction(
                \Filament\Actions\Testing\TestAction::make('create'),
                data: [
                    'code' => 'diwali10',
                    'type' => \App\Models\Coupon::TYPE_PERCENT,
                    'value' => 10,
                    'min_order' => 0,
                    'is_active' => true,
                ],
            )
            ->assertHasNoErrors();

        // Dehydration + model mutator normalise the code to uppercase.
        $this->assertDatabaseHas('coupons', ['code' => 'DIWALI10']);
    }
}
