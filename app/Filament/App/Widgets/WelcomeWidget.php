<?php

namespace App\Filament\App\Widgets;

use App\Models\User;
use Filament\Widgets\Widget;

class WelcomeWidget extends Widget
{
    protected string $view    = 'filament.app.widgets.welcome-widget';
    protected static ?int $sort = -3; // above stats
    protected static bool $isLazy = false;

    public function getUserData(): array
    {
        /** @var User $user */
        $user = auth()->user();

        return [
            'name'       => $user->name,
            'email'      => $user->email,
            'role'       => $user->role ?? User::ROLE_STAFF,
            'role_label' => User::roleOptions()[$user->role ?? User::ROLE_STAFF] ?? '👤 Staff',
            'avatar_url' => $user->getFilamentAvatarUrl(),
            'initials'   => collect(explode(' ', $user->name))
                ->map(fn ($word) => strtoupper(mb_substr($word, 0, 1)))
                ->take(2)
                ->implode(''),
        ];
    }
}
