<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\User;
use App\Services\SiteSettingsService;
use App\Support\AdminAccess;
use App\Support\IndiaStates;
use Filament\Forms\Components\Select;
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
                Section::make('Shipping')
                    ->schema([
                        TextInput::make('shipping_flat_fee')->label('Shipping fee (₹)')->numeric()->prefix('₹'),
                        TextInput::make('shipping_free_above')->label('Free shipping above (₹)')->numeric()->prefix('₹'),
                    ])->columns(2),
                Section::make('GST')
                    ->description('Same-state orders use CGST + SGST. Other-state orders use IGST. A product GST rate, if set, overrides the default rate.')
                    ->schema([
                        Toggle::make('tax_rules_enabled')
                            ->label('Charge GST on orders')
                            ->helperText('Turn on after you have entered your GST rate and business state.'),
                        TextInput::make('tax_rate')
                            ->label('Default GST rate (%)')
                            ->numeric()->minValue(0)->maxValue(100)->suffix('%')
                            ->helperText('Used when a product has no GST rate of its own. Typical goods are 18%.'),
                        Select::make('origin_state')
                            ->label('Business state')
                            ->options(IndiaStates::options())
                            ->searchable()
                            ->helperText('Compared with the customer delivery state to choose CGST+SGST or IGST.'),
                        TextInput::make('origin_gstin')
                            ->label('GSTIN')
                            ->maxLength(15)
                            ->helperText('Shown on invoices.'),
                        TextInput::make('business_legal_name')
                            ->label('Legal name on invoices')
                            ->maxLength(160),
                        TextInput::make('business_address')
                            ->label('Address on invoices')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])->columns(2),
                Section::make('Returns')
                    ->description('Customers can request a return only when this is on.')
                    ->schema([
                        Toggle::make('returns_enabled')->label('Allow return requests'),
                        TextInput::make('return_window_days')
                            ->label('Days after delivery to request a return')
                            ->integer()
                            ->minValue(1)
                            ->maxValue(3650),
                    ])->columns(2),
                Section::make('Contact & address')
                    ->schema([
                        TextInput::make('contact_email')->email(),
                        TextInput::make('contact_phone'),
                        TextInput::make('address_line'),
                        TextInput::make('whatsapp_number')
                            ->label('WhatsApp number')
                            ->helperText('Shown as the floating WhatsApp button on every page. Include the country code, e.g. +91 98765 43210. Leave empty to hide the button.'),
                        TextInput::make('whatsapp_message')
                            ->label('WhatsApp pre-filled message')
                            ->helperText('Optional text pre-filled in the customer\'s chat window.'),
                    ])->columns(1),
                Section::make('Social links')
                    ->description('Shown as icons on the right of the top bar. Leave a field empty and its icon is not displayed.')
                    ->schema([
                        TextInput::make('social_instagram')->label('Instagram URL')->url()->placeholder('https://instagram.com/yourpage'),
                        TextInput::make('social_youtube')->label('YouTube URL')->url()->placeholder('https://youtube.com/@yourchannel'),
                        TextInput::make('social_facebook')->label('Facebook URL')->url()->placeholder('https://facebook.com/yourpage'),
                        TextInput::make('social_x')->label('X (Twitter) URL')->url()->placeholder('https://x.com/yourhandle'),
                        TextInput::make('social_linkedin')->label('LinkedIn URL')->url()->placeholder('https://linkedin.com/company/yourcompany'),
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
