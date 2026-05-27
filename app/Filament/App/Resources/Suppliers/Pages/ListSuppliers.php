<?php

namespace App\Filament\App\Resources\Suppliers\Pages;

use App\Filament\App\Resources\Suppliers\SupplierResource;
use App\Models\Supplier;
use App\Support\MappedImportAction;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSuppliers extends ListRecords
{
    protected static string $resource = SupplierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            MappedImportAction::make('นำเข้าผู้จัดจำหน่ายจาก Excel', [
                ['key' => 'code', 'label' => 'รหัส Vendor (code)', 'required' => true, 'guess' => ['card_code', 'vendor', 'code', 'รหัส']],
                ['key' => 'name', 'label' => 'ชื่อ Vendor (name)', 'guess' => ['card_name', 'name', 'ชื่อ']],
                ['key' => 'tax_id', 'label' => 'เลขผู้เสียภาษี (tax_id)', 'guess' => ['tax_id', 'tax', 'ภาษี']],
                ['key' => 'phone', 'label' => 'โทรศัพท์ (phone)', 'guess' => ['phone', 'tel', 'โทร']],
                ['key' => 'email', 'label' => 'อีเมล (email)', 'guess' => ['email', 'mail', 'อีเมล']],
            ], function (array $v): bool {
                $code = trim((string) ($v['code'] ?? ''));
                if ($code === '') {
                    return false;
                }

                // Match withTrashed so a soft-deleted vendor with the same code is
                // updated/restored instead of triggering a unique-key collision.
                $supplier = Supplier::withTrashed()->firstOrNew(['code' => mb_strtoupper($code)]);
                $supplier->fill([
                    'name'   => trim((string) ($v['name'] ?? '')) ?: $code,
                    'tax_id' => trim((string) ($v['tax_id'] ?? '')) ?: null,
                    'phone'  => trim((string) ($v['phone'] ?? '')) ?: null,
                    'email'  => trim((string) ($v['email'] ?? '')) ?: null,
                    'type'   => 'goods',
                    'status' => 'active',
                ]);
                $supplier->deleted_at = null;
                $supplier->save();

                return true;
            }),
        ];
    }
}
