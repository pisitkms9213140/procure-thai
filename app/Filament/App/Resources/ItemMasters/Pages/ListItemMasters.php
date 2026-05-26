<?php

namespace App\Filament\App\Resources\ItemMasters\Pages;

use App\Filament\App\Resources\ItemMasters\ItemMasterResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListItemMasters extends ListRecords
{
    protected static string $resource = ItemMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
