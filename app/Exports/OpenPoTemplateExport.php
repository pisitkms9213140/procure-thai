<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OpenPoTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        return [
            ['PO-2024-0001', 'SUP-001', 'WM-001', 'แป้งสาลี',  'KG',  'WH-01', 500,  100, 25.00,  '2024-01-15', '2024-02-15', '1001', 'SAP-1001'],
            ['PO-2024-0001', 'SUP-001', 'WM-003', 'น้ำตาลทราย','KG',  'WH-01', 1000, 0,   30.00,  '2024-01-15', '2024-02-15', '1001', 'SAP-1001'],
            ['PO-2024-0002', 'SUP-002', 'WM-002', 'เนย',        'KG',  'WH-03', 200,  50,  180.00, '2024-01-20', '2024-03-01', '1002', 'SAP-1002'],
        ];
    }

    public function headings(): array
    {
        return [
            'po_number', 'vendor_code', 'item_code', 'item_name', 'uom_code',
            'warehouse_code', 'ordered_qty', 'received_qty', 'unit_price',
            'po_date', 'required_date', 'sap_doc_entry', 'sap_doc_num',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                  'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'D97706']]],
        ];
    }
}
