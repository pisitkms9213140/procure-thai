<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_notes', function (Blueprint $table) {
            if (! Schema::hasColumn('delivery_notes', 'verified_at')) {
                // The supplier-side verification block — checked before the
                // system unlocks QR DN issuance. The vendor compares their own
                // invoice numbers against the PO and ticks the checkbox.
                $table->timestamp('verified_at')->nullable();
                $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('verified_invoice_no')->nullable();
                $table->decimal('verified_invoice_amount', 15, 2)->nullable();
                $table->string('verified_tax_id', 20)->nullable();
                // print = A4 / PDF (desktop vendors), digital = on-screen QR (mobile vendors)
                $table->string('qr_format', 16)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->dropForeign(['verified_by_user_id']);
            $table->dropColumn([
                'verified_at',
                'verified_by_user_id',
                'verified_invoice_no',
                'verified_invoice_amount',
                'verified_tax_id',
                'qr_format',
            ]);
        });
    }
};
