<?php

use App\Http\Controllers\RegisterController;
use Illuminate\Support\Facades\Route;

// Self-service trial registration
Route::get('/register', [RegisterController::class, 'show'])->name('register');
Route::post('/register', [RegisterController::class, 'store']);
Route::get('/register/success/{subdomain}', function ($subdomain) {
    return view('register-success', compact('subdomain'));
})->name('register.success');

Route::get('/', function () {
    $host = request()->getHost();
    
    // ถ้าเป็นโดเมนหลัก หรือ localhost ให้แสดงหน้า Landing ปกติ
    if ($host === 'procurethai.uk' || $host === '127.0.0.1' || $host === 'localhost') {
        return view('welcome'); // (เปลี่ยน 'welcome' เป็นชื่อไฟล์หน้า Landing ของคุณถ้าคุณตั้งชื่ออื่น)
    }
    
    // แต่ถ้าเป็น Subdomain ของลูกค้า ให้เด้งไปที่หน้า Dashboard ทันที
    return redirect('/app');
});
Route::get('/login', function () {
    // Central domain → แสดงหน้า login ให้กรอก subdomain
    return view('central-login');
})->name('central.login');

// Redirect /login → /masuk เพื่อหนี Filament route override
Route::get('/masuk', function () {
    return view('central-login');
})->name('masuk');

// Locale switch (TH ↔ EN). Defined here in web.php because routes/tenant.php
// is not loaded (TenancyServiceProvider is intentionally not registered).
// Only sets a session value + redirects, so it needs no tenant DB context.
Route::get('/locale/{lang}', function (string $lang) {
    abort_unless(in_array($lang, ['th', 'en']), 404);
    session(['locale' => $lang]);
    app()->setLocale($lang);
    return back(fallback: '/app');
})->name('locale.switch');

// Excel template downloads. Defined here (not in routes/tenant.php, which is
// not loaded) so the ExcelTemplatesPage download links resolve.
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
})->middleware('auth')->name('tenant.template.download');
