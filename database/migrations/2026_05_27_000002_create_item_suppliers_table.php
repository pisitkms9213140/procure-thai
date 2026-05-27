<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('item_masters')->cascadeOnDelete();
            $table->string('vendor_code');                 // references suppliers.code
            $table->string('vendor_item_code')->nullable(); // vendor's own code for this item
            $table->decimal('price', 15, 4)->nullable();
            $table->integer('lead_time_days')->default(0);
            $table->decimal('min_order_qty', 15, 4)->default(1);
            $table->boolean('is_preferred')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['item_id', 'vendor_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_suppliers');
    }
};
