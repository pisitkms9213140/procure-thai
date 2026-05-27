<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('request_items', function (Blueprint $table) {
            $table->decimal('confirmed_unit_price', 15, 4)->nullable()->after('budget_price'); // ราคาที่ซัพพลายเออร์ยืนยัน
            $table->decimal('confirmed_qty', 15, 4)->nullable()->after('confirmed_unit_price'); // จำนวนที่ยืนยัน
            $table->date('confirmed_delivery_date')->nullable()->after('confirmed_qty');         // กำหนดส่ง
            $table->text('supplier_note')->nullable()->after('confirmed_delivery_date');
            $table->timestamp('confirmed_at')->nullable()->after('supplier_note');
        });
    }

    public function down(): void
    {
        Schema::table('request_items', function (Blueprint $table) {
            $table->dropColumn([
                'confirmed_unit_price', 'confirmed_qty', 'confirmed_delivery_date',
                'supplier_note', 'confirmed_at',
            ]);
        });
    }
};
