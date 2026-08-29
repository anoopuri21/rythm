<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\User;
use App\Services\SiteSettingsService;
use App\Support\AdminAccess;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string|\UnitEnum|null $navigationGroup = 'SETTINGS';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.settings';

    public array $data = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->hasAdminPermission(AdminAccess::SETTINGS_MANAGE);
    }

    public function mount(SiteSettingsService $settings): void
    {
        $this->form->fill($settings->all());
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Shipping & taxes')
                    ->schema([
                        TextInput::make('shipping_flat_fee')->label('Shipping flat fee (₹)')->numeric()->prefix('₹'),
                        TextInput::make('shipping_free_above')->label('Free shipping above (₹)')->numeric()->prefix('₹'),
                        TextInput::make('tax_rate')->label('GST / tax rate (%)')->numeric()->suffix('%'),
                    ])->columns(3),
                Section::make('Return requests')
                    ->description('Disabled by default. Enable only after the business has approved and published its return policy.')
                    ->schema([
                        Toggle::make('returns_enabled')->label('Enable customer return requests'),
                        TextInput::make('return_window_days')
                            ->label('Eligibility window after recorded delivery (days)')
                            ->integer()
                            ->minValue(1)
                            ->maxValue(3650)
                            ->helperText('No legal or business window is assumed. Enter only an approved value.'),
                    ])->columns(2),
                Section::make('Contact & address')
                    ->schema([
                        TextInput::make('contact_email')->email(),
                        TextInput::make('contact_phone'),
                        TextInput::make('address_line'),
                    ])->columns(1),
                Section::make('Social links')
                    ->schema([
                        TextInput::make('social_instagram')->url(),
                        TextInput::make('social_youtube')->url(),
                        TextInput::make('social_facebook')->url(),
                        TextInput::make('social_x')->url(),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save(SiteSettingsService $settings): void
    {
        $settings->saveAll($this->form->getState());

        Notification::make()->success()->title('Settings saved')->send();
    }
}
