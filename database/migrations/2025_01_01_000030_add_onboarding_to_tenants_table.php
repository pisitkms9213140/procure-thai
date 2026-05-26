<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->enum('integration_mode', ['sap_api', 'excel'])->default('excel')->after('status');
            $table->boolean('onboarding_completed')->default(false)->after('integration_mode');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['integration_mode', 'onboarding_completed']);
        });
    }
};
