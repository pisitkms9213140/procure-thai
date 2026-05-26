<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SupplierTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        return [
            ['SUP-001', 'บริษัท แป้งดี จำกัด',       '0101234567890', 'flour@example.com',  '02-111-2222', 'คุณสมชาย',  '123 ถนนสุขุมวิท เขตคลองเตย', 'กรุงเทพมหานคร', '10110', 'goods',    'net30'],
            ['SUP-002', 'ห้างหุ้นส่วน เนยสด',         '0209876543210', 'butter@example.com', '02-333-4444', 'คุณสมหญิง', '456 ถนนลาดพร้าว',             'กรุงเทพมหานคร', '10230', 'goods',    'net60'],
            ['SVC-001', 'บริษัท โลจิสติกส์เร็ว จำกัด', '0312345678901', 'logistics@ex.com',   '02-555-6666', 'คุณสมศักดิ์','789 ถนนพระราม 2',             'สมุทรสาคร',     '74000', 'service',  'net15'],
        ];
    }

    public function headings(): array
    {
        return [
            'code', 'name', 'tax_id', 'email', 'phone', 'contact_person',
            'address', 'province', 'postcode', 'type', 'payment_terms',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                  'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '7C3AED']]],
        ];
    }
}
