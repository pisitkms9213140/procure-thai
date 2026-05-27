<?php

namespace App\Filament\App\Pages;

use App\Models\IntegrationSetting;
use App\Services\SapB1Service;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions as ActionsContainer;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class IntegrationSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;
    use \App\Filament\Concerns\HiddenFromVendor;

    protected string $view                = 'filament.app.pages.integration-settings-page';
    protected static \BackedEnum|string|null $navigationIcon  = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'การตั้งค่า SAP';
    protected static \UnitEnum|string|null $navigationGroup = 'การตั้งค่า';
    protected static ?int    $navigationSort  = 99;

    public ?array $data = [];

    public function mount(): void
    {
        $settings = IntegrationSetting::first();
        $this->form->fill([
            'integration_mode'        => $settings?->integration_mode ?? 'excel',
            'sap_service_layer_url'   => $settings?->sap_service_layer_url,
            'sap_company_db'          => $settings?->sap_company_db,
            'sap_username'            => $settings?->sap_username,
            'sap_connection_verified' => $settings?->sap_connection_verified,
            'sap_last_synced_at'      => $settings?->sap_last_synced_at?->format('d/m/Y H:i'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('โหมดการเชื่อมต่อ')->schema([
                    Select::make('integration_mode')
                        ->label('วิธีเชื่อมต่อ SAP B1')
                        ->options([
                            'sap_api' => '🔗 SAP B1 Service Layer API',
                            'excel'   => '📊 Excel Import / Export',
                        ])
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, Set $set) {
                            if ($state === 'sap_api') {
                                Notification::make()
                                    ->warning()
                                    ->title('SAP B1 ยังไม่พร้อมใช้งาน')
                                    ->body('ขณะนี้ระบบรองรับเฉพาะการนำเข้า/ส่งออกผ่าน Excel เท่านั้น การเชื่อมต่อ SAP B1 Service Layer อยู่ระหว่างการพัฒนา')
                                    ->persistent()
                                    ->send();

                                // Revert — SAP mode is not selectable yet.
                                $set('integration_mode', 'excel');
                            }
                        }),
                ]),

                Section::make('SAP B1 Service Layer')
                    ->columns(2)
                    ->schema([
                        TextInput::make('sap_service_layer_url')
                            ->label('Service Layer URL')
                            ->columnSpanFull()
                            ->required(),

                        TextInput::make('sap_company_db')
                            ->label('Company DB')
                            ->required(),

                        TextInput::make('sap_username')
                            ->label('Username')
                            ->required(),

                        TextInput::make('sap_password')
                            ->label('Password (เว้นว่างเพื่อคงค่าเดิม)')
                            ->password()
                            ->revealable(),

                        Placeholder::make('sap_connection_verified')
                            ->label('สถานะการเชื่อมต่อ')
                            ->content(fn (Get $get) => $get('sap_connection_verified')
                                ? '✅ เชื่อมต่อสำเร็จ'
                                : '❌ ยังไม่ได้ทดสอบ'),

                        Placeholder::make('sap_last_synced_at')
                            ->label('Sync ล่าสุด'),

                        ActionsContainer::make([
                            Action::make('testConnection')
                                ->label('🔌 ทดสอบการเชื่อมต่อ')
                                ->color('info')
                                ->action(function (Get $get) {
                                    $tmp                        = new IntegrationSetting();
                                    $tmp->sap_service_layer_url = $get('sap_service_layer_url');
                                    $tmp->sap_company_db        = $get('sap_company_db');
                                    $tmp->sap_username          = $get('sap_username');
                                    $tmp->sap_password          = $get('sap_password') ?: IntegrationSetting::first()?->getSapPasswordDecrypted();

                                    $result = (new SapB1Service($tmp))->testConnection();

                                    if ($result['success']) {
                                        IntegrationSetting::first()?->update(['sap_connection_verified' => true]);
                                        Notification::make()->title('เชื่อมต่อสำเร็จ ✅')->success()->send();
                                    } else {
                                        Notification::make()->title('เชื่อมต่อล้มเหลว ❌')->body($result['message'])->danger()->send();
                                    }
                                }),
                        ])->columnSpanFull(),
                    ])
                    ->visible(fn (Get $get) => $get('integration_mode') === 'sap_api'),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data     = $this->form->getState();
        $settings = IntegrationSetting::firstOrNew([]);

        $settings->integration_mode = $data['integration_mode'];

        if ($data['integration_mode'] === 'sap_api') {
            $settings->sap_service_layer_url = $data['sap_service_layer_url'] ?? null;
            $settings->sap_company_db        = $data['sap_company_db'] ?? null;
            $settings->sap_username          = $data['sap_username'] ?? null;

            if (!empty($data['sap_password'])) {
                $settings->sap_password = $data['sap_password'];
            }
        }

        $settings->save();
        tenant()->update(['integration_mode' => $data['integration_mode']]);

        Notification::make()->title('บันทึกการตั้งค่าแล้ว')->success()->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('💾 บันทึก')
                ->color('primary')
                ->action(fn () => $this->save()),
        ];
    }
}
