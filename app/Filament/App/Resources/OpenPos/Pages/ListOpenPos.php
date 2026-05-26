<?php

namespace App\Filament\App\Resources\OpenPos\Pages;

use App\Filament\App\Resources\OpenPos\OpenPoResource;
use Filament\Resources\Pages\ListRecords;

class ListOpenPos extends ListRecords
{
    protected static string $resource = OpenPoResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
