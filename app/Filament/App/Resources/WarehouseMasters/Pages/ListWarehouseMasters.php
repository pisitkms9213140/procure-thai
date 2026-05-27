<?php

namespace App\Filament\App\Resources\WarehouseMasters\Pages;

use App\Filament\App\Resources\WarehouseMasters\WarehouseMasterResource;
use App\Models\WarehouseMaster;
use App\Support\MappedImportAction;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWarehouseMasters extends ListRecords
{
    protected static string $resource = WarehouseMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            MappedImportAction::make('นำเข้าคลังสินค้าจาก Excel', [
                ['key' => 'code', 'label' => 'รหัสคลัง (code)', 'required' => true, 'guess' => ['whs_code', 'warehouse', 'code', 'รหัส']],
                ['key' => 'name', 'label' => 'ชื่อคลัง (name)', 'guess' => ['whs_name', 'name', 'ชื่อ']],
                ['key' => 'is_inactive', 'label' => 'สถานะปิดใช้งาน (Y = ปิด)', 'guess' => ['is_inactive', 'inactive', 'สถานะ']],
            ], function (array $v): bool {
                $code = trim((string) ($v['code'] ?? ''));
                if ($code === '') {
                    return false;
                }

                $inactive = strtoupper(trim((string) ($v['is_inactive'] ?? '')));
                $isActive = ! in_array($inactive, ['Y', 'YES', '1', 'TRUE'], true);

                WarehouseMaster::updateOrCreate(
                    ['code' => mb_strtoupper($code)],
                    [
                        'name'               => trim((string) ($v['name'] ?? '')) ?: $code,
                        'type'               => 'normal',
                        'is_active'          => $isActive,
                        'sap_warehouse_code' => $code,
                    ]
                );

                return true;
            }),
        ];
    }
}
