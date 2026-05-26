<?php

namespace App\Filament\App\Resources\WarehouseMasters\Pages;

use App\Filament\App\Resources\WarehouseMasters\WarehouseMasterResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWarehouseMasters extends ListRecords
{
    protected static string $resource = WarehouseMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
