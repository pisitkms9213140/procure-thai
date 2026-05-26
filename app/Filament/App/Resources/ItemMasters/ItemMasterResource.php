<?php

namespace App\Filament\App\Resources\ItemMasters;

use App\Filament\App\Resources\ItemMasters\Pages;
use App\Models\ItemMaster;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ItemMasterResource extends Resource
{
    protected static ?string $model = ItemMaster::class;

    protected static \BackedEnum|string|null $navigationIcon  = 'heroicon-o-cube';
    protected static \UnitEnum|string|null   $navigationGroup = 'ข้อมูลหลัก';
    protected static ?string $navigationLabel  = 'รายการสินค้า';
    protected static ?string $modelLabel       = 'สินค้า';
    protected static ?string $pluralModelLabel = 'รายการสินค้า';
    protected static ?int    $navigationSort   = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('ข้อมูลสินค้า')->columns(2)->schema([
                TextInput::make('item_code')
                    ->label('รหัสสินค้า')->required()->maxLength(50)
                    ->disabled(fn ($record) => $record !== null)->dehydrated(),
                TextInput::make('item_name')
                    ->label('ชื่อสินค้า (TH)')->required()->maxLength(255),
                TextInput::make('item_name_en')
                    ->label('ชื่อสินค้า (EN)')->maxLength(255),
                Select::make('item_type')
                    ->label('ประเภทสินค้า')
                    ->options([
                        'raw_material'=>'วัตถุดิบ','finished_goods'=>'สินค้าสำเร็จรูป',
                        'packaging'=>'บรรจุภัณฑ์','consumable'=>'วัสดุสิ้นเปลือง','service'=>'บริการ',
                    ])
                    ->required()->default('raw_material'),
                TextInput::make('item_group')->label('กลุ่มสินค้า')->maxLength(100),
                TextInput::make('uom_code')->label('หน่วยนับ')->maxLength(20),
                TextInput::make('default_vendor_code')->label('รหัส Vendor หลัก')->maxLength(50),
                TextInput::make('last_purchase_price')->label('ราคาซื้อล่าสุด')->numeric()->prefix('฿'),
                TextInput::make('min_order_qty')->label('ปริมาณสั่งซื้อขั้นต่ำ')->numeric()->default(1),
                TextInput::make('lead_time_days')->label('Lead Time (วัน)')->numeric()->default(0),
            ]),
            Section::make('การควบคุม')->columns(3)->schema([
                Toggle::make('requires_lot_tracking')->label('ติดตาม Lot Number'),
                Toggle::make('requires_expiry_date')->label('ติดตามวันหมดอายุ'),
                Toggle::make('is_active')->label('ใช้งาน')->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('item_code')->label('รหัสสินค้า')->searchable()->sortable()->copyable(),
                Tables\Columns\TextColumn::make('item_name')->label('ชื่อสินค้า')->searchable()->limit(40),
                Tables\Columns\TextColumn::make('item_type')->label('ประเภท')->badge()
                    ->color(fn ($state) => match($state) {
                        'raw_material'=>'warning','finished_goods'=>'success','packaging'=>'info',default=>'gray',
                    })
                    ->formatStateUsing(fn ($state) => match($state) {
                        'raw_material'=>'วัตถุดิบ','finished_goods'=>'สำเร็จรูป',
                        'packaging'=>'บรรจุภัณฑ์','consumable'=>'วัสดุสิ้นเปลือง','service'=>'บริการ',default=>$state,
                    }),
                Tables\Columns\TextColumn::make('uom_code')->label('UoM'),
                Tables\Columns\TextColumn::make('default_vendor_code')->label('Vendor หลัก'),
                Tables\Columns\TextColumn::make('last_purchase_price')->label('ราคาล่าสุด')->money('THB')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label('ใช้งาน')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('item_type')->label('ประเภท')
                    ->options([
                        'raw_material'=>'วัตถุดิบ','finished_goods'=>'สำเร็จรูป',
                        'packaging'=>'บรรจุภัณฑ์','consumable'=>'วัสดุสิ้นเปลือง','service'=>'บริการ',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')->label('ใช้งาน'),
            ])
            ->actions([EditAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListItemMasters::route('/'),
            'create' => Pages\CreateItemMaster::route('/create'),
            'edit'   => Pages\EditItemMaster::route('/{record}/edit'),
        ];
    }
}
