<?php

namespace App\Filament\App\Resources\ItemMasters\RelationManagers;

use App\Models\Supplier;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class VendorsRelationManager extends RelationManager
{
    protected static string $relationship = 'vendors';

    protected static ?string $title = 'ผู้จัดจำหน่ายของสินค้านี้';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Select::make('vendor_code')
                ->label('ผู้จัดจำหน่าย')
                ->options(fn () => Supplier::query()->orderBy('name')->pluck('name', 'code'))
                ->searchable()
                ->required(),
            TextInput::make('vendor_item_code')->label('รหัสสินค้าของผู้ขาย')->maxLength(50),
            TextInput::make('price')->label('ราคา')->numeric()->prefix('฿'),
            TextInput::make('lead_time_days')->label('Lead Time (วัน)')->numeric()->default(0),
            TextInput::make('min_order_qty')->label('สั่งขั้นต่ำ')->numeric()->default(1),
            Toggle::make('is_preferred')->label('ผู้ขายหลัก (Preferred)'),
            Textarea::make('notes')->label('หมายเหตุ')->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('vendor_code')
            ->columns([
                Tables\Columns\TextColumn::make('vendor_code')->label('รหัส Vendor')->searchable(),
                Tables\Columns\TextColumn::make('supplier.name')->label('ชื่อผู้จัดจำหน่าย')->placeholder('-'),
                Tables\Columns\TextColumn::make('price')->label('ราคา')->money('THB')->placeholder('-'),
                Tables\Columns\TextColumn::make('lead_time_days')->label('Lead Time')->suffix(' วัน'),
                Tables\Columns\IconColumn::make('is_preferred')->label('หลัก')->boolean(),
            ])
            ->headerActions([CreateAction::make()->label('+ เพิ่มผู้ขาย')])
            ->actions([EditAction::make(), DeleteAction::make()]);
    }
}
