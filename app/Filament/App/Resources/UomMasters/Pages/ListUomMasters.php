<?php

namespace App\Filament\App\Resources\UomMasters\Pages;

use App\Filament\App\Resources\UomMasters\UomMasterResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUomMasters extends ListRecords
{
    protected static string $resource = UomMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
