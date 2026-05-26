<?php

namespace App\Filament\App\Resources\ItemMasters\Pages;

use App\Filament\App\Resources\ItemMasters\ItemMasterResource;
use Filament\Resources\Pages\CreateRecord;

class CreateItemMaster extends CreateRecord
{
    protected static string $resource = ItemMasterResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
