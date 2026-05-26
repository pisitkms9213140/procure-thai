<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function show()
    {
        return view('register');
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'subdomain'    => 'required|string|max:50|alpha_dash|unique:tenants,id',
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|max:255',
            'password'     => 'required|string|min:8|confirmed',
        ], [
            'company_name.required' => 'กรุณากรอกชื่อบริษัท',
            'subdomain.required'    => 'กรุณากรอก Subdomain',
            'subdomain.unique'      => 'Subdomain นี้ถูกใช้งานแล้ว',
            'subdomain.alpha_dash'  => 'Subdomain ใช้ได้เฉพาะ a-z, 0-9 และ -',
            'name.required'         => 'กรุณากรอกชื่อผู้ใช้',
            'email.required'        => 'กรุณากรอกอีเมล',
            'email.email'           => 'รูปแบบอีเมลไม่ถูกต้อง',
            'password.required'     => 'กรุณากรอกรหัสผ่าน',
            'password.min'          => 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร',
            'password.confirmed'    => 'รหัสผ่านไม่ตรงกัน',
        ]);

        // สร้าง Tenant (triggers CreateDatabase + MigrateDatabase + SeedDatabase jobs)
        $tenant = Tenant::create([
            'id'           => $request->subdomain,
            'company_name' => $request->company_name,
            'plan'         => 'demo',
            'status'       => 'active',
        ]);

        // สร้าง Domain
        $centralDomain = config('app.url');
        $host = parse_url($centralDomain, PHP_URL_HOST) ?? 'localhost';
        $tenant->domains()->create([
            'domain' => $request->subdomain . '.' . $host,
        ]);

        // สร้าง User คนแรกใน Tenant DB
        tenancy()->initialize($tenant);

        \App\Models\User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        tenancy()->end();

        return redirect()->route('register.success', ['subdomain' => $request->subdomain]);
    }
}
