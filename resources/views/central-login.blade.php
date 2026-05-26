<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ — ProcureThai</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;600;700;800&family=Sarabun:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root { --navy:#0d1b2a; --accent:#00c2ff; --gold:#f5a623; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Sarabun',sans-serif; background:var(--navy); min-height:100vh; display:flex; flex-direction:column; }
        h1,h2,h3 { font-family:'Prompt',sans-serif; }
        nav { background:rgba(13,27,42,0.97); padding:14px 0; border-bottom:1px solid rgba(0,194,255,0.2); }
        .nav-inner { max-width:1200px; margin:0 auto; padding:0 24px; display:flex; align-items:center; justify-content:space-between; }
        .logo { color:white; font-family:'Prompt',sans-serif; font-weight:700; font-size:1.4rem; text-decoration:none; }
        .logo span { color:var(--accent); }
        .page { flex:1; display:flex; align-items:center; justify-content:center; padding:60px 24px; }
        .card {
            background:rgba(255,255,255,0.05); border:1px solid rgba(0,194,255,0.2);
            border-radius:20px; padding:48px; width:100%; max-width:440px;
        }
        .card h2 { color:white; font-size:1.6rem; font-weight:800; margin-bottom:8px; }
        .card .sub { color:rgba(255,255,255,0.5); font-size:0.9rem; margin-bottom:32px; }
        label { display:block; color:rgba(255,255,255,0.8); font-size:0.88rem; font-weight:600; margin-bottom:8px; }
        .input-wrap { display:flex; align-items:center; }
        .input-prefix {
            background:rgba(0,194,255,0.1); border:1px solid rgba(255,255,255,0.15);
            border-right:none; color:rgba(255,255,255,0.4);
            padding:12px 14px; border-radius:8px 0 0 8px; font-size:0.85rem; white-space:nowrap;
        }
        input[type=text] {
            flex:1; padding:12px 16px; border-radius:0 8px 8px 0;
            border:1px solid rgba(255,255,255,0.15); background:rgba(255,255,255,0.07);
            color:white; font-family:'Sarabun',sans-serif; font-size:0.95rem; outline:none;
        }
        input:focus { border-color:var(--accent); }
        input::placeholder { color:rgba(255,255,255,0.3); }
        .hint { color:rgba(255,255,255,0.35); font-size:0.78rem; margin-top:6px; margin-bottom:24px; }
        .btn {
            width:100%; padding:13px; border-radius:8px; border:none; cursor:pointer;
            background:var(--accent); color:var(--navy);
            font-family:'Prompt',sans-serif; font-weight:700; font-size:1rem;
            transition:all 0.3s;
        }
        .btn:hover { background:var(--gold); }
        .register-link { text-align:center; margin-top:20px; color:rgba(255,255,255,0.4); font-size:0.88rem; }
        .register-link a { color:var(--accent); text-decoration:none; }
        .divider { border:none; border-top:1px solid rgba(255,255,255,0.1); margin:24px 0; }
        .admin-link { text-align:center; }
        .admin-link a { color:rgba(255,255,255,0.3); font-size:0.8rem; text-decoration:none; }
        .admin-link a:hover { color:rgba(255,255,255,0.6); }
    </style>
</head>
<body>
<nav>
    <div class="nav-inner">
        <a href="/" class="logo">Procure<span>Thai</span></a>
        <a href="/register" style="color:var(--accent);text-decoration:none;font-size:0.9rem">ทดลองใช้ฟรี →</a>
    </div>
</nav>

<div class="page">
    <div class="card">
        <h2>เข้าสู่ระบบ</h2>
        <p class="sub">กรอก Subdomain ของบริษัทคุณเพื่อเข้าสู่ระบบ</p>

        <form onsubmit="goToApp(event)">
            <label>Subdomain ของบริษัทคุณ</label>
            <div class="input-wrap">
                <div class="input-prefix">https://</div>
                <input type="text" id="subdomain" placeholder="mycompany"
                       pattern="[a-z0-9\-]+" required
                       oninput="this.value=this.value.toLowerCase().replace(/[^a-z0-9-]/g,'')">
            </div>
            <div class="hint">.procurethai.uk</div>
            <button type="submit" class="btn">เข้าสู่ระบบ →</button>
        </form>

        <div class="register-link">
            ยังไม่มีบัญชี? <a href="/register">ทดลองใช้ฟรี 30 วัน</a>
        </div>

        <hr class="divider">
        <div class="admin-link">
            <a href="/superadmin/login">เข้าสู่ระบบในฐานะผู้ดูแลระบบ</a>
        </div>
    </div>
</div>

<script>
function goToApp(e) {
    e.preventDefault();
    const sub = document.getElementById('subdomain').value.trim();
    if (!sub) return;
    // Local dev: ไปที่ /app/login พร้อม query string (สำหรับ dev ที่ใช้ localhost)
    const isLocal = location.hostname === 'localhost' || location.hostname === '127.0.0.1';
    if (isLocal) {
        alert('Local dev: เปิด http://' + sub + '.localhost:8080/app/login\n\nหรือเพิ่ม "127.0.0.1 ' + sub + '.localhost" ใน hosts file ก่อน');
        return;
    }
    window.location.href = 'http://' + sub + '.procurethai.uk/app/login';
}
</script>
</body>
</html>
