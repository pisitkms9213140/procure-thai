<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // UoM Master
        Schema::create('uom_masters', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();               // KG, PCS, BOX, etc.
            $table->string('name');                         // กิโลกรัม, ชิ้น, กล่อง
            $table->string('purchase_unit')->nullable();    // หน่วยซื้อ (หน่วยใหญ่ตอนสั่ง PO)
            $table->decimal('conversion_factor', 15, 4)->default(1); // ตัวคูณ: หน่วยเล็กต่อ 1 หน่วยซื้อ
            $table->string('sap_code')->nullable();         // UoM code ใน SAP
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Item Master (วัตถุดิบ / บรรจุภัณฑ์)
        Schema::create('item_masters', function (Blueprint $table) {
            $table->id();
            $table->string('item_code')->unique();          // รหัสสินค้า SAP
            $table->string('item_name');
            $table->string('item_name_en')->nullable();
            $table->enum('item_type', ['raw_material', 'packaging', 'mro', 'service'])
                ->default('raw_material');
            $table->string('item_group')->nullable();
            $table->foreignId('uom_id')->nullable()->constrained('uom_masters')->nullOnDelete();
            $table->string('uom_code')->nullable();         // เก็บ text ด้วยเผื่อ UoM ยังไม่ sync
            $table->string('default_vendor_code')->nullable();
            $table->decimal('last_purchase_price', 15, 4)->nullable();
            $table->decimal('min_order_qty', 15, 4)->default(1);
            $table->integer('lead_time_days')->default(0);
            $table->boolean('requires_lot_tracking')->default(false); // สำคัญสำหรับวัตถุดิบอาหาร
            $table->boolean('requires_expiry_date')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('sap_item_code')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Warehouse Master
        Schema::create('warehouse_masters', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();               // WH01, WH-COLD, etc.
            $table->string('name');
            $table->enum('type', ['raw_material', 'finished_goods', 'wip', 'general'])
                ->default('raw_material');
            $table->string('location')->nullable();
            $table->boolean('is_cold_storage')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('sap_warehouse_code')->nullable();
            $table->timestamps();
        });

        // Integration Settings (SAP B1 API config ต่อ tenant)
        Schema::create('integration_settings', function (Blueprint $table) {
            $table->id();
            $table->string('integration_mode')->default('excel'); // sap_api | excel
            $table->string('sap_service_layer_url')->nullable();  // https://sap-server:50000
            $table->string('sap_company_db')->nullable();
            $table->string('sap_username')->nullable();
            $table->text('sap_password_encrypted')->nullable();   // encrypted
            $table->boolean('sap_connection_verified')->default(false);
            $table->timestamp('sap_last_synced_at')->nullable();
            $table->json('sync_config')->nullable();              // sync intervals, etc.
            $table->timestamps();
        });

        // Vendor Invitations (invite vendor สร้าง login)
        Schema::create('vendor_invitations', function (Blueprint $table) {
            $table->id();
            $table->string('vendor_code');
            $table->string('email');
            $table->string('token')->unique();
            $table->enum('status', ['pending', 'accepted', 'expired'])->default('pending');
            $table->foreignId('invited_by')->constrained('users');
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_invitations');
        Schema::dropIfExists('integration_settings');
        Schema::dropIfExists('warehouse_masters');
        Schema::dropIfExists('item_masters');
        Schema::dropIfExists('uom_masters');
    }
};
