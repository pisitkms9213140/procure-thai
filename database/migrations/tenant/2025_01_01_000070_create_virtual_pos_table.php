<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('virtual_pos', function (Blueprint $table) {
            $table->id();
            $table->string('vpo_number')->unique();          // VPO-2026-0001
            $table->foreignId('quotation_id')->constrained()->cascadeOnDelete();
            $table->string('vendor_code');
            $table->date('po_date');
            $table->date('expected_delivery_date');
            $table->decimal('unit_price', 15, 4);
            $table->decimal('ordered_qty', 15, 4);
            $table->decimal('received_qty', 15, 4)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->string('currency', 3)->default('THB');
            $table->enum('status', ['pending', 'partial', 'completed', 'cancelled'])
                ->default('pending');
            $table->string('sap_po_number')->nullable();     // เลข PO ใน SAP หลัง sync
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('virtual_pos');
    }
};
