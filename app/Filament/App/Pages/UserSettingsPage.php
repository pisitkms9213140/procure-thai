<?php

namespace App\Filament\App\Pages;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Actions\Action as TableAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class UserSettingsPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view                       = 'filament.app.pages.user-settings-page';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel    = 'จัดการผู้ใช้งาน';
    protected static \UnitEnum|string|null $navigationGroup = 'การตั้งค่า';
    protected static ?int    $navigationSort     = 96;

    public function table(Table $table): Table
    {
        return $table
            ->query(User::query()->orderByDesc('created_at'))
            ->columns([
                TextColumn::make('name')
                    ->label('ชื่อ')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('email')
                    ->label('อีเมล')
                    ->searchable()
                    ->sortable()
                    ->color('gray'),

                TextColumn::make('phone')
                    ->label('เบอร์โทร')
                    ->default('—')
                    ->color('gray'),

                TextColumn::make('role')
                    ->label('สิทธิ์')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => User::roleOptions()[$state] ?? $state)
                    ->color(fn (string $state) => User::roleBadgeColors()[$state] ?? 'gray'),

                TextColumn::make('created_at')
                    ->label('วันที่สร้าง')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->color('gray'),
            ])
            ->actions([
                EditAction::make()
                    ->label('แก้ไข')
                    ->form([
                        TextInput::make('name')
                            ->label('ชื่อ')
                            ->required(),

                        TextInput::make('email')
                            ->label('อีเมล')
                            ->email()
                            ->required(),

                        TextInput::make('phone')
                            ->label('เบอร์โทร')
                            ->tel(),

                        Select::make('role')
                            ->label('สิทธิ์การใช้งาน')
                            ->options(User::roleOptions())
                            ->required(),

                        TextInput::make('password')
                            ->label('รหัสผ่านใหม่ (เว้นว่างเพื่อคงเดิม)')
                            ->password()
                            ->minLength(8)
                            ->nullable()
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => \Illuminate\Support\Facades\Hash::make($state)),
                    ]),

                DeleteAction::make()
                    ->label('ลบ')
                    ->before(function (User $record, $action) {
                        if ($record->id === auth()->id()) {
                            Notification::make()
                                ->warning()
                                ->title('ไม่สามารถลบตัวเองได้')
                                ->send();
                            $action->cancel();
                        }
                    }),
            ])
            ->headerActions([
                TableAction::make('add_user')
                    ->label('+ เพิ่มผู้ใช้งาน')
                    ->color('primary')
                    ->form([
                        TextInput::make('name')
                            ->label('ชื่อ-นามสกุล')
                            ->required(),

                        TextInput::make('email')
                            ->label('อีเมล')
                            ->email()
                            ->required()
                            ->unique(User::class, 'email'),

                        TextInput::make('phone')
                            ->label('เบอร์โทร')
                            ->tel(),

                        Select::make('role')
                            ->label('สิทธิ์การใช้งาน')
                            ->options(User::roleOptions())
                            ->default(User::ROLE_STAFF)
                            ->required()
                            ->helperText('Manager = Admin เต็มสิทธิ์ | Supervisor = อนุมัติได้ | Staff = ใช้งานทั่วไป'),

                        TextInput::make('password')
                            ->label('รหัสผ่าน')
                            ->password()
                            ->required()
                            ->minLength(8),
                    ])
                    ->action(function (array $data) {
                        User::create([
                            'name'     => $data['name'],
                            'email'    => $data['email'],
                            'phone'    => $data['phone'] ?? null,
                            'role'     => $data['role'],
                            'password' => \Illuminate\Support\Facades\Hash::make($data['password']),
                        ]);

                        Notification::make()
                            ->success()
                            ->title('เพิ่มผู้ใช้งานแล้ว')
                            ->send();
                    }),
            ])
            ->emptyStateHeading('ยังไม่มีผู้ใช้งาน')
            ->emptyStateDescription('กดปุ่ม "เพิ่มผู้ใช้งาน" เพื่อเพิ่มผู้ใช้งานใหม่')
            ->emptyStateIcon('heroicon-o-users');
    }
}
