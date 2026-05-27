<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('uom_masters', function (Blueprint $table) {
            // หน่วยซื้อ: the (usually larger) unit used when purchasing in a PO.
            $table->string('purchase_unit')->nullable()->after('name');
            // ตัวคูณ: number of base/inventory units (this row's unit) per 1 purchase unit.
            // e.g. base = ชิ้น, purchase = กล่อง, factor = 12  (1 กล่อง = 12 ชิ้น)
            $table->decimal('conversion_factor', 15, 4)->default(1)->after('purchase_unit');
        });
    }

    public function down(): void
    {
        Schema::table('uom_masters', function (Blueprint $table) {
            $table->dropColumn(['purchase_unit', 'conversion_factor']);
        });
    }
};
