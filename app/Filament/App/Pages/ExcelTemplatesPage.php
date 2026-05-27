<?php

namespace App\Filament\App\Pages;

use Filament\Pages\Page;

class ExcelTemplatesPage extends Page
{
    protected string $view                        = 'filament.app.pages.excel-templates-page';
    protected static \BackedEnum|string|null $navigationIcon  = 'heroicon-o-arrow-down-tray';
    protected static ?string $navigationLabel     = 'Excel Templates';
    protected static \UnitEnum|string|null $navigationGroup  = 'การตั้งค่า';
    protected static ?int    $navigationSort      = 97;

    public function getTemplates(): array
    {
        return [
            [
                'title'       => 'หน่วยนับ (UoM Master)',
                'description' => 'Template สำหรับนำเข้าหน่วยนับ เช่น กก., ชิ้น, กล่อง',
                'icon'        => '📐',
                'route'       => route('tenant.template.download', 'uom'),
                'color'       => 'blue',
            ],
            [
                'title'       => 'รายการสินค้า (Item Master)',
                'description' => 'Template สำหรับนำเข้า Item Master วัตถุดิบและบรรจุภัณฑ์',
                'icon'        => '📦',
                'route'       => route('tenant.template.download', 'items'),
                'color'       => 'green',
            ],
            [
                'title'       => 'ผู้จัดจำหน่าย (Supplier)',
                'description' => 'Template สำหรับนำเข้าข้อมูล Vendor / Supplier',
                'icon'        => '🏭',
                'route'       => route('tenant.template.download', 'suppliers'),
                'color'       => 'purple',
            ],
            [
                'title'       => 'คลังสินค้า (Warehouse)',
                'description' => 'Template สำหรับนำเข้า Warehouse / Storage Location',
                'icon'        => '🏪',
                'route'       => route('tenant.template.download', 'warehouses'),
                'color'       => 'yellow',
            ],
            [
                'title'       => 'PO ค้างรับ (Open PO)',
                'description' => 'Template สำหรับนำเข้า Purchase Order ที่ยังค้างรับอยู่',
                'icon'        => '📋',
                'route'       => route('tenant.template.download', 'open_pos'),
                'color'       => 'orange',
            ],
        ];
    }
}
