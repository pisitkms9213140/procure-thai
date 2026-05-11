<?php
namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    // เราสามารถเพิ่ม Field พิเศษ เช่น ชื่อบริษัท หรือ แพ็กเกจที่ใช้ ได้ที่นี่
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'company_name',
            'plan',
            'status',
            'data', // demo, pro, enterprise
        ];
    }
}