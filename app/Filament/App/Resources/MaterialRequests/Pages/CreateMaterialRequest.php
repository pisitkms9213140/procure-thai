<?php

namespace App\Filament\App\Resources\MaterialRequests\Pages;

use App\Filament\App\Resources\MaterialRequests\MaterialRequestResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMaterialRequest extends CreateRecord
{
    protected static string $resource = MaterialRequestResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['status'] ??= 'draft';

        return $data;
    }
}
