<?php

namespace App\Imports;

use App\Models\WarehouseMaster;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class WarehouseMasterImport implements ToCollection, WithHeadingRow
{
    public int $imported = 0;

    // Excel columns: code | name | type | location
    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            if (empty($row['code'])) continue;

            WarehouseMaster::updateOrCreate(
                ['code' => strtoupper(trim($row['code']))],
                [
                    'name'              => $row['name'] ?? '',
                    'type'              => $row['type'] ?? 'normal',
                    'location'          => $row['location'] ?? null,
                    'is_cold_storage'   => strtolower($row['type'] ?? '') === 'cold',
                    'is_active'         => true,
                    'sap_warehouse_code'=> $row['code'] ?? null,
                ]
            );
            $this->imported++;
        }
    }
}
