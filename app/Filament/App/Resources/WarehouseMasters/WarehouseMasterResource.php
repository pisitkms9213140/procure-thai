<?php

namespace App\Filament\App\Resources\WarehouseMasters;

use App\Filament\App\Resources\WarehouseMasters\Pages;
use App\Models\WarehouseMaster;
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

class WarehouseMasterResource extends Resource
{
    protected static ?string $model = WarehouseMaster::class;

    protected static \BackedEnum|string|null $navigationIcon  = 'heroicon-o-building-storefront';
    protected static \UnitEnum|string|null   $navigationGroup = 'ข้อมูลหลัก';
    protected static ?string $navigationLabel  = 'คลังสินค้า';
    protected static ?string $modelLabel       = 'คลังสินค้า';
    protected static ?string $pluralModelLabel = 'คลังสินค้า';
    protected static ?int    $navigationSort   = 30;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('ข้อมูลคลังสินค้า')->columns(2)->schema([
                TextInput::make('code')
                    ->label('รหัสคลัง')->required()->maxLength(20)
                    ->disabled(fn ($record) => $record !== null)->dehydrated(),
                TextInput::make('name')->label('ชื่อคลัง')->required()->maxLength(100),
                Select::make('type')->label('ประเภทคลัง')
                    ->options(['normal'=>'คลังทั่วไป','cold'=>'ห้องเย็น','hazmat'=>'วัตถุอันตราย','bonded'=>'คลังบอนด์'])
                    ->default('normal'),
                TextInput::make('location')->label('ที่ตั้ง / อาคาร')->maxLength(255)->columnSpanFull(),
                Toggle::make('is_cold_storage')->label('ห้องเย็น'),
                Toggle::make('is_active')->label('ใช้งาน')->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('รหัสคลัง')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->label('ชื่อคลัง')->searchable(),
                Tables\Columns\TextColumn::make('type')->label('ประเภท')->badge()
                    ->color(fn ($state) => match($state) { 'cold'=>'info','hazmat'=>'danger',default=>'gray' })
                    ->formatStateUsing(fn ($state) => match($state) {
                        'normal'=>'ทั่วไป','cold'=>'ห้องเย็น','hazmat'=>'อันตราย','bonded'=>'บอนด์',default=>$state,
                    }),
                Tables\Columns\TextColumn::make('location')->label('ที่ตั้ง')->limit(40),
                Tables\Columns\IconColumn::make('is_cold_storage')->label('เย็น')->boolean(),
                Tables\Columns\IconColumn::make('is_active')->label('ใช้งาน')->boolean(),
            ])
            ->actions([EditAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListWarehouseMasters::route('/'),
            'create' => Pages\CreateWarehouseMaster::route('/create'),
            'edit'   => Pages\EditWarehouseMaster::route('/{record}/edit'),
        ];
    }
}
