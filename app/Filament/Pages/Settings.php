<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\User;
use App\Services\MailSenderSettingsService;
use App\Services\SiteSettingsService;
use App\Support\AdminAccess;
use App\Support\IndiaStates;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use RuntimeException;

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

    public function mount(SiteSettingsService $settings, MailSenderSettingsService $mailSender): void
    {
        $this->form->fill($this->formDataFromSettings($settings, $mailSender));
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
                Section::make('Outbound email (sender)')
                    ->description('Customer mails (verify email, orders, stock alerts) use this From address only after the mailbox owner confirms a verification link. SMTP/API credentials still come from the server .env.')
                    ->schema([
                        TextInput::make('mail_from_status_display')
                            ->label('Status')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('mail_from_name')
                            ->label('Sender display name')
                            ->maxLength(120)
                            ->placeholder((string) config('app.name'))
                            ->helperText('Shown as the From name in the customer inbox.'),
                        TextInput::make('mail_from_address')
                            ->label('Sender email address')
                            ->email()
                            ->maxLength(254)
                            ->placeholder('noreply@yourdomain.com')
                            ->helperText('Must be a real mailbox you control. Changing it sends a new verification email; the old verified address stays live until the new one is confirmed. Leave empty and save to clear and fall back to MAIL_FROM_ADDRESS.'),
                    ])->columns(1),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('resendMailFromVerification')
                ->label('Resend sender verification')
                ->color('gray')
                ->visible(fn (): bool => app(MailSenderSettingsService::class)->pendingAddress() !== '')
                ->action(function (): void {
                    $mail = app(MailSenderSettingsService::class);
                    $pending = $mail->pendingAddress();
                    if ($pending === '') {
                        Notification::make()->warning()->title('Nothing pending')->send();

                        return;
                    }
                    try {
                        $mail->startVerification($pending);
                        Notification::make()->success()->title('Verification email resent')->body($pending)->send();
                    } catch (RuntimeException $e) {
                        Notification::make()->danger()->title($e->getMessage())->send();
                    }
                }),
        ];
    }

    public function save(SiteSettingsService $settings, MailSenderSettingsService $mailSender): void
    {
        $state = $this->form->getState();

        // Mail-from is handled separately (verification). Do not write pending/live tokens via saveAll.
        $mailFromAddress = $state['mail_from_address'] ?? null;
        $mailFromName = $state['mail_from_name'] ?? null;
        unset(
            $state['mail_from_address'],
            $state['mail_from_name'],
            $state['mail_from_verified_at'],
            $state['mail_from_pending_address'],
            $state['mail_from_pending_token'],
            $state['mail_from_pending_sent_at'],
            $state['mail_from_status_display'],
        );

        $settings->saveAll($state);

        try {
            $result = $mailSender->saveFromAdmin([
                'mail_from_address' => $mailFromAddress,
                'mail_from_name' => $mailFromName,
            ]);
        } catch (RuntimeException $e) {
            Notification::make()->danger()->title($e->getMessage())->send();

            return;
        }

        $this->form->fill($this->formDataFromSettings($settings, $mailSender));

        Notification::make()
            ->success()
            ->title('Settings saved')
            ->body($result['message'])
            ->send();
    }

    /**
     * @return array<string, mixed>
     */
    private function formDataFromSettings(SiteSettingsService $settings, MailSenderSettingsService $mailSender): array
    {
        $data = $settings->all();
        $data['mail_from_status_display'] = $mailSender->statusSummary();

        // Show pending address in the field while waiting for confirmation.
        if (($data['mail_from_pending_address'] ?? '') !== '') {
            $data['mail_from_address'] = $data['mail_from_pending_address'];
        } elseif (($data['mail_from_address'] ?? '') === '') {
            $data['mail_from_address'] = '';
        }

        if (($data['mail_from_name'] ?? '') === '') {
            $data['mail_from_name'] = '';
        }

        return $data;
    }
}
