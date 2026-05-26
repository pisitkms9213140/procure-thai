<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Self-billing: โรงงานออก Invoice ให้คู่ค้าจากยอดรับจริง
        Schema::create('self_billing_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('sbi_number')->unique();          // SBI-2026-0001
            $table->foreignId('delivery_plan_id')->constrained()->cascadeOnDelete();
            $table->string('vendor_code');
            $table->decimal('qty_billed', 15, 4);
            $table->decimal('unit_price', 15, 4);
            $table->decimal('subtotal', 15, 2);
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->decimal('wht_amount', 15, 2)->default(0);  // หัก ณ ที่จ่าย
            $table->decimal('net_amount', 15, 2);
            $table->string('currency', 3)->default('THB');
            $table->date('invoice_date');
            $table->date('due_date');
            $table->enum('invoice_type', ['standard', 'down_payment'])->default('standard');
            $table->enum('status', ['draft', 'pending', 'verified', 'approved', 'paid', 'voided'])
                ->default('draft');
            $table->enum('verification_status', ['unverified', 'verified', 'disputed'])
                ->default('unverified');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->string('sap_invoice_number')->nullable(); // เลขที่ใน SAP หลัง sync
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // ใบวางบิล — รวม SBI หลายใบ
        Schema::create('billing_statements', function (Blueprint $table) {
            $table->id();
            $table->string('bs_number')->unique();           // BS-2026-0001
            $table->string('vendor_code');
            $table->date('statement_date');
            $table->date('payment_due_date');
            $table->decimal('total_amount', 15, 2);
            $table->enum('status', ['draft', 'sent', 'acknowledged', 'paid'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('billing_statement_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('billing_statement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('self_billing_invoice_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_statement_items');
        Schema::dropIfExists('billing_statements');
        Schema::dropIfExists('self_billing_invoices');
    }
};
