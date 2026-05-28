<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // คู่ค้าแจ้งส่งมอบ + สร้าง QR Token
        Schema::create('delivery_notes', function (Blueprint $table) {
            $table->id();
            $table->string('dn_number')->unique();           // DN-2026-0001
            $table->foreignId('virtual_po_id')->constrained()->cascadeOnDelete();
            $table->string('vendor_code');
            $table->decimal('planned_qty', 15, 4);
            $table->date('planned_delivery_date');
            $table->string('vehicle_plate')->nullable();
            $table->string('driver_name')->nullable();
            $table->enum('status', ['pending', 'in_transit', 'arrived', 'received', 'rejected'])
                ->default('pending');
            $table->string('qr_token')->unique()->nullable(); // UUID สำหรับ QR Code
            $table->timestamp('qr_expires_at')->nullable();
            $table->string('qr_format', 16)->nullable();      // print | digital

            // Vendor-side verification block — gates QR DN issuance
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('verified_invoice_no')->nullable();
            $table->decimal('verified_invoice_amount', 15, 2)->nullable();
            $table->string('verified_tax_id', 20)->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // เจ้าหน้าที่คลังสแกนรับของจริง
        Schema::create('delivery_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_note_id')->constrained()->cascadeOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('actual_delivered_qty', 15, 4);
            $table->decimal('rejected_qty', 15, 4)->default(0);
            $table->enum('condition', ['good', 'damaged', 'partial'])->default('good');
            $table->string('damage_photo_path')->nullable(); // รูปสินค้าชำรุด
            $table->text('rejection_reason')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->string('lot_number')->nullable();
            $table->date('expiry_date')->nullable();         // สำคัญสำหรับโรงงานอาหาร
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_plans');
        Schema::dropIfExists('delivery_notes');
    }
};
