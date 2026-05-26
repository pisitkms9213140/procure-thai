<?php

namespace App\Filament\App\Resources\PurchaseOrders;

use App\Filament\App\Resources\PurchaseOrders\Pages\CreatePurchaseOrder;
use App\Filament\App\Resources\PurchaseOrders\Pages\EditPurchaseOrder;
use App\Filament\App\Resources\PurchaseOrders\Pages\ListPurchaseOrders;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'ใบสั่งซื้อ (PO)';
    protected static ?string $modelLabel = 'ใบสั่งซื้อ';
    protected static ?string $pluralModelLabel = 'ใบสั่งซื้อ';
    protected static ?string $navigationGroup = 'จัดซื้อ';
    protected static ?int $navigationSort = 2;
    protected static ?string $recordTitleAttribute = 'po_number';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('ข้อมูลใบสั่งซื้อ')
                ->schema([
                    TextInput::make('po_number')
                        ->label('เลขที่ PO')
                        ->default(fn () => PurchaseOrder::generatePoNumber())
                        ->disabled()
                        ->dehydrated()
                        ->required()
                        ->unique(ignoreRecord: true),

                    Select::make('supplier_id')
                        ->label('ซัพพลายเออร์')
                        ->relationship('supplier', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    DatePicker::make('po_date')
                        ->label('วันที่ออก PO')
                        ->default(now())
                        ->required(),

                    DatePicker::make('delivery_date')
                        ->label('วันที่ต้องการรับสินค้า'),

                    Select::make('status')
                        ->label('สถานะ')
                        ->options([
                            'draft' => 'Draft (ร่าง)',
                            'sent' => 'Sent (ส่งแล้ว)',
                            'acknowledged' => 'Acknowledged (ซัพพลายเออร์รับทราบ)',
                            'partial' => 'Partial (รับบางส่วน)',
                            'completed' => 'Completed (เสร็จสิ้น)',
                            'cancelled' => 'Cancelled (ยกเลิก)',
                        ])
                        ->default('draft')
                        ->required(),

                    TextInput::make('payment_terms')
                        ->label('เงื่อนไขการชำระเงิน')
                        ->placeholder('เช่น Net 30'),

                    Textarea::make('shipping_address')
                        ->label('ที่อยู่จัดส่ง')
                        ->rows(2)
                        ->columnSpanFull(),

                    Textarea::make('notes')
                        ->label('หมายเหตุ')
                        ->rows(2)
                        ->columnSpanFull(),
                ])->columns(2),

            Section::make('รายการสินค้า / บริการ')
                ->schema([
                    Repeater::make('items')
                        ->relationship()
                        ->schema([
                            TextInput::make('product_code')
                                ->label('รหัสสินค้า')
                                ->maxLength(50),

                            TextInput::make('description')
                                ->label('รายละเอียด')
                                ->required()
                                ->columnSpan(2),

                            TextInput::make('unit')
                                ->label('หน่วย')
                                ->default('ชิ้น')
                                ->required(),

                            TextInput::make('quantity')
                                ->label('จำนวน')
                                ->numeric()
                                ->default(1)
                                ->minValue(0.0001)
                                ->required()
                                ->live(debounce: 500)
                                ->afterStateUpdated(fn (Get $get, Set $set) => self::updateLineTotal($get, $set)),

                            TextInput::make('unit_price')
                                ->label('ราคาต่อหน่วย')
                                ->numeric()
                                ->prefix('฿')
                                ->required()
                                ->live(debounce: 500)
                                ->afterStateUpdated(fn (Get $get, Set $set) => self::updateLineTotal($get, $set)),

                            TextInput::make('discount_percent')
                                ->label('ส่วนลด %')
                                ->numeric()
                                ->default(0)
                                ->suffix('%')
                                ->live(debounce: 500)
                                ->afterStateUpdated(fn (Get $get, Set $set) => self::updateLineTotal($get, $set)),

                            TextInput::make('line_total')
                                ->label('รวม')
                                ->numeric()
                                ->prefix('฿')
                                ->disabled()
                                ->dehydrated(),
                        ])
                        ->columns(4)
                        ->defaultItems(1)
                        ->addActionLabel('+ เพิ่มรายการ')
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['description'] ?? null),
                ]),

            Section::make('สรุปมูลค่า')
                ->schema([
                    TextInput::make('subtotal')
                        ->label('รวมก่อน VAT')
                        ->numeric()
                        ->prefix('฿')
                        ->disabled()
                        ->dehydrated(),

                    TextInput::make('vat_amount')
                        ->label('VAT 7%')
                        ->numeric()
                        ->prefix('฿')
                        ->disabled()
                        ->dehydrated(),

                    TextInput::make('total_amount')
                        ->label('ยอดรวมสุทธิ')
                        ->numeric()
                        ->prefix('฿')
                        ->disabled()
                        ->dehydrated(),
                ])->columns(3),
        ]);
    }

    protected static function updateLineTotal(Get $get, Set $set): void
    {
        $qty = (float) ($get('quantity') ?? 0);
        $price = (float) ($get('unit_price') ?? 0);
        $discount = (float) ($get('discount_percent') ?? 0);
        $gross = $qty * $price;
        $set('line_total', round($gross - ($gross * $discount / 100), 2));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('po_number')
                    ->label('เลขที่ PO')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('ซัพพลายเออร์')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('po_date')
                    ->label('วันที่')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('delivery_date')
                    ->label('วันส่งมอบ')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('สถานะ')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'sent' => 'info',
                        'acknowledged' => 'warning',
                        'partial' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'sent' => 'ส่งแล้ว',
                        'acknowledged' => 'รับทราบ',
                        'partial' => 'บางส่วน',
                        'completed' => 'เสร็จสิ้น',
                        'cancelled' => 'ยกเลิก',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('ยอดรวม')
                    ->money('THB')
                    ->sortable(),

                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('ผู้สร้าง')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('สถานะ')
                    ->options([
                        'draft' => 'Draft',
                        'sent' => 'ส่งแล้ว',
                        'acknowledged' => 'รับทราบ',
                        'partial' => 'บางส่วน',
                        'completed' => 'เสร็จสิ้น',
                        'cancelled' => 'ยกเลิก',
                    ]),
                Tables\Filters\SelectFilter::make('supplier_id')
                    ->label('ซัพพลายเออร์')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('created_at', 'desc')
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
            'index' => ListPurchaseOrders::route('/'),
            'create' => CreatePurchaseOrder::route('/create'),
            'edit' => EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
