<?php

namespace App\Filament\App\Resources\OpenPos\Pages;

use App\Filament\App\Resources\OpenPos\OpenPoResource;
use App\Models\OpenPo;
use App\Support\MappedImportAction;
use Carbon\Carbon;
use Filament\Resources\Pages\ListRecords;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ListOpenPos extends ListRecords
{
    protected static string $resource = OpenPoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            MappedImportAction::make('นำเข้า PO ค้างรับจาก Excel', [
                ['key' => 'po_number', 'label' => 'เลขที่เอกสาร (po_number)', 'required' => true, 'guess' => ['document number', 'doc', 'po', 'เลขที่']],
                ['key' => 'vendor_code', 'label' => 'รหัส Vendor', 'guess' => ['vendor code', 'customer/vendor', 'vendor', 'card']],
                ['key' => 'item_code', 'label' => 'รหัสสินค้า (item_code)', 'required' => true, 'guess' => ['item no', 'item code', 'รหัส']],
                ['key' => 'item_name', 'label' => 'ชื่อสินค้า', 'guess' => ['description', 'item/service', 'ชื่อ']],
                ['key' => 'uom_code', 'label' => 'หน่วยนับ', 'guess' => ['uom', 'หน่วย']],
                ['key' => 'warehouse_code', 'label' => 'รหัสคลัง', 'guess' => ['warehousecode', 'warehouse code', 'whs', 'คลัง']],
                ['key' => 'ordered_qty', 'label' => 'จำนวนสั่ง (Quantity)', 'guess' => ['quantity', 'qty', 'จำนวน']],
                ['key' => 'remaining_qty', 'label' => 'จำนวนค้างรับ (Remaining Open Qty)', 'guess' => ['remaining', 'open quantity', 'ค้าง']],
                ['key' => 'unit_price', 'label' => 'ราคาต่อหน่วย (Price)', 'guess' => ['price', 'ราคา']],
                ['key' => 'po_date', 'label' => 'วันที่เอกสาร (Posting Date)', 'guess' => ['posting date', 'doc date', 'วันที่']],
                ['key' => 'required_date', 'label' => 'วันที่ต้องการรับ (Delivery Date)', 'guess' => ['delivery date', 'required', 'กำหนดส่ง']],
            ], function (array $v): bool {
                $poNumber = static::docNumber($v['po_number'] ?? null);
                $itemCode = trim((string) ($v['item_code'] ?? ''));
                if ($poNumber === '' || $itemCode === '') {
                    return false;
                }

                $ordered   = (float) ($v['ordered_qty'] ?? 0);
                $remaining = isset($v['remaining_qty']) && $v['remaining_qty'] !== null && $v['remaining_qty'] !== ''
                    ? (float) $v['remaining_qty']
                    : $ordered;

                if ($remaining <= 0) {
                    return false; // already fully received
                }

                $received = max(0, $ordered - $remaining);

                OpenPo::updateOrCreate(
                    ['po_number' => $poNumber, 'item_code' => mb_strtoupper($itemCode)],
                    [
                        'vendor_code'    => mb_strtoupper(trim((string) ($v['vendor_code'] ?? ''))),
                        'item_name'      => trim((string) ($v['item_name'] ?? '')),
                        'uom_code'       => trim((string) ($v['uom_code'] ?? '')) ?: null,
                        'warehouse_code' => trim((string) ($v['warehouse_code'] ?? '')) ?: null,
                        'ordered_qty'    => $ordered,
                        'received_qty'   => $received,
                        'unit_price'     => (float) ($v['unit_price'] ?? 0),
                        // po_date is NOT NULL in the schema — fall back to today.
                        'po_date'        => static::parseDate($v['po_date'] ?? null) ?? now()->format('Y-m-d'),
                        'required_date'  => static::parseDate($v['required_date'] ?? null),
                        'status'         => $received > 0 ? 'partial' : 'open',
                        'source'         => 'excel_import',
                        'imported_at'    => now(),
                    ]
                );

                return true;
            }),
        ];
    }

    /** Document numbers come through as floats (Excel scientific notation) — keep the integer form. */
    protected static function docNumber(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        if (is_numeric($value)) {
            return (string) (int) $value;
        }
        return trim((string) $value);
    }

    /** Handle Excel date serials and Thai "d.m.y" / "d.m.Y" text dates. */
    protected static function parseDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value))->format('Y-m-d');
            } catch (\Throwable) {
                // fall through
            }
        }

        $value = trim((string) $value);
        foreach (['d.m.y', 'd.m.Y', 'd/m/y', 'd/m/Y'] as $fmt) {
            try {
                return Carbon::createFromFormat($fmt, $value)->format('Y-m-d');
            } catch (\Throwable) {
                // try next format
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}
