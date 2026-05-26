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
