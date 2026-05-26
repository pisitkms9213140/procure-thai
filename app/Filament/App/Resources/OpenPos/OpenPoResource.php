<?php

namespace App\Filament\App\Resources\OpenPos;

use App\Filament\App\Resources\OpenPos\Pages;
use App\Models\OpenPo;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class OpenPoResource extends Resource
{
    protected static ?string $model = OpenPo::class;

    protected static \BackedEnum|string|null $navigationIcon  = 'heroicon-o-document-text';
    protected static \UnitEnum|string|null   $navigationGroup = 'จัดซื้อ';
    protected static ?string $navigationLabel  = 'PO ค้างรับ';
    protected static ?string $modelLabel       = 'PO ค้างรับ';
    protected static ?string $pluralModelLabel = 'PO ค้างรับ';
    protected static ?int    $navigationSort   = 40;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('po_number')->label('เลข PO')->searchable()->sortable()->copyable(),
                Tables\Columns\TextColumn::make('vendor_code')->label('Vendor')->searchable(),
                Tables\Columns\TextColumn::make('item_code')->label('รหัสสินค้า')->searchable(),
                Tables\Columns\TextColumn::make('item_name')->label('ชื่อสินค้า')->limit(30),
                Tables\Columns\TextColumn::make('ordered_qty')->label('สั่งซื้อ')->numeric(decimalPlaces: 2),
                Tables\Columns\TextColumn::make('received_qty')->label('รับแล้ว')->numeric(decimalPlaces: 2),
                Tables\Columns\TextColumn::make('open_qty')
                    ->label('ค้างรับ')
                    ->getStateUsing(fn (OpenPo $r) => $r->ordered_qty - $r->received_qty)
                    ->numeric(decimalPlaces: 2)
                    ->color(fn ($state) => $state > 0 ? 'warning' : 'success'),
                Tables\Columns\TextColumn::make('unit_price')->label('ราคา/หน่วย')->money('THB'),
                Tables\Columns\TextColumn::make('status')->label('สถานะ')->badge()
                    ->color(fn ($state) => match($state) { 'open'=>'warning','partial'=>'info','closed'=>'success',default=>'gray' })
                    ->formatStateUsing(fn ($state) => match($state) {
                        'open'=>'ค้างรับ','partial'=>'รับบางส่วน','closed'=>'รับครบ',default=>$state,
                    }),
                Tables\Columns\TextColumn::make('required_date')->label('กำหนดรับ')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('source')->label('แหล่งข้อมูล')->badge()
                    ->color(fn ($state) => $state === 'sap_sync' ? 'primary' : 'gray')
                    ->formatStateUsing(fn ($state) => $state === 'sap_sync' ? 'SAP' : 'Excel'),
            ])
            ->defaultSort('required_date')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('สถานะ')
                    ->options(['open'=>'ค้างรับ','partial'=>'รับบางส่วน','closed'=>'รับครบ']),
                Tables\Filters\SelectFilter::make('source')->label('แหล่งข้อมูล')
                    ->options(['sap_sync'=>'SAP Sync','excel_import'=>'Excel Import']),
            ])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListOpenPos::route('/')];
    }
}
