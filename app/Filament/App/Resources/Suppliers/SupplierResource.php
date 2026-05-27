<?php

namespace App\Filament\App\Resources\Suppliers;

use App\Filament\App\Resources\Suppliers\Pages\CreateSupplier;
use App\Filament\App\Resources\Suppliers\Pages\EditSupplier;
use App\Filament\App\Resources\Suppliers\Pages\ListSuppliers;
use App\Models\Supplier;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'ซัพพลายเออร์';
    protected static ?string $modelLabel = 'ซัพพลายเออร์';
    protected static ?string $pluralModelLabel = 'ซัพพลายเออร์';
    protected static \UnitEnum|string|null $navigationGroup = 'จัดซื้อ';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'name';

    // ─── Vendor scoping: a vendor sees/edits only their own supplier ───
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        $user  = auth()->user();
        if ($user?->isVendor()) {
            $query->where('code', $user->vendor_code);
        }
        return $query;
    }

    public static function canCreate(): bool
    {
        return ! (auth()->user()?->isVendor() ?? false);
    }

    public static function canDelete($record): bool
    {
        return ! (auth()->user()?->isVendor() ?? false);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('ข้อมูลบริษัท')
                ->schema([
                    TextInput::make('code')
                        ->label('รหัสซัพพลายเออร์')
                        ->default(fn () => Supplier::generateCode())
                        ->disabled()
                        ->dehydrated()
                        ->required()
                        ->unique(ignoreRecord: true),

                    TextInput::make('name')
                        ->label('ชื่อบริษัท / ร้านค้า')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('tax_id')
                        ->label('เลขประจำตัวผู้เสียภาษี')
                        ->maxLength(13)
                        ->unique(ignoreRecord: true),

                    Select::make('type')
                        ->label('ประเภทสินค้า/บริการ')
                        ->options([
                            'goods' => 'สินค้า',
                            'service' => 'บริการ',
                            'both' => 'สินค้าและบริการ',
                        ])
                        ->required(),

                    Select::make('status')
                        ->label('สถานะ')
                        ->options([
                            'active' => 'ใช้งาน',
                            'inactive' => 'ไม่ใช้งาน',
                            'blacklisted' => 'บัญชีดำ',
                        ])
                        ->default('active')
                        ->required(),

                    TextInput::make('payment_terms')
                        ->label('เงื่อนไขการชำระเงิน')
                        ->placeholder('เช่น Net 30, COD'),
                ])->columns(2),

            Section::make('ข้อมูลติดต่อ')
                ->schema([
                    TextInput::make('contact_person')
                        ->label('ผู้ติดต่อ')
                        ->maxLength(255),

                    TextInput::make('email')
                        ->label('อีเมล')
                        ->email(),

                    TextInput::make('phone')
                        ->label('เบอร์โทรศัพท์')
                        ->tel()
                        ->maxLength(20),

                    TextInput::make('province')
                        ->label('จังหวัด'),

                    TextInput::make('postcode')
                        ->label('รหัสไปรษณีย์')
                        ->maxLength(10),

                    Textarea::make('address')
                        ->label('ที่อยู่')
                        ->rows(3)
                        ->columnSpanFull(),

                    Textarea::make('notes')
                        ->label('หมายเหตุ')
                        ->rows(2)
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('รหัส')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('ชื่อบริษัท')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('contact_person')
                    ->label('ผู้ติดต่อ')
                    ->searchable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('โทร')
                    ->copyable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('ประเภท')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'goods' => 'info',
                        'service' => 'warning',
                        'both' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'goods' => 'สินค้า',
                        'service' => 'บริการ',
                        'both' => 'สินค้า+บริการ',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('สถานะ')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'gray',
                        'blacklisted' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'ใช้งาน',
                        'inactive' => 'ไม่ใช้งาน',
                        'blacklisted' => 'บัญชีดำ',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('purchase_orders_count')
                    ->label('PO')
                    ->counts('purchaseOrders')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('วันที่เพิ่ม')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('สถานะ')
                    ->options([
                        'active' => 'ใช้งาน',
                        'inactive' => 'ไม่ใช้งาน',
                        'blacklisted' => 'บัญชีดำ',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->label('ประเภท')
                    ->options([
                        'goods' => 'สินค้า',
                        'service' => 'บริการ',
                        'both' => 'สินค้า+บริการ',
                    ]),
            ])
            ->actions([EditAction::make()])
            ->bulkActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSuppliers::route('/'),
            'create' => CreateSupplier::route('/create'),
            'edit' => EditSupplier::route('/{record}/edit'),
        ];
    }
}
