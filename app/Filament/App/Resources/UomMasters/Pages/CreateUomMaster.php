<?php

namespace App\Filament\App\Resources\UomMasters\Pages;

use App\Filament\App\Resources\UomMasters\UomMasterResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUomMaster extends CreateRecord
{
    protected static string $resource = UomMasterResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
