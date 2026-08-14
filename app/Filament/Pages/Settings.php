<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Services\SiteSettingsService;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'SETTINGS';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.settings';

    public array $data = [];

    public function mount(SiteSettingsService $settings): void
    {
        $this->form->fill($settings->all());
    }

    public function form(Form $form): Form
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
