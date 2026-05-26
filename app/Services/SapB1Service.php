<?php

namespace App\Services;

use App\Models\IntegrationSetting;
use App\Models\ItemMaster;
use App\Models\OpenPo;
use App\Models\Supplier;
use App\Models\UomMaster;
use App\Models\WarehouseMaster;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SapB1Service
{
    private string $baseUrl;
    private string $companyDb;
    private string $username;
    private string $password;
    private ?string $sessionId = null;

    public function __construct(IntegrationSetting $settings)
    {
        $this->baseUrl   = rtrim($settings->sap_service_layer_url, '/') . '/b1s/v1';
        $this->companyDb = $settings->sap_company_db;
        $this->username  = $settings->sap_username;
        $this->password  = $settings->getSapPasswordDecrypted();
    }

    // ─── Connection ───────────────────────────────────────────────
    public function testConnection(): array
    {
        try {
            $response = Http::withoutVerifying()
                ->timeout(10)
                ->post("{$this->baseUrl}/Login", [
                    'CompanyDB' => $this->companyDb,
                    'UserName'  => $this->username,
                    'Password'  => $this->password,
                ]);

            if ($response->successful()) {
                $this->sessionId = $response->json('SessionId');
                $this->logout();
                return ['success' => true, 'message' => 'เชื่อมต่อ SAP B1 สำเร็จ'];
            }

            return ['success' => false, 'message' => $response->json('error.message.value') ?? 'Connection failed'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // ─── Sync Master Data ─────────────────────────────────────────
    public function syncUomMaster(): int
    {
        $this->login();
        $count = 0;
        $data = $this->get('/UnitOfMeasurements?$select=Code,Name');

        foreach ($data['value'] ?? [] as $row) {
            UomMaster::updateOrCreate(
                ['sap_code' => $row['Code']],
                ['code' => $row['Code'], 'name' => $row['Name'] ?? $row['Code'], 'is_active' => true]
            );
            $count++;
        }
        $this->logout();
        return $count;
    }

    public function syncItemMaster(): int
    {
        $this->login();
        $count = 0;
        $data = $this->get('/Items?$select=ItemCode,ItemName,ForeignName,ItemsGroupCode,PurchaseUnit,DefaultSupplier,LastPurchasePrice,MinOrderQuantity&$filter=PurchaseItem eq \'tYES\'');

        foreach ($data['value'] ?? [] as $row) {
            ItemMaster::updateOrCreate(
                ['sap_item_code' => $row['ItemCode']],
                [
                    'item_code'   => $row['ItemCode'],
                    'item_name'   => $row['ItemName'],
                    'item_name_en'=> $row['ForeignName'] ?? null,
                    'uom_code'    => $row['PurchaseUnit'] ?? null,
                    'default_vendor_code' => $row['DefaultSupplier'] ?? null,
                    'last_purchase_price' => $row['LastPurchasePrice'] ?? null,
                    'min_order_qty' => $row['MinOrderQuantity'] ?? 1,
                    'is_active'   => true,
                ]
            );
            $count++;
        }
        $this->logout();
        return $count;
    }

    public function syncVendorMaster(): int
    {
        $this->login();
        $count = 0;
        $data = $this->get("/BusinessPartners?$select=CardCode,CardName,EmailAddress,Phone1,ContactPerson,Address,City,ZipCode&$filter=CardType eq 'cSupplier'");

        foreach ($data['value'] ?? [] as $row) {
            Supplier::updateOrCreate(
                ['code' => $row['CardCode']],
                [
                    'name'           => $row['CardName'],
                    'email'          => $row['EmailAddress'] ?? null,
                    'phone'          => $row['Phone1'] ?? null,
                    'contact_person' => $row['ContactPerson'] ?? null,
                    'address'        => $row['Address'] ?? null,
                    'province'       => $row['City'] ?? null,
                    'postcode'       => $row['ZipCode'] ?? null,
                    'status'         => 'active',
                ]
            );
            $count++;
        }
        $this->logout();
        return $count;
    }

    public function syncWarehouseMaster(): int
    {
        $this->login();
        $count = 0;
        $data = $this->get('/Warehouses?$select=WarehouseCode,WarehouseName,Street');

        foreach ($data['value'] ?? [] as $row) {
            WarehouseMaster::updateOrCreate(
                ['sap_warehouse_code' => $row['WarehouseCode']],
                [
                    'code'     => $row['WarehouseCode'],
                    'name'     => $row['WarehouseName'],
                    'location' => $row['Street'] ?? null,
                    'is_active'=> true,
                ]
            );
            $count++;
        }
        $this->logout();
        return $count;
    }

    public function syncOpenPos(): int
    {
        $this->login();
        $count = 0;
        $data = $this->get("/PurchaseOrders?$select=DocNum,DocEntry,CardCode,DocumentLines&$filter=DocumentStatus eq 'bost_Open'");

        foreach ($data['value'] ?? [] as $po) {
            foreach ($po['DocumentLines'] ?? [] as $line) {
                $openQty = ($line['Quantity'] ?? 0) - ($line['ReceivedQuantity'] ?? 0);
                if ($openQty <= 0) continue;

                OpenPo::updateOrCreate(
                    ['sap_doc_entry' => $po['DocEntry'], 'item_code' => $line['ItemCode']],
                    [
                        'po_number'     => 'SAP-' . $po['DocNum'],
                        'vendor_code'   => $po['CardCode'],
                        'item_code'     => $line['ItemCode'],
                        'item_name'     => $line['ItemDescription'] ?? '',
                        'uom_code'      => $line['UnitOfMeasure'] ?? null,
                        'warehouse_code'=> $line['WarehouseCode'] ?? null,
                        'ordered_qty'   => $line['Quantity'] ?? 0,
                        'received_qty'  => $line['ReceivedQuantity'] ?? 0,
                        'unit_price'    => $line['UnitPrice'] ?? 0,
                        'po_date'       => substr($po['DocDate'] ?? '', 0, 10) ?: null,
                        'required_date' => substr($line['RequiredDate'] ?? '', 0, 10) ?: null,
                        'sap_doc_num'   => (string) $po['DocNum'],
                        'status'        => ($line['ReceivedQuantity'] ?? 0) > 0 ? 'partial' : 'open',
                        'source'        => 'sap_sync',
                        'imported_at'   => now(),
                    ]
                );
                $count++;
            }
        }
        $this->logout();
        return $count;
    }

    // ─── Private Helpers ──────────────────────────────────────────
    private function login(): void
    {
        $response = Http::withoutVerifying()->timeout(15)
            ->post("{$this->baseUrl}/Login", [
                'CompanyDB' => $this->companyDb,
                'UserName'  => $this->username,
                'Password'  => $this->password,
            ]);
        $this->sessionId = $response->json('SessionId');
    }

    private function logout(): void
    {
        if ($this->sessionId) {
            Http::withoutVerifying()
                ->withHeaders(['Cookie' => "B1SESSION={$this->sessionId}"])
                ->post("{$this->baseUrl}/Logout");
            $this->sessionId = null;
        }
    }

    private function get(string $endpoint): array
    {
        $response = Http::withoutVerifying()
            ->withHeaders(['Cookie' => "B1SESSION={$this->sessionId}", 'Prefer' => 'odata.maxpagesize=500'])
            ->get($this->baseUrl . $endpoint);

        if (!$response->successful()) {
            Log::error('SAP B1 API error', ['endpoint' => $endpoint, 'status' => $response->status()]);
            return ['value' => []];
        }
        return $response->json();
    }
}
