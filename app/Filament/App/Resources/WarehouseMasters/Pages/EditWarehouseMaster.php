<?php

namespace App\Filament\App\Resources\WarehouseMasters\Pages;

use App\Filament\App\Resources\WarehouseMasters\WarehouseMasterResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWarehouseMaster extends EditRecord
{
    protected static string $resource = WarehouseMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
