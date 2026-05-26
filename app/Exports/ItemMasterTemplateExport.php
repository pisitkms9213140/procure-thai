<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ItemMasterTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        return [
            ['WM-001', 'แป้งสาลี All Purpose',   'Wheat Flour All Purpose', 'raw_material', 'แป้ง',    'KG',  'SUP-001', 25.00,  50,  3, 0, 0],
            ['WM-002', 'เนย',                    'Butter',                  'raw_material', 'ไขมัน',   'KG',  'SUP-002', 180.00, 10,  5, 1, 1],
            ['WM-003', 'น้ำตาลทราย',             'Sugar',                   'raw_material', 'น้ำตาล',  'KG',  'SUP-001', 30.00,  100, 2, 0, 0],
        ];
    }

    public function headings(): array
    {
        return [
            'item_code', 'item_name', 'item_name_en', 'item_type', 'item_group',
            'uom_code', 'default_vendor_code', 'last_purchase_price',
            'min_order_qty', 'lead_time_days', 'requires_lot_tracking', 'requires_expiry_date',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                  'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2563EB']]],
        ];
    }
}
