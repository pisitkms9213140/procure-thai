<?php

namespace App\Imports;

use App\Models\UomMaster;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class UomMasterImport implements ToCollection, WithHeadingRow, WithValidation
{
    public int $imported = 0;
    public int $skipped  = 0;

    // Excel columns: code | name | sap_code
    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            if (empty($row['code'])) continue;
            UomMaster::updateOrCreate(
                ['code' => strtoupper(trim($row['code']))],
                [
                    'name'     => $row['name'] ?? $row['code'],
                    'sap_code' => $row['sap_code'] ?? null,
                    'is_active'=> true,
                ]
            );
            $this->imported++;
        }
    }

    public function rules(): array
    {
        return ['*.code' => 'required|string'];
    }
}
