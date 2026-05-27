<?php

namespace App\Filament\App\Resources\ItemCategories\Pages;

use App\Filament\App\Resources\ItemCategories\ItemCategoryResource;
use Filament\Resources\Pages\EditRecord;

class EditItemCategory extends EditRecord
{
    protected static string $resource = ItemCategoryResource::class;
}
