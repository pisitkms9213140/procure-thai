<?php

namespace App\Filament\Resources\Tenants\Pages;

use App\Filament\Resources\Tenants\TenantResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

    // ⚠️ ลอจิกสำคัญ: เมื่อสร้างบริษัทเสร็จ ให้สร้าง subdomain ให้เขาด้วย
    protected function afterCreate(): void
    {
        $tenant = $this->record;

        // ตัวอย่าง: ถ้า ID คือ 'test' จะได้ domain 'test.procurethai.uk'
        $tenant->domains()->create([
            'domain' => $tenant->id . '.procurethai.uk',
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}