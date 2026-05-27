<?php

namespace App\Filament\App\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

class CompanySettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view                        = 'filament.app.pages.company-settings-page';
    protected static \BackedEnum|string|null $navigationIcon  = 'heroicon-o-building-office';
    protected static ?string $navigationLabel     = 'ตั้งค่าบริษัท';
    protected static \UnitEnum|string|null $navigationGroup  = 'การตั้งค่า';
    protected static ?int    $navigationSort      = 98;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'company_name' => tenant('company_name'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Section::make('ข้อมูลบริษัท')
                    ->description('ชื่อบริษัทจะแสดงบน Header ของระบบ')
                    ->schema([
                        TextInput::make('company_name')
                            ->label('ชื่อบริษัท / Company Name')
                            ->placeholder('เช่น บริษัท ตัวอย่าง จำกัด')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        tenant()->update(['company_name' => $data['company_name']]);

        Notification::make()
            ->success()
            ->title('บันทึกข้อมูลบริษัทแล้ว')
            ->send();
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
