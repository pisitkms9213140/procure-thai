<?php

namespace App\Filament\Resources\Tenants;

use App\Filament\Resources\Tenants\Pages\CreateTenant;
use App\Filament\Resources\Tenants\Pages\EditTenant;
use App\Filament\Resources\Tenants\Pages\ListTenants;
use App\Models\Tenant;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

// ⚠️ 1. กลุ่ม Actions (ที่เจอใน vendor/filament/actions)
use Filament\Actions\EditAction; 
use Filament\Actions\BulkActionGroup; 
use Filament\Actions\DeleteBulkAction; 

// ⚠️ 2. กลุ่ม Layout (ที่เจอใน vendor/filament/schemas)
use Filament\Schemas\Components\Section;

// ⚠️ 3. กลุ่ม Input Fields (ที่เจอใน vendor/filament/forms)
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $recordTitleAttribute = 'company_name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                // เรียกใช้คลาสที่ Import มาจากบ้านของมันเอง
                Section::make('รายละเอียดบริษัทลูกค้า')
                    ->description('ข้อมูลส่วนนี้จะถูกใช้เพื่อระบุตัวตนและแยกฐานข้อมูลของลูกค้า')
                    ->schema([
                        TextInput::make('id')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->label('Tenant ID (Subdomain)')
                            ->placeholder('เช่น scg, siampack')
                            ->disabled(fn ($context) => $context === 'edit'),

                        TextInput::make('company_name')
                            ->required()
                            ->label('ชื่อบริษัท'),

                        Select::make('plan')
                            ->options([
                                'demo' => 'Demo (30 Days)',
                                'pro' => 'Pro (SME)',
                                'enterprise' => 'Enterprise',
                            ])
                            ->default('demo')
                            ->required()
                            ->label('แพ็กเกจการใช้งาน'),
                        Select::make('status')
                            ->options([
                                'pending' => 'Pending (รอดำเนินการ)',
                                'active' => 'Active (ใช้งานปกติ)',
                                'overdue' => 'Overdue (ค้างชำระ)',
                                'disable' => 'Disable (ระงับการใช้งาน)',
                            ])
                            ->default('pending')
                            ->required()
                            ->label('สถานะระบบ'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID / Subdomain')
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('company_name')
                    ->label('ชื่อบริษัท')
                    ->searchable(),

                Tables\Columns\TextColumn::make('plan')
                    ->label('แพ็กเกจ')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'demo' => 'gray',
                        'pro' => 'success',
                        'enterprise' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('สถานะ')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',     // สีเขียว
                        'pending' => 'warning',    // สีเหลือง
                        'overdue' => 'danger',     // สีแดง
                        'disable' => 'gray',       // สีเทา
                        default => 'gray',
                    }),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
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
            'index' => ListTenants::route('/'),
            'create' => CreateTenant::route('/create'),
            'edit' => EditTenant::route('/{record}/edit'),
        ];
    }
}