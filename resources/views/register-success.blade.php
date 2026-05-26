<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสำเร็จ — ProcureThai</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;600;700;800&family=Sarabun:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root { --navy:#0d1b2a; --accent:#00c2ff; --gold:#f5a623; --green:#27ae60; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Sarabun',sans-serif; background:var(--navy); min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px; }
        h1,h2,h3 { font-family:'Prompt',sans-serif; }
        .card {
            background:rgba(255,255,255,0.05); border:1px solid rgba(0,194,255,0.2);
            border-radius:20px; padding:56px 48px; text-align:center; max-width:500px; width:100%;
        }
        .icon { font-size:4rem; margin-bottom:20px; }
        h2 { color:white; font-size:1.8rem; font-weight:800; margin-bottom:12px; }
        .sub { color:rgba(255,255,255,0.6); margin-bottom:32px; line-height:1.7; }
        .url-box {
            background:rgba(0,194,255,0.1); border:1px solid rgba(0,194,255,0.3);
            border-radius:10px; padding:16px 20px; margin-bottom:28px;
        }
        .url-label { color:rgba(255,255,255,0.5); font-size:0.8rem; margin-bottom:6px; }
        .url-text { color:var(--accent); font-family:'Prompt',sans-serif; font-weight:700; font-size:1rem; }
        .btn {
            display:inline-block; background:var(--gold); color:var(--navy);
            padding:14px 36px; border-radius:8px; text-decoration:none;
            font-family:'Prompt',sans-serif; font-weight:700; font-size:1rem;
            transition:all 0.3s;
        }
        .btn:hover { background:white; transform:translateY(-2px); }
    </style>
</head>
<body>
<div class="card">
    <div class="icon">🎉</div>
    <h2>ระบบของคุณพร้อมใช้งานแล้ว!</h2>
    <p class="sub">บัญชีของคุณถูกสร้างเรียบร้อยแล้ว<br>คลิกปุ่มด้านล่างเพื่อเข้าสู่ระบบได้เลย</p>

    <div class="url-box">
        <div class="url-label">URL ของบริษัทคุณ</div>
        <div class="url-text">{{ $subdomain }}.procurethai.uk/app</div>
    </div>

    <a href="http://{{ $subdomain }}.procurethai.uk/app/login" class="btn">
        🚀 เข้าสู่ระบบ
    </a>
</div>
</body>
</html>
