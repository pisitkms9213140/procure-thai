<?php

namespace App\Filament\App\Pages;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\EditProfile;
use Filament\Schemas\Schema;

class ProfilePage extends EditProfile
{
    protected static ?string $navigationLabel = 'โปรไฟล์';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                FileUpload::make('avatar_url')
                    ->label('รูปโปรไฟล์')
                    ->image()
                    ->avatar()
                    ->disk('public')
                    ->directory('avatars')
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
