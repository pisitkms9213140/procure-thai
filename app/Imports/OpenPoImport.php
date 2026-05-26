<?php

namespace App\Imports;

use App\Models\OpenPo;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class OpenPoImport implements ToCollection, WithHeadingRow
{
    public int $imported = 0;

    // Excel columns: po_number | vendor_code | item_code | item_name | uom_code
    //                warehouse_code | ordered_qty | received_qty | unit_price
    //                po_date | required_date | sap_doc_entry | sap_doc_num
    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            if (empty($row['po_number']) || empty($row['item_code'])) continue;

            $ordered  = (float) ($row['ordered_qty']  ?? 0);
            $received = (float) ($row['received_qty'] ?? 0);
            $open     = $ordered - $received;
            if ($open <= 0) continue; // ไม่ import PO ที่รับครบแล้ว

            OpenPo::updateOrCreate(
                [
                    'po_number'  => trim($row['po_number']),
                    'item_code'  => strtoupper(trim($row['item_code'])),
                ],
                [
                    'vendor_code'    => strtoupper(trim($row['vendor_code'] ?? '')),
                    'item_name'      => $row['item_name'] ?? '',
                    'uom_code'       => $row['uom_code'] ?? null,
                    'warehouse_code' => $row['warehouse_code'] ?? null,
                    'ordered_qty'    => $ordered,
                    'received_qty'   => $received,
                    'unit_price'     => $row['unit_price'] ?? 0,
                    'po_date'        => $this->parseDate($row['po_date'] ?? null),
                    'required_date'  => $this->parseDate($row['required_date'] ?? null),
                    'sap_doc_entry'  => $row['sap_doc_entry'] ?? null,
                    'sap_doc_num'    => $row['sap_doc_num'] ?? null,
                    'status'         => $received > 0 ? 'partial' : 'open',
                    'source'         => 'excel_import',
                    'imported_at'    => now(),
                ]
            );
            $this->imported++;
        }
    }

    private function parseDate($value): ?string
    {
        if (empty($value)) return null;
        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }
}
