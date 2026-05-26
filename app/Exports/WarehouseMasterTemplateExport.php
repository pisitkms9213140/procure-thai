<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WarehouseMasterTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        return [
            ['WH-01', 'คลังสินค้าหลัก',    'normal',    'อาคาร A ถนนพระราม 2'],
            ['WH-02', 'คลังวัตถุดิบ',      'normal',    'อาคาร B'],
            ['WH-03', 'คลังห้องเย็น',       'cold',      'อาคาร C ชั้น 1'],
        ];
    }

    public function headings(): array
    {
        return ['code', 'name', 'type', 'location'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                  'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'DC2626']]],
        ];
    }
}
