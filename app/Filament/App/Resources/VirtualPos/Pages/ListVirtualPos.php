<?php

namespace App\Filament\App\Resources\VirtualPos\Pages;

use App\Filament\App\Resources\VirtualPos\VirtualPoResource;
use Filament\Resources\Pages\ListRecords;

class ListVirtualPos extends ListRecords
{
    protected static string $resource = VirtualPoResource::class;

    protected function getHeaderActions(): array
    {
        return []; // POs are created only via Manager approval on a PR
    }
}
