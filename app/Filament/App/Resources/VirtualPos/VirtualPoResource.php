<?php

namespace App\Filament\App\Resources\VirtualPos;

use App\Filament\App\Resources\VirtualPos\Pages;
use App\Models\VirtualPo;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class VirtualPoResource extends Resource
{
    protected static ?string $model = VirtualPo::class;

    protected static \BackedEnum|string|null $navigationIcon  = 'heroicon-o-document-check';
    protected static \UnitEnum|string|null   $navigationGroup = 'จัดซื้อ';
    protected static ?string $navigationLabel  = 'ใบสั่งซื้อ (PO)';
    protected static ?string $modelLabel       = 'ใบสั่งซื้อ';
    protected static ?string $pluralModelLabel = 'ใบสั่งซื้อ';
    protected static ?int    $navigationSort   = 5;

    // ─── Vendor scoping: a vendor sees only their own POs ───
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        $user  = auth()->user();
        if ($user?->isVendor()) {
            $query->where('vendor_code', $user->vendor_code);
        }
        return $query;
    }

    public static function canCreate(): bool { return false; } // created via PR approval only
    public static function canDelete($record): bool { return ! (auth()->user()?->isVendor() ?? false); }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('ข้อมูล PO')->columns(2)->schema([
                TextInput::make('vpo_number')->label('เลขที่ PO')->disabled()->dehydrated(),
                TextInput::make('vendor_code')->label('รหัส Vendor')->disabled()->dehydrated(),
                DatePicker::make('po_date')->label('วันที่ออก PO')->disabled()->dehydrated(),
                DatePicker::make('expected_delivery_date')->label('วันส่งมอบที่คาดไว้'),
                TextInput::make('unit_price')->label('ราคาต่อหน่วย')->numeric()->disabled()->dehydrated(),
                TextInput::make('ordered_qty')->label('จำนวนสั่ง')->numeric()->disabled()->dehydrated(),
                TextInput::make('total_amount')->label('ยอดรวม')->numeric()->prefix('฿')->disabled()->dehydrated(),
                TextInput::make('sap_po_number')->label('เลข PO ใน SAP')
                    ->helperText('กรอกเมื่อ Sync เข้า SAP เรียบร้อย'),
            ]),

            Section::make('สถานะ')->columns(2)->schema([
                TextInput::make('status')->label('สถานะ')->disabled()->dehydrated(),
                Placeholder::make('approver_label')->label('ผู้อนุมัติ')
                    ->content(fn ($record) => $record?->approver?->name ?? '-'),
                Textarea::make('notes')->label('หมายเหตุ')->rows(3)->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('vpo_number')->label('เลขที่ PO')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('vendor_code')->label('Vendor')->searchable(),
                Tables\Columns\TextColumn::make('po_date')->label('วันที่ออก')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('expected_delivery_date')->label('นัดส่ง')->date('d/m/Y'),
                Tables\Columns\TextColumn::make('ordered_qty')->label('จำนวน')->numeric()->alignRight(),
                Tables\Columns\TextColumn::make('total_amount')->label('ยอดรวม')->money('THB')->alignRight(),
                Tables\Columns\TextColumn::make('status')->label('สถานะ')->badge()
                    ->formatStateUsing(fn ($state) => [
                        'pending'=>'รออนุมัติ/รอส่ง','partial'=>'รับบางส่วน',
                        'completed'=>'รับครบแล้ว','cancelled'=>'ยกเลิก',
                    ][$state] ?? $state)
                    ->color(fn ($state) => [
                        'pending'=>'warning','partial'=>'info',
                        'completed'=>'success','cancelled'=>'danger',
                    ][$state] ?? 'gray'),
                Tables\Columns\TextColumn::make('sap_po_number')->label('SAP PO')->placeholder('-')->color('gray'),
            ])
            ->defaultSort('po_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'pending'=>'รออนุมัติ/รอส่ง','partial'=>'รับบางส่วน',
                    'completed'=>'รับครบแล้ว','cancelled'=>'ยกเลิก',
                ]),
            ])
            ->actions([EditAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVirtualPos::route('/'),
            'edit'  => Pages\EditVirtualPo::route('/{record}/edit'),
        ];
    }
}
