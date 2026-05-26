<?php

namespace App\Imports;

use App\Models\Supplier;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SupplierImport implements ToCollection, WithHeadingRow
{
    public int $imported = 0;

    // Excel columns: code | name | tax_id | email | phone | contact_person
    //                address | province | postcode | type | payment_terms
    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            if (empty($row['code'])) continue;

            Supplier::updateOrCreate(
                ['code' => strtoupper(trim($row['code']))],
                [
                    'name'           => $row['name'] ?? '',
                    'tax_id'         => $row['tax_id'] ?? null,
                    'email'          => $row['email'] ?? null,
                    'phone'          => $row['phone'] ?? null,
                    'contact_person' => $row['contact_person'] ?? null,
                    'address'        => $row['address'] ?? null,
                    'province'       => $row['province'] ?? null,
                    'postcode'       => $row['postcode'] ?? null,
                    'type'           => $row['type'] ?? 'goods',
                    'payment_terms'  => $row['payment_terms'] ?? null,
                    'status'         => 'active',
                ]
            );
            $this->imported++;
        }
    }
}
