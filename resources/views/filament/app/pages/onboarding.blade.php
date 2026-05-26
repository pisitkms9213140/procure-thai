<x-filament-panels::page>
    <div class="mb-6 rounded-2xl bg-gradient-to-r from-amber-500 to-orange-500 p-6 text-white shadow-lg">
        <h2 class="text-xl font-bold">👋 ยินดีต้อนรับสู่ ProcureThai!</h2>
        <p class="mt-1 text-sm opacity-90">
            กรุณาตั้งค่าเริ่มต้นก่อนใช้งาน — เลือกโหมดการเชื่อมต่อและนำเข้าข้อมูลหลัก (ใช้เวลาประมาณ 5-10 นาที)
        </p>
        <div class="mt-3 flex gap-6 text-xs opacity-75">
            <span>📋 Step 1: โหมดการเชื่อมต่อ</span>
            <span>📦 Step 2: สินค้า & หน่วยนับ</span>
            <span>🏢 Step 3: ผู้จัดจำหน่าย</span>
            <span>🏭 Step 4: คลัง & PO ค้างรับ</span>
        </div>
    </div>

    {{ $this->form }}
</x-filament-panels::page>
