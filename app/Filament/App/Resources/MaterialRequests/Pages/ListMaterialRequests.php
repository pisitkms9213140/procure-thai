<?php

namespace App\Filament\App\Resources\MaterialRequests\Pages;

use App\Filament\App\Resources\MaterialRequests\MaterialRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMaterialRequests extends ListRecords
{
    protected static string $resource = MaterialRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('สร้างใบขอซื้อ')];
    }
}
