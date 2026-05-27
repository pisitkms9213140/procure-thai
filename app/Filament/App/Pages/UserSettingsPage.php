<?php

namespace App\Filament\App\Pages;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserSettingsPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view                        = 'filament.app.pages.user-settings-page';
    protected static \BackedEnum|string|null $navigationIcon  = 'heroicon-o-users';
    protected static ?string $navigationLabel     = 'จัดการผู้ใช้งาน';
    protected static \UnitEnum|string|null $navigationGroup  = 'การตั้งค่า';
    protected static ?int    $navigationSort      = 96;

    public function table(Table $table): Table
    {
        return $table
            ->query(User::query())
            ->columns([
                TextColumn::make('name')
                    ->label('ชื่อ')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('อีเมล')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('เบอร์โทร')
                    ->default('—'),

                TextColumn::make('created_at')
                    ->label('วันที่สร้าง')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->actions([
                DeleteAction::make()
                    ->label('ลบ')
                    ->before(function (User $record) {
                        if ($record->id === auth()->id()) {
                            Notification::make()
                                ->warning()
                                ->title('ไม่สามารถลบตัวเองได้')
                                ->send();
                            $this->halt();
                        }
                    }),
            ])
            ->headerActions([
                \Filament\Tables\Actions\Action::make('invite')
                    ->label('+ เพิ่มผู้ใช้งาน')
                    ->color('primary')
                    ->form([
                        TextInput::make('name')
                            ->label('ชื่อ')
                            ->required(),
                        TextInput::make('email')
                            ->label('อีเมล')
                            ->email()
                            ->required()
                            ->unique(User::class, 'email'),
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
