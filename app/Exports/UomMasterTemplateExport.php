<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UomMasterTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        // ข้อมูลตัวอย่าง
        return [
            ['KG',  'กิโลกรัม',  'KG'],
            ['PCS', 'ชิ้น',      'PC'],
            ['BOX', 'กล่อง',     'BX'],
            ['L',   'ลิตร',      'LT'],
        ];
    }

    public function headings(): array
    {
        return ['code', 'name', 'sap_code'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                  'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F7942']]],
        ];
    }
}
