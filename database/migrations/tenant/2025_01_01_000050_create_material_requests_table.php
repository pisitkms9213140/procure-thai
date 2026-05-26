<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_requests', function (Blueprint $table) {
            $table->id();
            $table->string('mr_number')->unique();           // MR-2026-0001
            $table->foreignId('created_by')->constrained('users');
            $table->date('request_date');
            $table->date('required_date');
            $table->enum('status', ['draft', 'open', 'partial', 'completed', 'cancelled'])
                ->default('draft');
            $table->enum('priority', ['normal', 'urgent', 'critical'])->default('normal');
            $table->string('department')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_request_id')->constrained()->cascadeOnDelete();
            $table->string('vendor_code');                  // ผูกกับ Supplier.code
            $table->string('item_code')->nullable();        // รหัสสินค้า SAP
            $table->string('description');
            $table->string('unit')->default('กก.');
            $table->decimal('quantity', 15, 4);
            $table->decimal('budget_price', 15, 4)->nullable(); // ราคาประมาณ
            $table->enum('status', ['pending', 'quoted', 'approved', 'cancelled'])
                ->default('pending');
            $table->text('notes')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_items');
        Schema::dropIfExists('material_requests');
    }
};
