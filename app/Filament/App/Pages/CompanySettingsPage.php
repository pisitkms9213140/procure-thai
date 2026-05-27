<?php

namespace App\Filament\App\Pages;

use App\Support\ThaiGeography;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class CompanySettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view                                   = 'filament.app.pages.company-settings-page';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationLabel                = 'ตั้งค่าบริษัท';
    protected static \UnitEnum|string|null $navigationGroup   = 'การตั้งค่า';
    protected static ?int    $navigationSort                  = 98;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'company_name' => tenant('company_name'),
            'company_logo' => tenant('company_logo'),
            'subdomain'    => tenant('id'),
            'tax_id'       => tenant('tax_id'),
            'branch_id'    => tenant('branch_id'),
            'address'      => tenant('address'),
            'street'       => tenant('street'),
            'province_id'  => tenant('province_id'),
            'district_id'  => tenant('district_id'),
            'postcode'     => tenant('postcode'),
            'telephone'    => tenant('telephone'),
            'email'        => tenant('email'),
            'website'      => tenant('website'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('ข้อมูลบริษัท')
                    ->description('ข้อมูลนี้จะแสดงบนหัวเอกสาร (ใบสั่งซื้อ / ใบแจ้งหนี้) และ Header ของระบบ')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('company_logo')
                            ->label('โลโก้บริษัท')
                            ->image()
                            ->disk('public')
                            ->directory('company-logos')
                            ->maxSize(2048)
                            ->helperText('แนะนำ PNG พื้นหลังโปร่งใส ขนาดไม่เกิน 2MB')
                            ->columnSpanFull(),

                        TextInput::make('company_name')
                            ->label('ชื่อบริษัท / Company Name')
                            ->placeholder('เช่น บริษัท ตัวอย่าง จำกัด')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('subdomain')
                            ->label('Subdomain')
                            ->disabled()
                            ->dehydrated(false)
                            ->suffix('.procurethai.uk'),

                        TextInput::make('tax_id')
                            ->label('เลขประจำตัวผู้เสียภาษี / Tax ID')
                            ->maxLength(13),

                        TextInput::make('branch_id')
                            ->label('รหัสสาขา / Branch ID')
                            ->placeholder('เช่น 00000 (สำนักงานใหญ่)')
                            ->maxLength(10),
                    ]),

                Section::make('ที่อยู่')
                    ->columns(2)
                    ->schema([
                        TextInput::make('address')
                            ->label('ที่อยู่ (เลขที่ / หมู่ / ซอย)')
                            ->columnSpanFull(),

                        TextInput::make('street')
                            ->label('ถนน')
                            ->columnSpanFull(),

                        Select::make('province_id')
                            ->label('จังหวัด')
                            ->options(ThaiGeography::provinces())
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('district_id', null);
                                $set('postcode', null);
                            }),

                        Select::make('district_id')
                            ->label('อำเภอ / เขต')
                            ->options(fn (Get $get) => ThaiGeography::districts($get('province_id')))
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                $set('postcode', ThaiGeography::zipForDistrict($state));
                            }),

                        TextInput::make('postcode')
                            ->label('รหัสไปรษณีย์')
                            ->maxLength(5),
                    ]),

                Section::make('ข้อมูลติดต่อ')
                    ->columns(2)
                    ->schema([
                        TextInput::make('telephone')
                            ->label('โทรศัพท์')
                            ->tel(),

                        TextInput::make('email')
                            ->label('อีเมล')
                            ->email(),

                        TextInput::make('website')
                            ->label('เว็บไซต์')
                            ->url()
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $tenant = tenant();

        $tenant->company_name = $data['company_name'];
        $tenant->company_logo = $data['company_logo'] ?? null;
        $tenant->tax_id       = $data['tax_id'] ?? null;
        $tenant->branch_id    = $data['branch_id'] ?? null;
        $tenant->address      = $data['address'] ?? null;
        $tenant->street       = $data['street'] ?? null;
        $tenant->province_id  = $data['province_id'] ?? null;
        $tenant->district_id  = $data['district_id'] ?? null;
        // Store resolved Thai names too, so document rendering needs no lookup.
        $tenant->province     = ThaiGeography::provinceName($data['province_id'] ?? null);
        $tenant->district     = ThaiGeography::districtName($data['province_id'] ?? null, $data['district_id'] ?? null);
        $tenant->postcode     = $data['postcode'] ?? null;
        $tenant->telephone    = $data['telephone'] ?? null;
        $tenant->email        = $data['email'] ?? null;
        $tenant->website      = $data['website'] ?? null;

        $tenant->save();

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
