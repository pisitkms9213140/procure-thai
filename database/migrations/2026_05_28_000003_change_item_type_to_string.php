<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // item_type was enum(raw_material,packaging,mro,service) but the form
        // offers finished_goods / consumable too — widen to string so saving
        // any of those options doesn't truncate (1265) and 500.
        DB::statement("ALTER TABLE `item_masters` MODIFY `item_type` VARCHAR(32) NOT NULL DEFAULT 'raw_material'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `item_masters` MODIFY `item_type` ENUM('raw_material','packaging','mro','service') NOT NULL DEFAULT 'raw_material'");
    }
};
