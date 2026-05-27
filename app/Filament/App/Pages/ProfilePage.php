<?php

namespace App\Filament\App\Pages;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;

class ProfilePage extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view                       = 'filament.app.pages.profile-page';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationLabel    = 'โปรไฟล์ของฉัน';
    protected static bool $shouldRegisterNavigation = false; // hidden from sidebar; accessed via user menu

    public ?array $data = [];

    public function mount(): void
    {
        /** @var User $user */
        $user = auth()->user();

        $this->form->fill([
            'name'       => $user->name,
            'email'      => $user->email,
            'phone'      => $user->phone,
            'avatar_url' => $user->avatar_url,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        /** @var User $user */
        $user = auth()->user();

        return $schema
            ->schema([
                Section::make('รูปโปรไฟล์')->schema([
                    FileUpload::make('avatar_url')
                        ->label('')
                        ->image()
                        ->avatar()
                        ->disk('public')
                        ->directory('avatars')
                        ->columnSpanFull(),
                ]),

                Section::make('ข้อมูลส่วนตัว')
                    ->columns(2)
                    ->schema([
                        Placeholder::make('role_display')
                            ->label('สิทธิ์การใช้งาน')
                            ->content(new HtmlString(
                                '<span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold ring-1 ring-inset ' .
                                match ($user->role ?? 'staff') {
                                    'manager'    => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                    'supervisor' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                                    default      => 'bg-gray-50 text-gray-600 ring-gray-500/20',
                                } . '">' .
                                (User::roleOptions()[$user->role ?? 'staff'] ?? '👤 Staff') .
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
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->label('เบอร์โทรศัพท์')
                            ->tel()
                            ->maxLength(30)
                            ->columnSpanFull(),
                    ]),

                Section::make('เปลี่ยนรหัสผ่าน')
                    ->description('เว้นว่างไว้หากไม่ต้องการเปลี่ยน')
                    ->columns(2)
                    ->schema([
                        TextInput::make('password')
                            ->label('รหัสผ่านใหม่')
                            ->password()
                            ->revealable()
                            ->minLength(8)
                            ->nullable()
                            ->dehydrated(false),

                        TextInput::make('password_confirmation')
                            ->label('ยืนยันรหัสผ่าน')
                            ->password()
                            ->revealable()
                            ->nullable()
                            ->same('password')
                            ->dehydrated(false),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        /** @var User $user */
        $user = auth()->user();

        $update = [
            'name'       => $data['name'],
            'email'      => $data['email'],
            'phone'      => $data['phone'] ?? null,
            'avatar_url' => $data['avatar_url'] ?? null,
        ];

        if (!empty($data['password'])) {
            $update['password'] = Hash::make($data['password']);
        }

        $user->update($update);

        Notification::make()
            ->success()
            ->title('บันทึกโปรไฟล์แล้ว')
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('💾 บันทึก')
                ->color('primary')
                ->action(fn () => $this->save()),
        ];
    }
}
