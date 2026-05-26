<?php

namespace App\Filament\App\Resources\UomMasters\Pages;

use App\Filament\App\Resources\UomMasters\UomMasterResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUomMaster extends EditRecord
{
    protected static string $resource = UomMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
