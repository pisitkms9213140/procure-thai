<?php

namespace App\Filament\App\Resources\ItemMasters\Pages;

use App\Filament\App\Resources\ItemMasters\ItemMasterResource;
use App\Models\ItemCategory;
use App\Models\ItemMaster;
use App\Support\MappedImportAction;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListItemMasters extends ListRecords
{
    protected static string $resource = ItemMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            MappedImportAction::make('นำเข้ารายการสินค้าจาก Excel (SAP)', [
                ['key' => 'item_code', 'label' => 'รหัสสินค้า (Item No.)', 'required' => true, 'guess' => ['item no', 'item code', 'รหัสสินค้า', 'รหัส']],
                ['key' => 'item_name', 'label' => 'ชื่อสินค้า (Item Description)', 'guess' => ['item description', 'description', 'ชื่อ']],
                ['key' => 'item_name_en', 'label' => 'ชื่อ EN (Foreign Name)', 'guess' => ['foreign name', 'english']],
                ['key' => 'old_item_code', 'label' => 'รหัสสินค้าเก่า', 'guess' => ['เก่า', 'old']],
                ['key' => 'item_group', 'label' => 'กลุ่มสินค้า (Item Group)', 'guess' => ['item group']],
                ['key' => 'item_group_name', 'label' => 'ชื่อกลุ่มสินค้า (Group Name)', 'guess' => ['group name']],
                ['key' => 'uom_code', 'label' => 'หน่วยคงคลัง (กลุ่มหน่วยนับ)', 'guess' => ['กลุ่มหน่วยนับ', 'inventory unit', 'หน่วยสต๊อค']],
                ['key' => 'purchase_unit', 'label' => 'หน่วยซื้อ', 'guess' => ['หน่วยซื้อ', 'purchase uom']],
                ['key' => 'conversion_factor', 'label' => 'ตัวคูณ (Purchase Unit)', 'guess' => ['purchase unit']],
                ['key' => 'default_warehouse_code', 'label' => 'คลังเริ่มต้น (DfWHS)', 'guess' => ['dfwhs', 'default whs', 'คลัง']],
                ['key' => 'lead_time_days', 'label' => 'Lead Time', 'guess' => ['lead time']],
                ['key' => 'is_active', 'label' => 'สถานะใช้งาน (Active Y/N)', 'guess' => ['active']],
                ['key' => 'batch', 'label' => 'ติดตาม Batch (Y/N)', 'guess' => ['batch']],
            ], function (array $v, array $raw = []): bool {
                $code = trim((string) ($v['item_code'] ?? ''));
                if ($code === '') {
                    return false;
                }

                $factor = (float) ($v['conversion_factor'] ?? 0);

                // Build the category master from the item's group as we import.
                $group     = trim((string) ($v['item_group'] ?? ''));
                $groupName = trim((string) ($v['item_group_name'] ?? ''));
                if ($group !== '') {
                    ItemCategory::updateOrCreate(
                        ['code' => $group],
                        ['name' => $groupName ?: $group, 'is_active' => true]
                    );
                }

                // Match withTrashed so a soft-deleted item with the same code is
                // updated/restored instead of triggering a unique-key collision.
                $item = ItemMaster::withTrashed()->firstOrNew(['item_code' => mb_strtoupper($code)]);
                $item->fill([
                    'item_name'              => trim((string) ($v['item_name'] ?? '')) ?: $code,
                    'item_name_en'           => trim((string) ($v['item_name_en'] ?? '')) ?: null,
                    'item_type'              => 'raw_material',
                    'item_group'             => trim((string) ($v['item_group'] ?? '')) ?: null,
                    'item_group_name'        => trim((string) ($v['item_group_name'] ?? '')) ?: null,
                    'uom_code'               => trim((string) ($v['uom_code'] ?? '')) ?: null,
                    'purchase_unit'          => trim((string) ($v['purchase_unit'] ?? '')) ?: null,
                    'conversion_factor'      => $factor > 0 ? $factor : 1,
                    'default_warehouse_code' => trim((string) ($v['default_warehouse_code'] ?? '')) ?: null,
                    'old_item_code'          => trim((string) ($v['old_item_code'] ?? '')) ?: null,
                    'lead_time_days'         => (int) ($v['lead_time_days'] ?? 0),
                    'requires_lot_tracking'  => strtoupper(trim((string) ($v['batch'] ?? ''))) === 'Y',
                    'is_active'              => strtoupper(trim((string) ($v['is_active'] ?? 'Y'))) !== 'N',
                    'sap_item_code'          => $code,
                    'sap_raw'                => $raw ?: null,
                ]);
                $item->deleted_at = null;
                $item->save();

                return true;
            }, prefixFromKey: 'item_code', groupFromKey: 'item_group_name'),

            // Bulk-link vendors + last purchase price to existing items.
            MappedImportAction::make('นำเข้า Vendor สินค้า (Excel)', [
                ['key' => 'item_no', 'label' => 'รหัสสินค้า (Item No.)', 'required' => true, 'guess' => ['item no', 'item code', 'รหัสสินค้า', 'รหัส']],
                ['key' => 'vendor_id', 'label' => 'รหัส Vendor', 'required' => true, 'guess' => ['card_code', 'vendor code', 'vendor id', 'vendor', 'รหัสผู้ขาย']],
                ['key' => 'last_purchase', 'label' => 'ราคาล่าสุด', 'guess' => ['last purchase', 'price', 'ราคาล่าสุด', 'ราคา']],
            ], function (array $v, array $raw = []): bool {
                $code   = mb_strtoupper(trim((string) ($v['item_no'] ?? '')));
                $vendor = mb_strtoupper(trim((string) ($v['vendor_id'] ?? '')));
                if ($code === '' || $vendor === '') {
                    return false;
                }

                $item = ItemMaster::where('item_code', $code)->first();
                if (! $item) {
                    return false; // unknown item — skipped
                }

                $lp    = $v['last_purchase'] ?? null;
                $price = ($lp !== null && $lp !== '') ? (float) $lp : null;

                // Link vendor to item (pivot allows multiple vendors per item).
                \App\Models\ItemSupplier::updateOrCreate(
                    ['item_id' => $item->id, 'vendor_code' => $vendor],
                    $price !== null ? ['price' => $price] : []
                );

                // Set default vendor if the item has none; update last purchase price.
                $updates = [];
                if (empty($item->default_vendor_code)) {
                    $updates['default_vendor_code'] = $vendor;
                }
                if ($price !== null) {
                    $updates['last_purchase_price'] = $price;
                }
                if ($updates) {
                    $item->update($updates);
                }

                return true;
            }, name: 'importVendor', label: 'นำเข้า Vendor'),
        ];
    }
}
