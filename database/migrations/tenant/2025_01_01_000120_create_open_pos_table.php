<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('open_pos', function (Blueprint $table) {
            $table->id();
            $table->string('po_number');                    // เลข PO จาก SAP
            $table->string('vendor_code');
            $table->string('item_code');
            $table->string('item_name');
            $table->string('uom_code')->nullable();
            $table->string('warehouse_code')->nullable();
            $table->decimal('ordered_qty', 15, 4);
            $table->decimal('received_qty', 15, 4)->default(0);
            $table->decimal('open_qty', 15, 4)
                ->storedAs('ordered_qty - received_qty');
            $table->decimal('unit_price', 15, 4);
            $table->date('po_date');
            $table->date('required_date')->nullable();
            $table->enum('status', ['open', 'partial', 'closed'])->default('open');
            $table->string('sap_doc_entry')->nullable();    // DocEntry ใน SAP
            $table->string('sap_doc_num')->nullable();      // DocNum ใน SAP
            $table->enum('source', ['sap_sync', 'excel_import'])->default('excel_import');
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();

            $table->index(['vendor_code', 'status']);
            $table->index('po_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('open_pos');
    }
};
