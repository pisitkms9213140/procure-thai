<?php

namespace App\Filament\Concerns;

/**
 * Hide a Resource/Page from users with the 'vendor' role (and block direct
 * access). Only affects vendors — everyone else is unchanged.
 */
trait HiddenFromVendor
{
    public static function canAccess(): bool
    {
        return ! (auth()->user()?->isVendor() ?? false);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return ! (auth()->user()?->isVendor() ?? false);
    }
}
