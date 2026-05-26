<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('qt_number')->unique();           // QT-2026-0001
            $table->foreignId('request_item_id')->constrained()->cascadeOnDelete();
            $table->string('vendor_code');
            $table->decimal('unit_price', 15, 4);
            $table->decimal('quantity', 15, 4);
            $table->decimal('total_amount', 15, 2)->storedAs('unit_price * quantity');
            $table->date('delivery_date');
            $table->integer('lead_time_days')->default(0);
            $table->string('payment_terms')->nullable();
            $table->enum('status', ['submitted', 'under_review', 'approved', 'rejected'])
                ->default('submitted');
            $table->text('vendor_notes')->nullable();
            $table->text('reviewer_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('document_path')->nullable();     // ไฟล์ใบเสนอราคา
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
