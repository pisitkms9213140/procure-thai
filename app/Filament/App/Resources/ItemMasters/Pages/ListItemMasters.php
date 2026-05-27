<?php

namespace App\Filament\App\Resources\ItemMasters\Pages;

use App\Filament\App\Resources\ItemMasters\ItemMasterResource;
use App\Models\ItemMaster;
use App\Support\MappedImportAction;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListItemMasters extends ListRecords
{
    protected static string $resource = ItemMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            MappedImportAction::make('นำเข้ารายการสินค้าจาก Excel', [
                ['key' => 'item_code', 'label' => 'รหัสสินค้า (item_code)', 'required' => true, 'guess' => ['item_code', 'code', 'รหัส']],
                ['key' => 'item_name', 'label' => 'ชื่อสินค้า (item_name)', 'guess' => ['item_name', 'name', 'ชื่อ']],
                ['key' => 'uom_code', 'label' => 'หน่วยนับ (uom_code)', 'guess' => ['uom_code', 'uom', 'หน่วย']],
            ], function (array $v): bool {
                $code = trim((string) ($v['item_code'] ?? ''));
                if ($code === '') {
                    return false;
                }

                ItemMaster::updateOrCreate(
                    ['item_code' => mb_strtoupper($code)],
                    [
                        'item_name'     => trim((string) ($v['item_name'] ?? '')) ?: $code,
                        'item_type'     => 'raw_material',
                        'uom_code'      => trim((string) ($v['uom_code'] ?? '')) ?: null,
                        'sap_item_code' => $code,
                        'is_active'     => true,
                    ]
                );

                return true;
            }),
        ];
    }
}
