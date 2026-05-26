<?php

namespace App\Filament\App\Resources\WarehouseMasters\Pages;

use App\Filament\App\Resources\WarehouseMasters\WarehouseMasterResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWarehouseMaster extends CreateRecord
{
    protected static string $resource = WarehouseMasterResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
