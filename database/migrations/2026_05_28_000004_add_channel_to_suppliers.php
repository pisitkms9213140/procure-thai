<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            if (! Schema::hasColumn('suppliers', 'channel')) {
                // desktop | mobile | both — routes the supplier between the
                // desktop portal and the mobile PWA at PR/CF/verification time.
                $table->string('channel', 16)->default('both')->after('type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('channel');
        });
    }
};
