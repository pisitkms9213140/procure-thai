<?php

namespace App\Filament\App\Resources\ItemMasters;

use App\Filament\App\Resources\ItemMasters\Pages;
use App\Filament\App\Resources\ItemMasters\RelationManagers\VendorsRelationManager;
use App\Models\ItemCategory;
use App\Models\ItemMaster;
use App\Models\Supplier;
use App\Models\UomMaster;
use App\Models\WarehouseMaster;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
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

    // ─── Vendor scoping: a vendor sees only their products, read-only ───
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        $user  = auth()->user();
        if ($user?->isVendor()) {
            $query->where('default_vendor_code', $user->vendor_code);
        }
        return $query;
    }

    public static function canCreate(): bool { return ! (auth()->user()?->isVendor() ?? false); }
    public static function canDelete($record): bool { return ! (auth()->user()?->isVendor() ?? false); }

    public static function form(Schema $schema): Schema
    {
        // Vendors may edit only barcode / min order qty / lead time; everything
        // else is locked (disabled fields are not dehydrated, so values persist).
        $locked = fn () => auth()->user()?->isVendor() ?? false;

        return $schema->schema([
            Section::make('ข้อมูลสินค้า')->columns(2)->schema([
                TextInput::make('item_code')
                    ->label('รหัสสินค้า')->required()->maxLength(50)
                    ->disabled(fn ($record) => $record !== null)->dehydrated(),
                TextInput::make('item_name')
                    ->label('ชื่อสินค้า (TH)')->required()->maxLength(255)->disabled($locked),
                TextInput::make('item_name_en')
                    ->label('ชื่อสินค้า (EN)')->maxLength(255)->disabled($locked),
                TextInput::make('barcode')
                    ->label('Barcode')->maxLength(50),
                Select::make('item_type')
                    ->label('ประเภทสินค้า')
                    ->options([
                        'raw_material'=>'วัตถุดิบ','finished_goods'=>'สินค้าสำเร็จรูป',
                        'packaging'=>'บรรจุภัณฑ์','consumable'=>'วัสดุสิ้นเปลือง','service'=>'บริการ',
                    ])
                    ->required()->default('raw_material')->disabled($locked),
                Select::make('item_group')->label('กลุ่มสินค้า')
                    ->options(fn () => ItemCategory::query()->orderBy('code')
                        ->get()->mapWithKeys(fn ($c) => [$c->code => "{$c->code} - {$c->name}"]))
                    ->searchable()
                    ->live()
                    ->disabled($locked)
                    ->afterStateUpdated(function ($state, Set $set) {
                        $cat = ItemCategory::where('code', $state)->first();
                        if ($cat) {
                            $set('item_group_name', $cat->name);
                        }
                    })
                    ->createOptionForm([
                        TextInput::make('code')->label('รหัสกลุ่ม')->required(),
                        TextInput::make('name')->label('ชื่อกลุ่มสินค้า')->required(),
                    ])
                    ->createOptionUsing(fn (array $data) => ItemCategory::create([
                        'code' => $data['code'], 'name' => $data['name'], 'is_active' => true,
                    ])->code),
                Select::make('item_group_name')->label('ชื่อกลุ่มสินค้า')
                    ->options(fn () => ItemCategory::query()->orderBy('name')->pluck('name', 'name'))
                    ->searchable()->disabled($locked),
                Select::make('uom_code')->label('หน่วยคงคลัง (หน่วยเล็ก)')
                    ->options(fn () => UomMaster::query()->orderBy('code')
                        ->get()->mapWithKeys(fn ($u) => [$u->code => "{$u->code} - {$u->name}"]))
                    ->searchable()->disabled($locked),
                Select::make('purchase_unit')->label('หน่วยซื้อ')
                    ->helperText('หน่วยที่ใช้ตอนสั่งซื้อใน PO')
                    ->options(fn () => UomMaster::query()->orderBy('code')
                        ->get()->mapWithKeys(fn ($u) => [$u->code => "{$u->code} - {$u->name}"]))
                    ->searchable()->disabled($locked),
                TextInput::make('conversion_factor')->label('ตัวคูณ')
                    ->helperText('จำนวนหน่วยคงคลังต่อ 1 หน่วยซื้อ เช่น 1 กล่อง = 12 ชิ้น → 12')
                    ->numeric()->minValue(0)->default(1)->disabled($locked),
                Select::make('default_warehouse_code')->label('คลังเริ่มต้น')
                    ->options(fn () => WarehouseMaster::query()->orderBy('code')
                        ->get()->mapWithKeys(fn ($w) => [$w->code => "{$w->code} - {$w->name}"]))
                    ->searchable()->disabled($locked),
                Select::make('default_vendor_code')->label('รหัส Vendor หลัก')
                    ->options(fn () => Supplier::query()->orderBy('code')
                        ->get()->mapWithKeys(fn ($s) => [$s->code => "{$s->code} - {$s->name}"]))
                    ->searchable()->disabled($locked), // vendor sees their own code, read-only
                TextInput::make('last_purchase_price')->label('ราคาซื้อล่าสุด')->numeric()->prefix('฿')->disabled($locked),
                TextInput::make('min_order_qty')->label('ปริมาณสั่งซื้อขั้นต่ำ')->numeric()->default(1),
                TextInput::make('lead_time_days')->label('Lead Time (วัน)')->numeric()->default(0),
            ]),
            Section::make('การควบคุม')->columns(3)->schema([
                Toggle::make('requires_lot_tracking')->label('ติดตาม Lot Number')->disabled($locked),
                Toggle::make('requires_expiry_date')->label('ติดตามวันหมดอายุ')->disabled($locked),
                Toggle::make('is_active')->label('ใช้งาน')->default(true)->disabled($locked),
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
                Tables\Columns\TextColumn::make('uom_code')->label('หน่วยคงคลัง'),
                Tables\Columns\TextColumn::make('purchase_unit')->label('หน่วยซื้อ')->placeholder('-'),
                Tables\Columns\TextColumn::make('conversion_factor')->label('ตัวคูณ')->numeric()->placeholder('-'),
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

    public static function getRelations(): array
    {
        return [
            VendorsRelationManager::class,
        ];
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
