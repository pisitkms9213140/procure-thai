<?php

namespace App\Filament\App\Resources\Invoices;

use App\Filament\App\Resources\Invoices\Pages\CreateInvoice;
use App\Filament\App\Resources\Invoices\Pages\EditInvoice;
use App\Filament\App\Resources\Invoices\Pages\ListInvoices;
use App\Models\Invoice;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;
    use \App\Filament\Concerns\HiddenFromVendor;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'ใบแจ้งหนี้';
    protected static ?string $modelLabel = 'ใบแจ้งหนี้';
    protected static ?string $pluralModelLabel = 'ใบแจ้งหนี้';
    protected static \UnitEnum|string|null $navigationGroup = 'จัดซื้อ';
    protected static ?int $navigationSort = 3;
    protected static ?string $recordTitleAttribute = 'invoice_number';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('ข้อมูลใบแจ้งหนี้')
                ->schema([
                    TextInput::make('invoice_number')
                        ->label('เลขที่ใบแจ้งหนี้')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(100),

                    Select::make('supplier_id')
                        ->label('ซัพพลายเออร์')
                        ->relationship('supplier', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Select::make('purchase_order_id')
                        ->label('อ้างอิง PO')
                        ->relationship('purchaseOrder', 'po_number')
                        ->searchable()
                        ->preload()
                        ->nullable(),

                    Select::make('status')
                        ->label('สถานะ')
                        ->options([
                            'pending' => 'รอตรวจสอบ',
                            'under_review' => 'กำลังตรวจสอบ',
                            'approved' => 'อนุมัติแล้ว',
                            'paid' => 'จ่ายแล้ว',
                            'rejected' => 'ปฏิเสธ',
                        ])
                        ->default('pending')
                        ->required(),

                    DatePicker::make('invoice_date')
                        ->label('วันที่ในใบแจ้งหนี้')
                        ->default(now())
                        ->required(),

                    DatePicker::make('due_date')
                        ->label('วันครบกำหนดชำระ')
                        ->required(),
                ])->columns(2),

            Section::make('มูลค่า')
                ->schema([
                    TextInput::make('subtotal')
                        ->label('รวมก่อน VAT')
                        ->numeric()
                        ->prefix('฿')
                        ->required(),

                    TextInput::make('vat_amount')
                        ->label('VAT')
                        ->numeric()
                        ->prefix('฿')
                        ->default(0),

                    TextInput::make('total_amount')
                        ->label('ยอดรวมสุทธิ')
                        ->numeric()
                        ->prefix('฿')
                        ->required(),
                ])->columns(3),

            Section::make('เอกสารและหมายเหตุ')
                ->schema([
                    FileUpload::make('file_path')
                        ->label('แนบไฟล์ใบแจ้งหนี้')
                        ->disk('public')
                        ->directory('invoices')
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                        ->maxSize(10240)
                        ->columnSpanFull(),

                    Textarea::make('notes')
                        ->label('หมายเหตุ')
                        ->rows(2)
                        ->columnSpanFull(),

                    Textarea::make('rejection_reason')
                        ->label('เหตุผลการปฏิเสธ')
                        ->rows(2)
                        ->columnSpanFull()
                        ->visible(fn ($get) => $get('status') === 'rejected'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('เลขที่ใบแจ้งหนี้')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('ซัพพลายเออร์')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('purchaseOrder.po_number')
                    ->label('อ้างอิง PO')
                    ->searchable(),

                Tables\Columns\TextColumn::make('invoice_date')
                    ->label('วันที่')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('ครบกำหนด')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn ($record) => $record->isOverdue() ? 'danger' : null),

                Tables\Columns\TextColumn::make('status')
                    ->label('สถานะ')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'under_review' => 'info',
                        'approved' => 'warning',
                        'paid' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'รอตรวจสอบ',
                        'under_review' => 'กำลังตรวจสอบ',
                        'approved' => 'อนุมัติแล้ว',
                        'paid' => 'จ่ายแล้ว',
                        'rejected' => 'ปฏิเสธ',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('ยอดรวม')
                    ->money('THB')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('สถานะ')
                    ->options([
                        'pending' => 'รอตรวจสอบ',
                        'under_review' => 'กำลังตรวจสอบ',
                        'approved' => 'อนุมัติแล้ว',
                        'paid' => 'จ่ายแล้ว',
                        'rejected' => 'ปฏิเสธ',
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
            'index' => ListInvoices::route('/'),
            'create' => CreateInvoice::route('/create'),
            'edit' => EditInvoice::route('/{record}/edit'),
        ];
    }
}
