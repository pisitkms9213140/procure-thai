<?php

namespace App\Filament\App\Resources\UomMasters;

use App\Filament\App\Resources\UomMasters\Pages;
use App\Models\UomMaster;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class UomMasterResource extends Resource
{
    protected static ?string $model = UomMaster::class;

    protected static \BackedEnum|string|null $navigationIcon  = 'heroicon-o-scale';
    protected static \UnitEnum|string|null   $navigationGroup = 'ข้อมูลหลัก';
    protected static ?string $navigationLabel  = 'หน่วยนับ (UoM)';
    protected static ?string $modelLabel       = 'หน่วยนับ';
    protected static ?string $pluralModelLabel = 'หน่วยนับ';
    protected static ?int    $navigationSort   = 20;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('code')
                ->label('รหัสหน่วยนับ')->required()->maxLength(20)
                ->disabled(fn ($record) => $record !== null)->dehydrated(),
            TextInput::make('name')->label('ชื่อหน่วยนับ')->required()->maxLength(100),
            TextInput::make('sap_code')->label('รหัสใน SAP B1')->maxLength(20),
            Toggle::make('is_active')->label('ใช้งาน')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('รหัส')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->label('ชื่อหน่วยนับ')->searchable(),
                Tables\Columns\TextColumn::make('sap_code')->label('รหัส SAP')->placeholder('-'),
                Tables\Columns\IconColumn::make('is_active')->label('ใช้งาน')->boolean(),
            ])
            ->filters([Tables\Filters\TernaryFilter::make('is_active')->label('ใช้งาน')])
            ->actions([EditAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUomMasters::route('/'),
            'create' => Pages\CreateUomMaster::route('/create'),
            'edit'   => Pages\EditUomMaster::route('/{record}/edit'),
        ];
    }
}
