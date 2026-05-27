<?php

namespace App\Filament\App\Resources\UomMasters\Pages;

use App\Filament\App\Resources\UomMasters\UomMasterResource;
use App\Models\UomMaster;
use App\Support\MappedImportAction;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUomMasters extends ListRecords
{
    protected static string $resource = UomMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            MappedImportAction::make('นำเข้าหน่วยนับจาก Excel', [
                ['key' => 'code', 'label' => 'รหัสหน่วยนับ (code)', 'required' => true, 'guess' => ['uom_code', 'code', 'รหัส']],
                ['key' => 'name', 'label' => 'ชื่อหน่วยนับ (name)', 'guess' => ['uom_name', 'name', 'ชื่อ', 'หน่วย']],
                ['key' => 'sap_code', 'label' => 'รหัส SAP (uom_entry)', 'guess' => ['uom_entry', 'entry', 'sap']],
            ], function (array $v): bool {
                $code = trim((string) ($v['code'] ?? ''));
                if ($code === '') {
                    return false;
                }

                UomMaster::updateOrCreate(
                    ['code' => mb_strtoupper($code)],
                    [
                        'name'      => trim((string) ($v['name'] ?? '')) ?: $code,
                        'sap_code'  => trim((string) ($v['sap_code'] ?? '')) ?: null,
                        'is_active' => true,
                    ]
                );

                return true;
            }),
        ];
    }
}
