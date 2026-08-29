<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\User;
use App\Services\SiteSettingsService;
use App\Support\AdminAccess;
use Filament\Forms\Components\TextInput;
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
                        TextInput::make('data.shipping_flat_fee')->label('Shipping flat fee (₹)')->numeric()->prefix('₹'),
                        TextInput::make('data.shipping_free_above')->label('Free shipping above (₹)')->numeric()->prefix('₹'),
                        TextInput::make('data.tax_rate')->label('GST / tax rate (%)')->numeric()->suffix('%'),
                    ])->columns(3),
                Section::make('Contact & address')
                    ->schema([
                        TextInput::make('data.contact_email')->email(),
                        TextInput::make('data.contact_phone'),
                        TextInput::make('data.address_line'),
                    ])->columns(1),
                Section::make('Social links')
                    ->schema([
                        TextInput::make('data.social_instagram')->url(),
                        TextInput::make('data.social_youtube')->url(),
                        TextInput::make('data.social_facebook')->url(),
                        TextInput::make('data.social_x')->url(),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save(SiteSettingsService $settings): void
    {
        $settings->saveAll($this->form->getState()['data'] ?? []);

        Notification::make()->success()->title('Settings saved')->send();
    }
}
