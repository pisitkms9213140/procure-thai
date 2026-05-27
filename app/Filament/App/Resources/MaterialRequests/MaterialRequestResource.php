<?php

namespace App\Filament\App\Resources\MaterialRequests;

use App\Filament\App\Resources\MaterialRequests\Pages;
use App\Models\ItemMaster;
use App\Models\MaterialRequest;
use App\Models\Supplier;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class MaterialRequestResource extends Resource
{
    protected static ?string $model = MaterialRequest::class;

    protected static \BackedEnum|string|null $navigationIcon  = 'heroicon-o-clipboard-document-list';
    protected static \UnitEnum|string|null   $navigationGroup = 'จัดซื้อ';
    protected static ?string $navigationLabel  = 'ใบขอซื้อ (PR)';
    protected static ?string $modelLabel       = 'ใบขอซื้อ';
    protected static ?string $pluralModelLabel = 'ใบขอซื้อ';
    protected static ?int    $navigationSort   = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('ข้อมูลใบขอซื้อ')->columns(2)->schema([
                TextInput::make('mr_number')->label('เลขที่ใบขอซื้อ')
                    ->default(fn () => MaterialRequest::generateNumber())
                    ->disabled()->dehydrated(),
                Select::make('priority')->label('ความเร่งด่วน')
                    ->options(['normal' => 'ปกติ', 'urgent' => 'ด่วน', 'critical' => 'ด่วนมาก'])
                    ->default('normal')->required(),
                DatePicker::make('request_date')->label('วันที่ขอ')->default(now())->required(),
                DatePicker::make('required_date')->label('วันที่ต้องการ')->required(),
                TextInput::make('department')->label('แผนก')->maxLength(100),
                Textarea::make('notes')->label('หมายเหตุ')->columnSpanFull()->rows(2),
            ]),

            Section::make('รายการสินค้า')->schema([
                Repeater::make('items')
                    ->relationship()
                    ->label('')
                    ->columns(12)
                    ->defaultItems(1)
                    ->schema([
                        Select::make('item_code')->label('สินค้า')
                            ->options(fn () => ItemMaster::query()->orderBy('item_code')->limit(2000)
                                ->get()->mapWithKeys(fn ($i) => [$i->item_code => "{$i->item_code} - {$i->item_name}"]))
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                $item = ItemMaster::where('item_code', $state)->first();
                                if ($item) {
                                    $set('description', $item->item_name);
                                    $set('unit', $item->uom_code ?: 'กก.');
                                    if ($item->default_vendor_code) {
                                        $set('vendor_code', $item->default_vendor_code);
                                    }
                                }
                            })
                            ->columnSpan(3),
                        TextInput::make('description')->label('รายละเอียด')->required()->columnSpan(3),
                        Select::make('vendor_code')->label('ผู้ขาย')
                            ->options(fn () => Supplier::query()->orderBy('code')
                                ->get()->mapWithKeys(fn ($s) => [$s->code => "{$s->code} - {$s->name}"]))
                            ->searchable()->required()->columnSpan(3),
                        TextInput::make('unit')->label('หน่วย')->default('กก.')->columnSpan(1),
                        TextInput::make('quantity')->label('จำนวน')->numeric()->required()->columnSpan(1),
                        TextInput::make('budget_price')->label('ราคาประมาณ')->numeric()->columnSpan(1),

                        // ─── การยืนยันจากซัพพลายเออร์ (Supplier CF) ───
                        TextInput::make('confirmed_unit_price')->label('ราคายืนยัน')
                            ->helperText('ราคาที่ซัพพลายเออร์ยืนยัน')->numeric()->columnSpan(4),
                        TextInput::make('confirmed_qty')->label('จำนวนยืนยัน')->numeric()->columnSpan(4),
                        DatePicker::make('confirmed_delivery_date')->label('กำหนดส่ง')->columnSpan(4),
                    ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('mr_number')->label('เลขที่')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('request_date')->label('วันที่ขอ')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('required_date')->label('ต้องการ')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('items_count')->counts('items')->label('รายการ'),
                Tables\Columns\TextColumn::make('confirmed_progress')->label('ซัพพลายเออร์ยืนยัน')
                    ->state(fn (MaterialRequest $record) => $record->items->where('status', 'quoted')->count()
                        . '/' . $record->items->count())
                    ->badge()
                    ->color(fn (MaterialRequest $record) => $record->items->count() > 0
                        && $record->items->where('status', 'quoted')->count() === $record->items->count()
                            ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('priority')->label('ความเร่งด่วน')->badge()
                    ->formatStateUsing(fn ($state) => ['normal' => 'ปกติ', 'urgent' => 'ด่วน', 'critical' => 'ด่วนมาก'][$state] ?? $state)
                    ->color(fn ($state) => ['normal' => 'gray', 'urgent' => 'warning', 'critical' => 'danger'][$state] ?? 'gray'),
                Tables\Columns\TextColumn::make('status')->label('สถานะ')->badge()
                    ->formatStateUsing(fn ($state) => [
                        'draft' => 'ร่าง', 'open' => 'ส่งขอราคาแล้ว', 'partial' => 'บางส่วน',
                        'completed' => 'เสร็จสิ้น', 'cancelled' => 'ยกเลิก',
                    ][$state] ?? $state)
                    ->color(fn ($state) => [
                        'draft' => 'gray', 'open' => 'info', 'partial' => 'warning',
                        'completed' => 'success', 'cancelled' => 'danger',
                    ][$state] ?? 'gray'),
                Tables\Columns\TextColumn::make('createdBy.name')->label('ผู้สร้าง')->color('gray'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('สถานะ')->options([
                    'draft' => 'ร่าง', 'open' => 'ส่งขอราคาแล้ว', 'partial' => 'บางส่วน',
                    'completed' => 'เสร็จสิ้น', 'cancelled' => 'ยกเลิก',
                ]),
            ])
            ->actions([
                \Filament\Actions\Action::make('send')
                    ->label('ส่งขอราคา')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->visible(fn (MaterialRequest $record) => $record->status === 'draft')
                    ->requiresConfirmation()
                    ->action(function (MaterialRequest $record) {
                        $record->update(['status' => 'open']);
                        \Filament\Notifications\Notification::make()
                            ->success()->title('ส่งขอราคาแล้ว — รอซัพพลายเออร์ยืนยัน')->send();
                    }),

                \Filament\Actions\Action::make('supplierConfirm')
                    ->label('ยืนยันจากซัพพลายเออร์')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (MaterialRequest $record) => $record->status === 'open')
                    ->requiresConfirmation()
                    ->modalDescription('ทำเครื่องหมายว่าซัพพลายเออร์ยืนยันแล้ว — เฉพาะรายการที่กรอก "ราคายืนยัน" ไว้ (กรอกได้ในหน้าแก้ไข)')
                    ->action(function (MaterialRequest $record) {
                        $confirmed = 0;
                        foreach ($record->items as $item) {
                            if ($item->confirmed_unit_price !== null) {
                                $item->update([
                                    'status'        => 'quoted',
                                    'confirmed_qty' => $item->confirmed_qty ?? $item->quantity,
                                    'confirmed_at'  => now(),
                                ]);
                                $confirmed++;
                            }
                        }

                        if ($confirmed === 0) {
                            \Filament\Notifications\Notification::make()->warning()
                                ->title('ยังไม่มีรายการที่กรอกราคายืนยัน')
                                ->body('กรุณาแก้ไขใบขอซื้อแล้วกรอก "ราคายืนยัน" ก่อน')->send();
                            return;
                        }

                        \Filament\Notifications\Notification::make()->success()
                            ->title("บันทึกการยืนยันจากซัพพลายเออร์ {$confirmed} รายการ")->send();
                    }),

                EditAction::make(),
            ])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMaterialRequests::route('/'),
            'create' => Pages\CreateMaterialRequest::route('/create'),
            'edit'   => Pages\EditMaterialRequest::route('/{record}/edit'),
        ];
    }
}
