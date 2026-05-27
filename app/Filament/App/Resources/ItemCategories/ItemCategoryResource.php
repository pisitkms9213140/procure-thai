<?php

namespace App\Filament\App\Resources\ItemCategories;

use App\Filament\App\Resources\ItemCategories\Pages;
use App\Models\ItemCategory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ItemCategoryResource extends Resource
{
    protected static ?string $model = ItemCategory::class;

    use \App\Filament\Concerns\HiddenFromVendor;

    protected static \BackedEnum|string|null $navigationIcon  = 'heroicon-o-tag';
    protected static \UnitEnum|string|null   $navigationGroup = 'ข้อมูลหลัก';
    protected static ?string $navigationLabel  = 'กลุ่มสินค้า (Category)';
    protected static ?string $modelLabel       = 'กลุ่มสินค้า';
    protected static ?string $pluralModelLabel = 'กลุ่มสินค้า';
    protected static ?int    $navigationSort   = 15;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('code')->label('รหัสกลุ่ม')->required()->maxLength(50)
                ->disabled(fn ($record) => $record !== null)->dehydrated(),
            TextInput::make('name')->label('ชื่อกลุ่มสินค้า')->required()->maxLength(255),
            Toggle::make('is_active')->label('ใช้งาน')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('รหัส')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->label('ชื่อกลุ่มสินค้า')->searchable(),
                Tables\Columns\IconColumn::make('is_active')->label('ใช้งาน')->boolean(),
            ])
            ->filters([Tables\Filters\TernaryFilter::make('is_active')->label('ใช้งาน')])
            ->actions([EditAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListItemCategories::route('/'),
            'create' => Pages\CreateItemCategory::route('/create'),
            'edit'   => Pages\EditItemCategory::route('/{record}/edit'),
        ];
    }
}
