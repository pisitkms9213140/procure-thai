<?php

namespace App\Filament\App\Resources\ItemCategories\Pages;

use App\Filament\App\Resources\ItemCategories\ItemCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListItemCategories extends ListRecords
{
    protected static string $resource = ItemCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
