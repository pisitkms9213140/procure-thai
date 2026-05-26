<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ทดลองใช้ฟรี 30 วัน — ProcureThai</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800&family=Sarabun:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root { --navy:#0d1b2a; --accent:#00c2ff; --gold:#f5a623; --green:#27ae60; --red:#e74c3c; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Sarabun',sans-serif; background:var(--navy); min-height:100vh; display:flex; flex-direction:column; }
        h1,h2,h3,h4 { font-family:'Prompt',sans-serif; }

        nav { background:rgba(13,27,42,0.97); padding:14px 0; border-bottom:1px solid rgba(0,194,255,0.2); }
        .nav-inner { max-width:1200px; margin:0 auto; padding:0 24px; display:flex; align-items:center; justify-content:space-between; }
        .logo { color:white; font-family:'Prompt',sans-serif; font-weight:700; font-size:1.4rem; text-decoration:none; }
        .logo span { color:var(--accent); }

        .page { flex:1; display:flex; align-items:center; justify-content:center; padding:60px 24px; }
        .card {
            background:rgba(255,255,255,0.05); border:1px solid rgba(0,194,255,0.2);
            border-radius:20px; padding:48px; width:100%; max-width:520px;
            backdrop-filter:blur(10px);
        }
        .card-badge {
            display:inline-block; background:rgba(0,194,255,0.15); border:1px solid rgba(0,194,255,0.4);
            color:var(--accent); padding:5px 14px; border-radius:20px;
            font-size:0.78rem; font-weight:600; margin-bottom:16px; letter-spacing:1px;
        }
        .card h2 { color:white; font-size:1.8rem; font-weight:800; margin-bottom:8px; }
        .card .sub { color:rgba(255,255,255,0.6); font-size:0.95rem; margin-bottom:32px; line-height:1.6; }

        .form-group { margin-bottom:20px; }
        label { display:block; color:rgba(255,255,255,0.8); font-size:0.88rem; font-weight:600; margin-bottom:8px; }
        input[type=text], input[type=email], input[type=password] {
            width:100%; padding:12px 16px; border-radius:8px;
            border:1px solid rgba(255,255,255,0.15); background:rgba(255,255,255,0.07);
            color:white; font-family:'Sarabun',sans-serif; font-size:0.95rem;
            transition:border-color 0.3s; outline:none;
        }
        input:focus { border-color:var(--accent); }
        input::placeholder { color:rgba(255,255,255,0.3); }

        .subdomain-wrap { display:flex; align-items:center; gap:0; }
        .subdomain-wrap input { border-radius:8px 0 0 8px; border-right:none; }
        .subdomain-suffix {
            background:rgba(0,194,255,0.15); border:1px solid rgba(255,255,255,0.15);
            border-left:none; color:rgba(255,255,255,0.5);
            padding:12px 14px; border-radius:0 8px 8px 0;
            font-size:0.85rem; white-space:nowrap;
        }

        .hint { color:rgba(255,255,255,0.4); font-size:0.78rem; margin-top:5px; }
        .error { color:#ff7675; font-size:0.8rem; margin-top:4px; }

        .btn-submit {
            width:100%; padding:14px; border-radius:8px; border:none; cursor:pointer;
            background:var(--gold); color:var(--navy);
            font-family:'Prompt',sans-serif; font-weight:700; font-size:1rem;
            transition:all 0.3s; margin-top:8px;
        }
        .btn-submit:hover { background:white; transform:translateY(-1px); }

        .login-link { text-align:center; margin-top:20px; color:rgba(255,255,255,0.5); font-size:0.88rem; }
        .login-link a { color:var(--accent); text-decoration:none; }

        .perks { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:28px; }
        .perk { display:flex; align-items:center; gap:8px; color:rgba(255,255,255,0.7); font-size:0.82rem; }
        .perk-icon { color:var(--green); font-size:1rem; }

        .alert-error {
            background:rgba(231,76,60,0.15); border:1px solid rgba(231,76,60,0.4);
            border-radius:8px; padding:12px 16px; margin-bottom:20px;
            color:#ff7675; font-size:0.88rem;
        }
    </style>
</head>
<body>
<nav>
    <div class="nav-inner">
        <a href="/" class="logo">Procure<span>Thai</span></a>
        <a href="/login" style="color:rgba(255,255,255,0.7);text-decoration:none;font-size:0.9rem">มีบัญชีแล้ว? เข้าสู่ระบบ</a>
    </div>
</nav>

<div class="page">
    <div class="card">
        <div class="card-badge">🎯 ทดลองฟรี 30 วัน</div>
        <h2>เริ่มต้นใช้งาน<br>ProcureThai</h2>
        <p class="sub">ไม่ต้องใช้บัตรเครดิต · ยกเลิกได้ทุกเมื่อ · ทุก module ใช้ได้ทันที</p>

        <div class="perks">
            <div class="perk"><span class="perk-icon">✅</span> ทุก module ใช้ได้</div>
            <div class="perk"><span class="perk-icon">✅</span> Supplier สูงสุด 10 ราย</div>
            <div class="perk"><span class="perk-icon">✅</span> RQ/PO ไม่จำกัด 30 วัน</div>
            <div class="perk"><span class="perk-icon">✅</span> ตั้งค่าใน 5 นาที</div>
        </div>

        @if($errors->any())
        <div class="alert-error">
            @foreach($errors->all() as $error)
                <div>• {{ $error }}</div>
            @endforeach
        </div>
        @endif

        <form method="POST" action="/register">
            @csrf

            <div class="form-group">
                <label>ชื่อบริษัท / องค์กร *</label>
                <input type="text" name="company_name" value="{{ old('company_name') }}" placeholder="เช่น บริษัท สยามแพ็ค จำกัด" required>
            </div>

            <div class="form-group">
                <label>Subdomain (URL ของคุณ) *</label>
                <div class="subdomain-wrap">
                    <input type="text" name="subdomain" value="{{ old('subdomain') }}"
                           placeholder="mycompany" pattern="[a-z0-9\-]+" required
                           oninput="this.value=this.value.toLowerCase().replace(/[^a-z0-9-]/g,'')">
                    <div class="subdomain-suffix">.procurethai.uk</div>
                </div>
                <div class="hint">ใช้ได้เฉพาะ a-z, 0-9 และ - (ห้ามมีช่องว่าง)</div>
            </div>

            <div class="form-group">
                <label>ชื่อผู้ใช้งาน *</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="ชื่อ-นามสกุล" required>
            </div>

            <div class="form-group">
                <label>อีเมล *</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="admin@company.com" required>
            </div>

            <div class="form-group">
                <label>รหัสผ่าน *</label>
                <input type="password" name="password" placeholder="อย่างน้อย 8 ตัวอักษร" required>
            </div>

            <div class="form-group">
                <label>ยืนยันรหัสผ่าน *</label>
                <input type="password" name="password_confirmation" placeholder="กรอกรหัสผ่านอีกครั้ง" required>
            </div>

            <button type="submit" class="btn-submit">🚀 เริ่มทดลองใช้ฟรีเลย</button>
        </form>

        <div class="login-link">
            มีบัญชีอยู่แล้ว? <a href="/login">เข้าสู่ระบบ</a>
        </div>
    </div>
</div>
</body>
</html>
