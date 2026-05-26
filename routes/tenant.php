<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::get('/', function () {
        return redirect('/app');
    });
    Route::get('/login', function () {
        return redirect('/app/login');
    })->name('login');

    // ─── Excel Template Downloads ───────────────────────────────────
    Route::get('/app/download-template/{type}', function (string $type) {
        $map = [
            'uom'        => [\App\Exports\UomMasterTemplateExport::class,      'uom_master_template.xlsx'],
            'items'      => [\App\Exports\ItemMasterTemplateExport::class,     'item_master_template.xlsx'],
            'suppliers'  => [\App\Exports\SupplierTemplateExport::class,       'supplier_template.xlsx'],
            'warehouses' => [\App\Exports\WarehouseMasterTemplateExport::class,'warehouse_template.xlsx'],
            'open_pos'   => [\App\Exports\OpenPoTemplateExport::class,         'open_po_template.xlsx'],
        ];

        abort_unless(isset($map[$type]), 404);

        [$class, $filename] = $map[$type];
        return \Maatwebsite\Excel\Facades\Excel::download(new $class(), $filename);
    })->middleware(['auth'])->name('tenant.template.download');
});
