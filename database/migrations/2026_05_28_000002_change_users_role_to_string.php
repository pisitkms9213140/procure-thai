<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // role was enum('manager','supervisor','staff') — widen to a string so
        // 'vendor' (and any future role) is accepted instead of being truncated.
        DB::statement("ALTER TABLE `users` MODIFY `role` VARCHAR(32) NOT NULL DEFAULT 'staff'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `users` MODIFY `role` ENUM('manager','supervisor','staff') NOT NULL DEFAULT 'staff'");
    }
};
