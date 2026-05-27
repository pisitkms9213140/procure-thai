<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_masters', function (Blueprint $table) {
            $table->string('item_group_name')->nullable()->after('item_group');
            $table->string('purchase_unit')->nullable()->after('uom_code');         // หน่วยซื้อ
            $table->decimal('conversion_factor', 15, 4)->default(1)->after('purchase_unit'); // ตัวคูณ: หน่วยคงคลังต่อ 1 หน่วยซื้อ
            $table->string('default_warehouse_code')->nullable()->after('default_vendor_code');
            $table->string('old_item_code')->nullable()->after('sap_item_code');     // รหัสสินค้าเก่า
            $table->json('sap_raw')->nullable();                                     // full SAP export row
        });
    }

    public function down(): void
    {
        Schema::table('item_masters', function (Blueprint $table) {
            $table->dropColumn([
                'item_group_name', 'purchase_unit', 'conversion_factor',
                'default_warehouse_code', 'old_item_code', 'sap_raw',
            ]);
        });
    }
};
