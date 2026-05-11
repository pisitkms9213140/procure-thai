<?php

use Illuminate\Support\Facades\Route;

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
    $host = request()->getHost();
    
    // ถ้าเข้าจากโดเมนหลัก (หรือ localhost) ให้พาไปหน้า Superadmin
    if ($host === 'procurethai.uk' || $host === '127.0.0.1' || $host === 'localhost') {
        return redirect('/superadmin/login');
    }
    
    // ถ้าเข้าจาก Subdomain (เช่น abc.procurethai.uk) ให้พาไปหน้าของลูกค้า
    return redirect('/app/login');
    
})->name('login'); // 👈 หัวใจสำคัญคือตรงนี้ครับ บังคับตั้งชื่อให้ตรงกับที่ Laravel ตามหา
