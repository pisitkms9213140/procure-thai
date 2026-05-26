<?php

namespace App\Filament\App\Resources\ItemMasters\Pages;

use App\Filament\App\Resources\ItemMasters\ItemMasterResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditItemMaster extends EditRecord
{
    protected static string $resource = ItemMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
