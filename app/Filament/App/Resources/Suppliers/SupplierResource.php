<?php

namespace App\Filament\App\Resources\Suppliers;

use App\Filament\App\Resources\Suppliers\Pages\CreateSupplier;
use App\Filament\App\Resources\Suppliers\Pages\EditSupplier;
use App\Filament\App\Resources\Suppliers\Pages\ListSuppliers;
use App\Models\Supplier;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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

                    Select::make('channel')
                        ->label('ช่องทางใช้งาน')
                        ->helperText('Desktop = พนักงานบริษัท / Mobile = รายย่อย (PWA)')
                        ->options([
                            'desktop' => '💻 Desktop (บริษัท)',
                            'mobile'  => '📱 Mobile (รายย่อย — PWA)',
                            'both'    => 'ทั้งสองช่องทาง',
                        ])
                        ->default('both')
                        ->required()
                        ->disabled(fn () => auth()->user()?->isVendor() ?? false),

                    Select::make('status')
                        ->label('สถานะ')
                        ->options([
                            'active' => 'ใช้งาน',
                            'inactive' => 'ไม่ใช้งาน',
                            'blacklisted' => 'บัญชีดำ',
                        ])
                        ->default('active')
                        ->required()
                        ->disabled(fn () => auth()->user()?->isVendor() ?? false),

                    TextInput::make('payment_terms')
                        ->label('เงื่อนไขการชำระเงิน')
                        ->placeholder('เช่น Net 30, COD'),
                ])->columns(2)->columnSpanFull(),

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
                ])->columns(2)->columnSpanFull(),

            Section::make('ข้อมูลบัญชีผู้ใช้ (Account)')
                ->description('บัญชีสำหรับซัพพลายเออร์เข้าระบบ — ใช้ปุ่ม "รีเซ็ตรหัสผ่าน" (แอดมิน) หรือ "เปลี่ยนรหัสผ่าน" (ซัพพลายเออร์)')
                ->visible(fn ($record) => $record !== null)
                ->columnSpanFull()
                ->schema([
                    Placeholder::make('account_username')
                        ->label('Username (อีเมลเข้าระบบ)')
                        ->content(fn ($record) => $record?->vendorUser()?->email
                            ?? 'ยังไม่มีบัญชี — ใช้ "สร้างผู้ใช้ Vendor" หรือ "รีเซ็ตรหัสผ่าน"'),
                ]),
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
            ->actions([
                EditAction::make(),

                // Admin: reset / create the vendor login, show the new password once.
                Action::make('resetVendorPassword')
                    ->label('รีเซ็ตรหัสผ่าน')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->visible(fn () => ! (auth()->user()?->isVendor() ?? false))
                    ->requiresConfirmation()
                    ->modalDescription('สร้างบัญชี (ถ้ายังไม่มี) และตั้งรหัสผ่านใหม่ 8 หลัก — จะแสดงเพียงครั้งเดียว')
                    ->action(function (Supplier $record) {
                        $email = $record->vendorEmail();
                        $user  = $record->vendorUser();

                        if (! $user && User::where('email', $email)->exists()) {
                            Notification::make()->danger()->title('อีเมลซ้ำ')->body($email)->send();
                            return;
                        }

                        $password = Str::password(8, true, true, false);

                        if ($user) {
                            $user->update(['password' => Hash::make($password)]);
                        } else {
                            $user = User::create([
                                'name'        => $record->name,
                                'email'       => $email,
                                'role'        => User::ROLE_VENDOR,
                                'vendor_code' => $record->code,
                                'password'    => Hash::make($password),
                            ]);
                        }

                        Notification::make()->success()->title('รีเซ็ตรหัสผ่านแล้ว')
                            ->body("Username: {$user->email}\nPassword: {$password}")
                            ->persistent()->send();
                    }),

                // Vendor: change their own password (verify old → set new).
                Action::make('changePassword')
                    ->label('เปลี่ยนรหัสผ่าน')
                    ->icon('heroicon-o-lock-closed')
                    ->visible(fn (Supplier $record) => (auth()->user()?->isVendor() ?? false)
                        && auth()->user()->vendor_code === $record->code)
                    ->form([
                        TextInput::make('old_password')->label('รหัสผ่านเดิม')->password()->required(),
                        TextInput::make('new_password')->label('รหัสผ่านใหม่')->password()->required()->minLength(8),
                        TextInput::make('new_password_confirmation')->label('ยืนยันรหัสผ่านใหม่')
                            ->password()->required()->same('new_password'),
                    ])
                    ->action(function (array $data) {
                        $user = auth()->user();
                        if (! Hash::check($data['old_password'], $user->password)) {
                            Notification::make()->danger()->title('รหัสผ่านเดิมไม่ถูกต้อง')->send();
                            return;
                        }
                        $user->update(['password' => Hash::make($data['new_password'])]);
                        Notification::make()->success()->title('เปลี่ยนรหัสผ่านแล้ว')->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    // Bulk-create vendor logins (username = code w/o hyphen @ subdomain).
                    BulkAction::make('createVendorUsers')
                        ->label('สร้างผู้ใช้ Vendor')
                        ->icon('heroicon-o-user-plus')
                        ->color('success')
                        ->visible(fn () => ! (auth()->user()?->isVendor() ?? false))
                        ->requiresConfirmation()
                        ->modalDescription('สร้างบัญชีผู้ใช้สิทธิ์ Vendor ให้ซัพพลายเออร์ที่เลือก (ตั้งรหัสผ่านสุ่ม — ใช้ "รีเซ็ตรหัสผ่าน" เพื่อดู/แจกรหัสภายหลัง)')
                        ->action(function (\Illuminate\Support\Collection $records) {
                            // Pre-load existing users (avoid ~1,500 per-row queries).
                            $existingCodes  = User::whereNotNull('vendor_code')->pluck('vendor_code')->flip();
                            $existingEmails = User::pluck('email')->map(fn ($e) => strtolower((string) $e))->flip();

                            // Hash ONCE — bcrypt is the bottleneck; 778 hashes time out.
                            // These are throwaway placeholders, reset per-vendor via "รีเซ็ตรหัสผ่าน".
                            $placeholderHash = Hash::make(Str::password(8, true, true, false));

                            $created    = 0;
                            $skipped    = 0;
                            $failed     = 0;
                            $firstError = null;

                            foreach ($records as $supplier) {
                                $email = $supplier->vendorEmail();
                                if ($existingCodes->has($supplier->code) || $existingEmails->has(strtolower($email))) {
                                    $skipped++;
                                    continue;
                                }

                                try {
                                    User::create([
                                        'name'        => $supplier->name,
                                        'email'       => $email,
                                        'role'        => User::ROLE_VENDOR,
                                        'vendor_code' => $supplier->code,
                                        'password'    => $placeholderHash,
                                    ]);
                                    $existingEmails->put(strtolower($email), true); // guard in-batch dupes
                                    $created++;
                                } catch (\Throwable $e) {
                                    $failed++;
                                    $firstError ??= $e->getMessage();
                                }
                            }

                            $notes = [];
                            if ($skipped) {
                                $notes[] = "ข้าม {$skipped} ราย (มีบัญชีแล้ว)";
                            }
                            if ($failed) {
                                $notes[] = "ผิดพลาด {$failed} ราย" . ($firstError ? " ({$firstError})" : '');
                            }

                            Notification::make()->{$failed ? 'warning' : 'success'}()
                                ->title("สร้างผู้ใช้ Vendor {$created} ราย")
                                ->body($notes ? implode(' · ', $notes) : null)
                                ->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
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
