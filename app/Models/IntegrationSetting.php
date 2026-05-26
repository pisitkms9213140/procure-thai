<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class IntegrationSetting extends Model
{
    protected $fillable = [
        'integration_mode', 'sap_service_layer_url', 'sap_company_db',
        'sap_username', 'sap_password_encrypted',
        'sap_connection_verified', 'sap_last_synced_at', 'sync_config',
    ];

    protected $casts = [
        'sap_connection_verified' => 'boolean',
        'sap_last_synced_at'      => 'datetime',
        'sync_config'             => 'array',
    ];

    public function setSapPasswordAttribute(string $password): void
    {
        $this->attributes['sap_password_encrypted'] = Crypt::encryptString($password);
    }

    public function getSapPasswordDecrypted(): ?string
    {
        if (!$this->sap_password_encrypted) return null;
        return Crypt::decryptString($this->sap_password_encrypted);
    }

    public function isSapApi(): bool { return $this->integration_mode === 'sap_api'; }
    public function isExcel(): bool { return $this->integration_mode === 'excel'; }
}
