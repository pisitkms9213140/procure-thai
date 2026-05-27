<?php

namespace App\Filament\App\Pages;

use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\EditProfile;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class ProfilePage extends EditProfile
{
    protected static ?string $navigationLabel = 'โปรไฟล์';

    public function form(Schema $schema): Schema
    {
        /** @var User $user */
        $user = auth()->user();

        $roleColor = match ($user->role ?? 'staff') {
            'manager'    => 'amber',
            'supervisor' => 'blue',
            default      => 'gray',
        };

        return $schema
            ->schema([
                // Avatar full-width at top
                FileUpload::make('avatar_url')
                    ->label('รูปโปรไฟล์')
                    ->image()
                    ->avatar()
                    ->disk('public')
                    ->directory('avatars')
                    ->columnSpanFull(),

                // Role badge (read-only)
                Placeholder::make('role_display')
                    ->label('สิทธิ์การใช้งาน')
                    ->content(fn () => new HtmlString(
                        '<span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold ring-1 ring-inset ' .
                        match (auth()->user()->role ?? 'staff') {
                            'manager'    => 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-400/10 dark:text-amber-400',
                            'supervisor' => 'bg-blue-50 text-blue-700 ring-blue-600/20 dark:bg-blue-400/10 dark:text-blue-400',
                            default      => 'bg-gray-50 text-gray-600 ring-gray-500/20 dark:bg-gray-400/10 dark:text-gray-400',
                        } . '">' .
                        (User::roleOptions()[auth()->user()->role ?? 'staff'] ?? '👤 Staff') .
                        '</span>'
                    ))
                    ->columnSpanFull(),

                TextInput::make('name')
                    ->label('ชื่อ-นามสกุล')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('อีเมล')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                TextInput::make('phone')
                    ->label('เบอร์โทรศัพท์')
                    ->tel()
                    ->maxLength(30),

                TextInput::make('password')
                    ->label('รหัสผ่านใหม่')
                    ->password()
                    ->revealable()
                    ->nullable()
                    ->minLength(8)
                    ->dehydrated(fn ($state) => filled($state))
                    ->dehydrateStateUsing(fn ($state) => \Illuminate\Support\Facades\Hash::make($state)),

                TextInput::make('passwordConfirmation')
                    ->label('ยืนยันรหัสผ่าน')
                    ->password()
                    ->revealable()
                    ->nullable()
                    ->same('password')
                    ->dehydrated(false),
            ])
            ->columns(2)
            ->statePath('data');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('บันทึกโปรไฟล์แล้ว');
    }
}
