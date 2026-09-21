<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\User;
use App\Services\PaymentSettingsService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RazorpaySettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    protected static string|\UnitEnum|null $navigationGroup = 'SETTINGS';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Razorpay';

    protected static ?string $title = 'Razorpay';

    protected string $view = 'filament.pages.razorpay-settings';

    public array $data = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->isSuperAdmin();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function mount(PaymentSettingsService $payments): void
    {
        abort_unless(PaymentSettingsService::userCanManage(auth()->user()), 403);

        $row = $payments->razorpayRowOrNew();

        $this->form->fill([
            'mode' => $row->mode ?: 'test',
            'test_key_id' => $row->test_key_id,
            'live_key_id' => $row->live_key_id,
            'test_key_secret' => '',
            'live_key_secret' => '',
            'test_webhook_secret' => '',
            'live_webhook_secret' => '',
            'webhook_url' => $payments->webhookUrl(),
        ]);
    }

    public function form(Schema $form): Schema
    {
        $payments = app(PaymentSettingsService::class);
        $row = $payments->razorpayRow();

        return $form
            ->schema([
                Section::make('How this works')
                    ->description('Save both test and live keys. The mode switch picks which pair the store uses. Secrets are stored encrypted and never shown again after you save. Paste a new secret only when you want to replace it.')
                    ->schema([
                        Select::make('mode')
                            ->label('Active mode')
                            ->options([
                                'test' => 'Test (no real charges)',
                                'live' => 'Live (real charges)',
                            ])
                            ->required(),
                        TextInput::make('webhook_url')
                            ->label('Webhook URL')
                            ->disabled()
                            ->dehydrated(false)
                            ->default($payments->webhookUrl())
                            ->helperText('In Razorpay, add this URL and the matching webhook secret for the active mode.'),
                    ]),
                Section::make('Test keys')
                    ->columns(2)
                    ->schema([
                        TextInput::make('test_key_id')
                            ->label('Test key ID')
                            ->placeholder('rzp_test_…')
                            ->maxLength(120),
                        TextInput::make('test_key_secret')
                            ->label('Test key secret')
                            ->password()
                            ->revealable()
                            ->maxLength(255)
                            ->helperText($row?->test_key_secret ? 'A secret is already saved. Leave blank to keep it.' : 'Paste the test secret from Razorpay.'),
                        TextInput::make('test_webhook_secret')
                            ->label('Test webhook secret')
                            ->password()
                            ->revealable()
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->helperText($row?->test_webhook_secret ? 'A webhook secret is already saved. Leave blank to keep it.' : 'Needed so Razorpay webhooks can be checked.'),
                    ]),
                Section::make('Live keys')
                    ->columns(2)
                    ->schema([
                        TextInput::make('live_key_id')
                            ->label('Live key ID')
                            ->placeholder('rzp_live_…')
                            ->maxLength(120),
                        TextInput::make('live_key_secret')
                            ->label('Live key secret')
                            ->password()
                            ->revealable()
                            ->maxLength(255)
                            ->helperText($row?->live_key_secret ? 'A secret is already saved. Leave blank to keep it.' : 'Paste the live secret from Razorpay.'),
                        TextInput::make('live_webhook_secret')
                            ->label('Live webhook secret')
                            ->password()
                            ->revealable()
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->helperText($row?->live_webhook_secret ? 'A webhook secret is already saved. Leave blank to keep it.' : 'Use a separate live webhook secret.'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(PaymentSettingsService $payments): void
    {
        abort_unless(PaymentSettingsService::userCanManage(auth()->user()), 403);

        $payments->saveRazorpay($this->form->getState());

        $this->form->fill([
            ...$this->form->getState(),
            'test_key_secret' => '',
            'live_key_secret' => '',
            'test_webhook_secret' => '',
            'live_webhook_secret' => '',
        ]);

        Notification::make()->success()->title('Razorpay settings saved')->send();
    }
}
