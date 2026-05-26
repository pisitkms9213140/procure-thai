<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Buffer ระหว่าง Portal และ SAP B1
        Schema::create('sync_queue', function (Blueprint $table) {
            $table->id();
            $table->enum('operation', [
                'create_po',        // VirtualPO → SAP PO
                'grpo',             // DeliveryPlan → SAP GRPO
                'create_invoice',   // SelfBillingInvoice → SAP AP Invoice
                'down_payment',     // Down Payment
                'payment_sync',     // ดึง Payment กลับจาก SAP
            ]);
            $table->string('reference_type');               // model class name
            $table->unsignedBigInteger('reference_id');
            $table->json('payload');                        // ข้อมูลที่จะส่ง SAP
            $table->enum('status', ['pending', 'processing', 'success', 'error'])
                ->default('pending');
            $table->integer('retry_count')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('next_retry_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'next_retry_at']);
            $table->index(['reference_type', 'reference_id']);
        });

        // Audit Log ทุก Event
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('vendor_code')->nullable();
            $table->string('action');                       // quotation.submitted, grpo.received
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['model_type', 'model_id']);
            $table->index(['vendor_code', 'created_at']);
        });

        // สถานะการโอนเงิน (Sync กลับจาก SAP)
        Schema::create('payment_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('vendor_code');
            $table->foreignId('self_billing_invoice_id')->nullable()
                ->constrained()->nullOnDelete();
            $table->string('sap_payment_doc_number')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('THB');
            $table->date('payment_date');
            $table->string('bank_reference')->nullable();   // เลขอ้างอิงธนาคาร
            $table->string('payment_method')->nullable();   // โอน, เช็ค, etc.
            $table->enum('status', ['scheduled', 'processed', 'failed'])->default('processed');
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->index(['vendor_code', 'payment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_statuses');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('sync_queue');
    }
};
