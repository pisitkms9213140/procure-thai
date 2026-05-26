<?php

namespace App\Imports;

use App\Models\ItemMaster;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ItemMasterImport implements ToCollection, WithHeadingRow
{
    public int $imported = 0;

    // Excel columns: item_code | item_name | item_name_en | item_type | item_group
    //                uom_code | default_vendor_code | last_purchase_price
    //                min_order_qty | lead_time_days | requires_lot_tracking | requires_expiry_date
    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            if (empty($row['item_code'])) continue;

            ItemMaster::updateOrCreate(
                ['item_code' => strtoupper(trim($row['item_code']))],
                [
                    'item_name'             => $row['item_name'] ?? '',
                    'item_name_en'          => $row['item_name_en'] ?? null,
                    'item_type'             => $row['item_type'] ?? 'raw_material',
                    'item_group'            => $row['item_group'] ?? null,
                    'uom_code'              => $row['uom_code'] ?? null,
                    'default_vendor_code'   => $row['default_vendor_code'] ?? null,
                    'last_purchase_price'   => $row['last_purchase_price'] ?? null,
                    'min_order_qty'         => $row['min_order_qty'] ?? 1,
                    'lead_time_days'        => $row['lead_time_days'] ?? 0,
                    'requires_lot_tracking' => !empty($row['requires_lot_tracking']),
                    'requires_expiry_date'  => !empty($row['requires_expiry_date']),
                    'sap_item_code'         => $row['item_code'] ?? null,
                    'is_active'             => true,
                ]
            );
            $this->imported++;
        }
    }
}
