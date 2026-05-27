<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_masters', function (Blueprint $table) {
            if (! Schema::hasColumn('item_masters', 'barcode')) {
                $table->string('barcode')->nullable()->after('item_name_en');
            }
        });
    }

    public function down(): void
    {
        Schema::table('item_masters', function (Blueprint $table) {
            $table->dropColumn('barcode');
        });
    }
};
