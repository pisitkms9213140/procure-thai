<?php

namespace App\Filament\App\Pages;

use App\Imports\ItemMasterImport;
use App\Imports\OpenPoImport;
use App\Imports\SupplierImport;
use App\Imports\UomMasterImport;
use App\Imports\WarehouseMasterImport;
use App\Models\IntegrationSetting;
use App\Services\SapB1Service;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions as ActionsContainer;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Maatwebsite\Excel\Facades\Excel;

class Onboarding extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view                = 'filament.app.pages.onboarding';
    protected static \BackedEnum|string|null $navigationIcon  = 'heroicon-o-rocket-launch';
    protected static ?string $title      = 'ตั้งค่าเริ่มต้น';
    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    // Sync / import result counters (Livewire reactive)
    public int $uomCount  = 0;
    public int $itemCount = 0;
    public int $supCount  = 0;
    public int $whCount   = 0;
    public int $poCount   = 0;

    // ─── Lifecycle ────────────────────────────────────────────────

    public function mount(): void
    {
        if (tenant('onboarding_completed')) {
            $this->redirect(filament()->getHomeUrl());
            return;
        }

        $settings = IntegrationSetting::first();
        $this->form->fill([
            'integration_mode'      => $settings?->integration_mode ?? 'excel',
            'sap_service_layer_url' => $settings?->sap_service_layer_url,
            'sap_company_db'        => $settings?->sap_company_db,
            'sap_username'          => $settings?->sap_username,
        ]);
    }

    // ─── Form ─────────────────────────────────────────────────────

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Wizard::make([
                    $this->modeStep(),
                    $this->itemsUomStep(),
                    $this->vendorStep(),
                    $this->warehousePoStep(),
                ])
                ->submitAction(new HtmlString(
                    '<button type="button" wire:click="completeOnboarding"
                        class="fi-btn fi-color-success rounded-lg bg-green-600 px-5 py-2.5
                               text-sm font-semibold text-white shadow hover:bg-green-500
                               focus-visible:outline focus-visible:outline-2">
                        🎉 เริ่มใช้งานระบบ
                    </button>'
                )),
            ])
            ->statePath('data');
    }

    // ─── Wizard Steps ─────────────────────────────────────────────

    protected function modeStep(): Step
    {
        return Step::make('mode')
            ->label('โหมดการเชื่อมต่อ')
            ->icon('heroicon-o-cog-6-tooth')
            ->description('เลือกวิธีการนำเข้าข้อมูลหลัก')
            ->schema([
                Radio::make('integration_mode')
                    ->label('วิธีเชื่อมต่อกับ SAP B1')
                    ->options([
                        'sap_api' => '🔗 SAP B1 Service Layer API (เชื่อมต่อตรง)',
                        'excel'   => '📊 Excel Import / Export (แนะนำสำหรับเริ่มต้น)',
                    ])
                    ->descriptions([
                        'sap_api' => 'เหมาะสำหรับ SAP B1 ที่เปิด Service Layer ให้เข้าถึงจาก internet ได้',
                        'excel'   => 'ดาวน์โหลด template → กรอกข้อมูล → อัปโหลดเข้าระบบ',
                    ])
                    ->required()
                    ->default('excel')
                    ->live(),

                Section::make('SAP B1 Service Layer')
                    ->description('กรอกข้อมูลการเชื่อมต่อ SAP Business One Service Layer')
                    ->columns(2)
                    ->schema([
                        TextInput::make('sap_service_layer_url')
                            ->label('Service Layer URL')
                            ->placeholder('https://192.168.1.100:50000')
                            ->columnSpanFull()
                            ->required(),

                        TextInput::make('sap_company_db')
                            ->label('Company DB')
                            ->placeholder('SBODEMOUS')
                            ->required(),

                        TextInput::make('sap_username')
                            ->label('Username')
                            ->required(),

                        TextInput::make('sap_password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->required(),

                        ActionsContainer::make([
                            Action::make('testSapConnection')
                                ->label('🔌 ทดสอบการเชื่อมต่อ')
                                ->color('info')
                                ->action(function (Get $get) {
                                    $tmp                        = new IntegrationSetting();
                                    $tmp->sap_service_layer_url = $get('sap_service_layer_url');
                                    $tmp->sap_company_db        = $get('sap_company_db');
                                    $tmp->sap_username          = $get('sap_username');
                                    $tmp->sap_password          = $get('sap_password');

                                    $result = (new SapB1Service($tmp))->testConnection();

                                    $result['success']
                                        ? Notification::make()->title('เชื่อมต่อ SAP สำเร็จ ✅')->body($result['message'])->success()->send()
                                        : Notification::make()->title('เชื่อมต่อล้มเหลว ❌')->body($result['message'])->danger()->send();
                                }),
                        ])->columnSpanFull(),
                    ])
                    ->visible(fn (Get $get) => $get('integration_mode') === 'sap_api'),
            ])
            ->afterValidation(fn () => $this->saveSettings());
    }

    protected function itemsUomStep(): Step
    {
        return Step::make('items_uom')
            ->label('สินค้า & หน่วยนับ')
            ->icon('heroicon-o-cube')
            ->description('นำเข้า UoM Master และ Item Master')
            ->schema([
                // ── UoM ──────────────────────────────────────────
                Section::make('หน่วยนับ (Unit of Measure)')
                    ->schema([
                        Placeholder::make('uom_status')
                            ->label('')
                            ->content(fn () => $this->uomCount > 0
                                ? new HtmlString("<span class='text-green-600 font-semibold'>✅ นำเข้าแล้ว {$this->uomCount} รายการ</span>")
                                : new HtmlString("<span class='text-gray-400 text-sm'>⏳ ยังไม่ได้นำเข้า</span>")),

                        // SAP path
                        ActionsContainer::make([
                            Action::make('syncUomSap')
                                ->label('🔄 Sync UoM จาก SAP')
                                ->color('primary')
                                ->requiresConfirmation()
                                ->action(function () {
                                    try {
                                        $settings       = IntegrationSetting::firstOrFail();
                                        $this->uomCount = (new SapB1Service($settings))->syncUomMaster();
                                        Notification::make()->title("Sync UoM สำเร็จ {$this->uomCount} รายการ")->success()->send();
                                    } catch (\Throwable $e) {
                                        Notification::make()->title('เกิดข้อผิดพลาด')->body($e->getMessage())->danger()->send();
                                    }
                                }),
                        ])->visible(fn () => ($this->data['integration_mode'] ?? 'excel') === 'sap_api'),

                        // Excel path
                        ActionsContainer::make([
                            Action::make('downloadUomTpl')
                                ->label('📥 ดาวน์โหลด Template UoM')
                                ->color('gray')
                                ->url(route('tenant.template.download', 'uom'))
                                ->openUrlInNewTab(),
                        ])->visible(fn () => ($this->data['integration_mode'] ?? 'excel') === 'excel'),

                        FileUpload::make('uom_file')
                            ->label('อัปโหลดไฟล์ UoM (.xlsx)')
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                                'text/csv',
                            ])
                            ->directory('imports')
                            ->visible(fn () => ($this->data['integration_mode'] ?? 'excel') === 'excel'),

                        ActionsContainer::make([
                            Action::make('importUom')
                                ->label('📤 Import UoM')
                                ->color('success')
                                ->action(function (Get $get) {
                                    $file = $get('uom_file');
                                    if (!$file) {
                                        Notification::make()->title('กรุณาเลือกไฟล์ก่อน')->warning()->send();
                                        return;
                                    }
                                    $importer       = new UomMasterImport();
                                    Excel::import($importer, Storage::disk('public')->path($file));
                                    $this->uomCount = $importer->imported;
                                    Notification::make()->title("Import UoM สำเร็จ {$this->uomCount} รายการ")->success()->send();
                                }),
                        ])->visible(fn () => ($this->data['integration_mode'] ?? 'excel') === 'excel'),
                    ]),

                // ── Items ─────────────────────────────────────────
                Section::make('รายการสินค้า (Item Master)')
                    ->schema([
                        Placeholder::make('item_status')
                            ->label('')
                            ->content(fn () => $this->itemCount > 0
                                ? new HtmlString("<span class='text-green-600 font-semibold'>✅ นำเข้าแล้ว {$this->itemCount} รายการ</span>")
                                : new HtmlString("<span class='text-gray-400 text-sm'>⏳ ยังไม่ได้นำเข้า</span>")),

                        ActionsContainer::make([
                            Action::make('syncItemsSap')
                                ->label('🔄 Sync Items จาก SAP')
                                ->color('primary')
                                ->requiresConfirmation()
                                ->action(function () {
                                    try {
                                        $settings        = IntegrationSetting::firstOrFail();
                                        $this->itemCount = (new SapB1Service($settings))->syncItemMaster();
                                        Notification::make()->title("Sync Items สำเร็จ {$this->itemCount} รายการ")->success()->send();
                                    } catch (\Throwable $e) {
                                        Notification::make()->title('เกิดข้อผิดพลาด')->body($e->getMessage())->danger()->send();
                                    }
                                }),
                        ])->visible(fn () => ($this->data['integration_mode'] ?? 'excel') === 'sap_api'),

                        ActionsContainer::make([
                            Action::make('downloadItemTpl')
                                ->label('📥 ดาวน์โหลด Template Items')
                                ->color('gray')
                                ->url(route('tenant.template.download', 'items'))
                                ->openUrlInNewTab(),
                        ])->visible(fn () => ($this->data['integration_mode'] ?? 'excel') === 'excel'),

                        FileUpload::make('item_file')
                            ->label('อัปโหลดไฟล์ Items (.xlsx)')
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                                'text/csv',
                            ])
                            ->directory('imports')
                            ->visible(fn () => ($this->data['integration_mode'] ?? 'excel') === 'excel'),

                        ActionsContainer::make([
                            Action::make('importItems')
                                ->label('📤 Import Items')
                                ->color('success')
                                ->action(function (Get $get) {
                                    $file = $get('item_file');
                                    if (!$file) {
                                        Notification::make()->title('กรุณาเลือกไฟล์ก่อน')->warning()->send();
                                        return;
                                    }
                                    $importer        = new ItemMasterImport();
                                    Excel::import($importer, Storage::disk('public')->path($file));
                                    $this->itemCount = $importer->imported;
                                    Notification::make()->title("Import Items สำเร็จ {$this->itemCount} รายการ")->success()->send();
                                }),
                        ])->visible(fn () => ($this->data['integration_mode'] ?? 'excel') === 'excel'),
                    ]),
            ]);
    }

    protected function vendorStep(): Step
    {
        return Step::make('vendors')
            ->label('ผู้จัดจำหน่าย')
            ->icon('heroicon-o-building-office')
            ->description('นำเข้าข้อมูล Vendor / Supplier')
            ->schema([
                Section::make('ผู้จัดจำหน่าย (Vendor / Supplier Master)')
                    ->schema([
                        Placeholder::make('sup_status')
                            ->label('')
                            ->content(fn () => $this->supCount > 0
                                ? new HtmlString("<span class='text-green-600 font-semibold'>✅ นำเข้าแล้ว {$this->supCount} ราย</span>")
                                : new HtmlString("<span class='text-gray-400 text-sm'>⏳ ยังไม่ได้นำเข้า</span>")),

                        ActionsContainer::make([
                            Action::make('syncVendorsSap')
                                ->label('🔄 Sync Vendors จาก SAP')
                                ->color('primary')
                                ->requiresConfirmation()
                                ->action(function () {
                                    try {
                                        $settings       = IntegrationSetting::firstOrFail();
                                        $this->supCount = (new SapB1Service($settings))->syncVendorMaster();
                                        Notification::make()->title("Sync Vendors สำเร็จ {$this->supCount} ราย")->success()->send();
                                    } catch (\Throwable $e) {
                                        Notification::make()->title('เกิดข้อผิดพลาด')->body($e->getMessage())->danger()->send();
                                    }
                                }),
                        ])->visible(fn () => ($this->data['integration_mode'] ?? 'excel') === 'sap_api'),

                        ActionsContainer::make([
                            Action::make('downloadSupTpl')
                                ->label('📥 ดาวน์โหลด Template Vendors')
                                ->color('gray')
                                ->url(route('tenant.template.download', 'suppliers'))
                                ->openUrlInNewTab(),
                        ])->visible(fn () => ($this->data['integration_mode'] ?? 'excel') === 'excel'),

                        FileUpload::make('supplier_file')
                            ->label('อัปโหลดไฟล์ Vendors (.xlsx)')
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                                'text/csv',
                            ])
                            ->directory('imports')
                            ->visible(fn () => ($this->data['integration_mode'] ?? 'excel') === 'excel'),

                        ActionsContainer::make([
                            Action::make('importSuppliers')
                                ->label('📤 Import Vendors')
                                ->color('success')
                                ->action(function (Get $get) {
                                    $file = $get('supplier_file');
                                    if (!$file) {
                                        Notification::make()->title('กรุณาเลือกไฟล์ก่อน')->warning()->send();
                                        return;
                                    }
                                    $importer       = new SupplierImport();
                                    Excel::import($importer, Storage::disk('public')->path($file));
                                    $this->supCount = $importer->imported;
                                    Notification::make()->title("Import Vendors สำเร็จ {$this->supCount} ราย")->success()->send();
                                }),
                        ])->visible(fn () => ($this->data['integration_mode'] ?? 'excel') === 'excel'),
                    ]),

                Section::make('การเชิญ Vendor เข้าใช้ Portal')
                    ->schema([
                        Placeholder::make('vendor_portal_info')
                            ->label('')
                            ->content(new HtmlString(
                                '<div class="text-sm text-gray-500 space-y-1">
                                    <p>📧 <strong>หลังจากตั้งค่าเสร็จ</strong> ไปที่เมนู <strong>จัดซื้อ → Vendors</strong> แล้วกด <strong>ส่งคำเชิญ</strong> เพื่อให้ Vendor เข้ามาจัดการข้อมูลของตัวเองได้</p>
                                    <p>🔐 Vendor จะได้รับ link สำหรับสร้าง account บน subdomain ของคุณ และเข้าถึงได้เฉพาะข้อมูลของตัวเองเท่านั้น</p>
                                </div>'
                            )),
                    ]),
            ]);
    }

    protected function warehousePoStep(): Step
    {
        return Step::make('warehouses_pos')
            ->label('คลังสินค้า & PO ค้างรับ')
            ->icon('heroicon-o-building-storefront')
            ->description('นำเข้า Warehouse Master และ Open POs')
            ->schema([
                // ── Warehouses ───────────────────────────────────
                Section::make('คลังสินค้า (Warehouse Master)')
                    ->schema([
                        Placeholder::make('wh_status')
                            ->label('')
                            ->content(fn () => $this->whCount > 0
                                ? new HtmlString("<span class='text-green-600 font-semibold'>✅ นำเข้าแล้ว {$this->whCount} คลัง</span>")
                                : new HtmlString("<span class='text-gray-400 text-sm'>⏳ ยังไม่ได้นำเข้า</span>")),

                        ActionsContainer::make([
                            Action::make('syncWhSap')
                                ->label('🔄 Sync Warehouses จาก SAP')
                                ->color('primary')
                                ->requiresConfirmation()
                                ->action(function () {
                                    try {
                                        $settings      = IntegrationSetting::firstOrFail();
                                        $this->whCount = (new SapB1Service($settings))->syncWarehouseMaster();
                                        Notification::make()->title("Sync Warehouses สำเร็จ {$this->whCount} คลัง")->success()->send();
                                    } catch (\Throwable $e) {
                                        Notification::make()->title('เกิดข้อผิดพลาด')->body($e->getMessage())->danger()->send();
                                    }
                                }),
                        ])->visible(fn () => ($this->data['integration_mode'] ?? 'excel') === 'sap_api'),

                        ActionsContainer::make([
                            Action::make('downloadWhTpl')
                                ->label('📥 ดาวน์โหลด Template Warehouses')
                                ->color('gray')
                                ->url(route('tenant.template.download', 'warehouses'))
                                ->openUrlInNewTab(),
                        ])->visible(fn () => ($this->data['integration_mode'] ?? 'excel') === 'excel'),

                        FileUpload::make('warehouse_file')
                            ->label('อัปโหลดไฟล์ Warehouses (.xlsx)')
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                                'text/csv',
                            ])
                            ->directory('imports')
                            ->visible(fn () => ($this->data['integration_mode'] ?? 'excel') === 'excel'),

                        ActionsContainer::make([
                            Action::make('importWarehouses')
                                ->label('📤 Import Warehouses')
                                ->color('success')
                                ->action(function (Get $get) {
                                    $file = $get('warehouse_file');
                                    if (!$file) {
                                        Notification::make()->title('กรุณาเลือกไฟล์ก่อน')->warning()->send();
                                        return;
                                    }
                                    $importer      = new WarehouseMasterImport();
                                    Excel::import($importer, Storage::disk('public')->path($file));
                                    $this->whCount = $importer->imported;
                                    Notification::make()->title("Import Warehouses สำเร็จ {$this->whCount} คลัง")->success()->send();
                                }),
                        ])->visible(fn () => ($this->data['integration_mode'] ?? 'excel') === 'excel'),
                    ]),

                // ── Open POs ─────────────────────────────────────
                Section::make('PO ค้างรับ (Open Purchase Orders)')
                    ->schema([
                        Placeholder::make('po_status')
                            ->label('')
                            ->content(fn () => $this->poCount > 0
                                ? new HtmlString("<span class='text-green-600 font-semibold'>✅ นำเข้าแล้ว {$this->poCount} รายการ</span>")
                                : new HtmlString("<span class='text-gray-400 text-sm'>⏳ ยังไม่ได้นำเข้า</span>")),

                        ActionsContainer::make([
                            Action::make('syncPoSap')
                                ->label('🔄 Sync Open POs จาก SAP')
                                ->color('primary')
                                ->requiresConfirmation()
                                ->action(function () {
                                    try {
                                        $settings      = IntegrationSetting::firstOrFail();
                                        $this->poCount = (new SapB1Service($settings))->syncOpenPos();
                                        Notification::make()->title("Sync Open POs สำเร็จ {$this->poCount} รายการ")->success()->send();
                                    } catch (\Throwable $e) {
                                        Notification::make()->title('เกิดข้อผิดพลาด')->body($e->getMessage())->danger()->send();
                                    }
                                }),
                        ])->visible(fn () => ($this->data['integration_mode'] ?? 'excel') === 'sap_api'),

                        ActionsContainer::make([
                            Action::make('downloadPoTpl')
                                ->label('📥 ดาวน์โหลด Template Open POs')
                                ->color('gray')
                                ->url(route('tenant.template.download', 'open_pos'))
                                ->openUrlInNewTab(),
                        ])->visible(fn () => ($this->data['integration_mode'] ?? 'excel') === 'excel'),

                        FileUpload::make('po_file')
                            ->label('อัปโหลดไฟล์ Open POs (.xlsx)')
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                                'text/csv',
                            ])
                            ->directory('imports')
                            ->visible(fn () => ($this->data['integration_mode'] ?? 'excel') === 'excel'),

                        ActionsContainer::make([
                            Action::make('importOpenPos')
                                ->label('📤 Import Open POs')
                                ->color('success')
                                ->action(function (Get $get) {
                                    $file = $get('po_file');
                                    if (!$file) {
                                        Notification::make()->title('กรุณาเลือกไฟล์ก่อน')->warning()->send();
                                        return;
                                    }
                                    $importer      = new OpenPoImport();
                                    Excel::import($importer, Storage::disk('public')->path($file));
                                    $this->poCount = $importer->imported;
                                    Notification::make()->title("Import Open POs สำเร็จ {$this->poCount} รายการ")->success()->send();
                                }),
                        ])->visible(fn () => ($this->data['integration_mode'] ?? 'excel') === 'excel'),
                    ]),
            ]);
    }

    // ─── Public Actions ───────────────────────────────────────────

    public function completeOnboarding(): void
    {
        tenant()->update(['onboarding_completed' => true]);

        Notification::make()
            ->title('ยินดีต้อนรับสู่ ProcureThai! 🎉')
            ->body('ระบบพร้อมใช้งานแล้ว คุณสามารถเริ่มสร้าง PR/PO ได้เลย')
            ->success()
            ->duration(5000)
            ->send();

        $this->redirect(filament()->getHomeUrl());
    }

    // ─── Helpers ──────────────────────────────────────────────────

    protected function saveSettings(): void
    {
        $data     = $this->data;
        $settings = IntegrationSetting::firstOrNew([]);

        $settings->integration_mode = $data['integration_mode'];

        if ($data['integration_mode'] === 'sap_api') {
            $settings->sap_service_layer_url = $data['sap_service_layer_url'] ?? null;
            $settings->sap_company_db        = $data['sap_company_db'] ?? null;
            $settings->sap_username          = $data['sap_username'] ?? null;

            if (!empty($data['sap_password'])) {
                $settings->sap_password = $data['sap_password'];
            }
        }

        $settings->save();

        // Mirror on tenant row for quick lookups
        tenant()->update(['integration_mode' => $data['integration_mode']]);
    }
}
