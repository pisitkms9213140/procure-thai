<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสำเร็จ — ProcureThai</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800&family=Sarabun:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy:   #0d1b2a;
            --navy2:  #111f30;
            --accent: #00c2ff;
            --gold:   #f5a623;
            --green:  #10b981;
            --green2: #059669;
        }
        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            font-family:'Sarabun',sans-serif;
            background:var(--navy);
            min-height:100vh;
            overflow-x:hidden;
        }
        h1,h2,h3,h4 { font-family:'Prompt',sans-serif; }

        /* ─── Animated grid bg ─── */
        body::before {
            content:'';
            position:fixed; inset:0; z-index:0;
            background-image:
                linear-gradient(rgba(0,194,255,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0,194,255,0.04) 1px, transparent 1px);
            background-size:50px 50px;
        }

        /* ─── Confetti particles ─── */
        .confetti { position:fixed; top:0; left:0; width:100%; height:100%; pointer-events:none; z-index:1; overflow:hidden; }
        .confetti span {
            position:absolute; top:-10px; width:8px; height:8px; border-radius:2px;
            animation:fall linear infinite;
        }
        .confetti span:nth-child(1)  { left:5%;   background:#f5a623; animation-duration:4s;  animation-delay:0s;   width:6px; height:6px; }
        .confetti span:nth-child(2)  { left:15%;  background:#00c2ff; animation-duration:5s;  animation-delay:.5s;  border-radius:50%; }
        .confetti span:nth-child(3)  { left:25%;  background:#10b981; animation-duration:3.5s;animation-delay:1s;   width:10px; height:5px; }
        .confetti span:nth-child(4)  { left:35%;  background:#f5a623; animation-duration:4.5s;animation-delay:.2s;  }
        .confetti span:nth-child(5)  { left:45%;  background:#a78bfa; animation-duration:3.8s;animation-delay:1.3s; width:5px; }
        .confetti span:nth-child(6)  { left:55%;  background:#00c2ff; animation-duration:5.2s;animation-delay:.7s;  border-radius:50%; }
        .confetti span:nth-child(7)  { left:65%;  background:#f472b6; animation-duration:4.2s;animation-delay:.1s;  }
        .confetti span:nth-child(8)  { left:75%;  background:#10b981; animation-duration:3.6s;animation-delay:1.8s; width:6px; height:10px; }
        .confetti span:nth-child(9)  { left:85%;  background:#f5a623; animation-duration:4.8s;animation-delay:.9s;  border-radius:50%; }
        .confetti span:nth-child(10) { left:92%;  background:#a78bfa; animation-duration:3.2s;animation-delay:.4s;  width:9px; height:5px; }
        @keyframes fall {
            0%   { transform:translateY(-20px) rotate(0deg); opacity:1; }
            80%  { opacity:.8; }
            100% { transform:translateY(100vh) rotate(360deg); opacity:0; }
        }

        /* ─── Navbar ─── */
        nav {
            position:relative; z-index:10;
            background:rgba(13,27,42,0.9); backdrop-filter:blur(12px);
            padding:16px 0; border-bottom:1px solid rgba(0,194,255,0.2);
        }
        .nav-inner {
            max-width:1100px; margin:0 auto; padding:0 24px;
            display:flex; align-items:center; justify-content:space-between;
        }
        .logo { color:white; font-family:'Prompt',sans-serif; font-weight:700; font-size:1.35rem; text-decoration:none; }
        .logo span { color:var(--accent); }
        .nav-badge {
            background:rgba(16,185,129,0.15); border:1px solid rgba(16,185,129,0.4);
            color:var(--green); padding:5px 12px; border-radius:20px; font-size:0.78rem; font-weight:600;
            display:flex; align-items:center; gap:6px;
        }
        .dot { width:6px; height:6px; border-radius:50%; background:var(--green); animation:pulse 1.5s ease-in-out infinite; }
        @keyframes pulse { 0%,100%{opacity:1; transform:scale(1);} 50%{opacity:.5; transform:scale(.8);} }

        /* ─── Main layout ─── */
        .page {
            position:relative; z-index:2;
            min-height:calc(100vh - 60px);
            display:flex; align-items:center; justify-content:center;
            padding:40px 24px;
        }
        .container { max-width:680px; width:100%; text-align:center; }

        /* ─── Check circle animation ─── */
        .check-wrap {
            width:100px; height:100px; margin:0 auto 32px;
            position:relative;
        }
        .check-ring {
            width:100px; height:100px; border-radius:50%;
            border:3px solid var(--green);
            animation:ringIn .5s ease-out both;
            position:relative;
        }
        @keyframes ringIn { from{transform:scale(.3); opacity:0;} to{transform:scale(1); opacity:1;} }
        .check-ring::after {
            content:''; position:absolute; inset:0; border-radius:50%;
            background:rgba(16,185,129,0.12);
        }
        .check-svg {
            position:absolute; top:50%; left:50%; transform:translate(-50%,-50%);
            width:48px; height:48px;
        }
        .check-path {
            stroke:#10b981; stroke-width:4; fill:none; stroke-linecap:round; stroke-linejoin:round;
            stroke-dasharray:60; stroke-dashoffset:60;
            animation:drawCheck .6s ease-out .4s both;
        }
        @keyframes drawCheck { to{stroke-dashoffset:0;} }
        .glow-ring {
            position:absolute; inset:-12px; border-radius:50%;
            border:1px solid rgba(16,185,129,0.25);
            animation:glow 2s ease-in-out 1s infinite;
        }
        @keyframes glow { 0%,100%{transform:scale(1); opacity:.5;} 50%{transform:scale(1.1); opacity:.1;} }

        /* ─── Headline ─── */
        .headline {
            color:white; font-size:2rem; font-weight:800; margin-bottom:10px; line-height:1.2;
            animation:slideUp .5s ease-out .3s both;
        }
        .headline span { color:var(--accent); }
        .subheadline {
            color:rgba(255,255,255,0.55); font-size:1rem; line-height:1.7;
            margin-bottom:36px;
            animation:slideUp .5s ease-out .45s both;
        }
        @keyframes slideUp { from{transform:translateY(20px);opacity:0;} to{transform:translateY(0);opacity:1;} }

        /* ─── URL Card ─── */
        .url-card {
            background:rgba(0,194,255,0.07); border:1px solid rgba(0,194,255,0.25);
            border-radius:14px; padding:20px 24px; margin-bottom:32px;
            animation:slideUp .5s ease-out .55s both;
            position:relative; overflow:hidden;
        }
        .url-card::before {
            content:''; position:absolute; top:0; left:0; right:0; height:2px;
            background:linear-gradient(90deg, transparent, var(--accent), transparent);
        }
        .url-label {
            color:rgba(255,255,255,0.45); font-size:0.75rem; font-weight:600;
            letter-spacing:1.5px; text-transform:uppercase; margin-bottom:10px;
        }
        .url-row { display:flex; align-items:center; justify-content:center; gap:10px; flex-wrap:wrap; }
        .url-text {
            color:var(--accent); font-family:'Prompt',sans-serif; font-weight:700;
            font-size:1.1rem; letter-spacing:.3px;
        }
        .copy-btn {
            background:rgba(0,194,255,0.15); border:1px solid rgba(0,194,255,0.35);
            color:var(--accent); padding:5px 14px; border-radius:6px;
            font-size:0.78rem; font-weight:600; cursor:pointer;
            transition:all .2s; white-space:nowrap;
        }
        .copy-btn:hover { background:rgba(0,194,255,0.25); }
        .copy-btn.copied { background:rgba(16,185,129,0.2); border-color:var(--green); color:var(--green); }

        /* ─── Next steps ─── */
        .steps-label {
            color:rgba(255,255,255,0.4); font-size:0.75rem; font-weight:600;
            letter-spacing:1.5px; text-transform:uppercase;
            margin-bottom:14px;
            animation:slideUp .5s ease-out .65s both;
        }
        .steps {
            display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-bottom:36px;
            animation:slideUp .5s ease-out .7s both;
        }
        @media(max-width:480px){ .steps{ grid-template-columns:1fr; } }
        .step {
            background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08);
            border-radius:12px; padding:18px 14px; text-align:left;
            transition:border-color .2s;
        }
        .step:hover { border-color:rgba(0,194,255,0.2); }
        .step-num {
            width:28px; height:28px; border-radius:50%;
            background:linear-gradient(135deg, var(--accent), #0080cc);
            color:white; font-weight:800; font-size:0.82rem;
            display:flex; align-items:center; justify-content:center;
            margin-bottom:10px;
        }
        .step-title { color:white; font-family:'Prompt',sans-serif; font-size:0.85rem; font-weight:600; margin-bottom:4px; }
        .step-desc { color:rgba(255,255,255,0.45); font-size:0.78rem; line-height:1.5; }

        /* ─── CTA ─── */
        .cta-wrap { animation:slideUp .5s ease-out .8s both; }
        .btn-primary {
            display:inline-flex; align-items:center; gap:10px;
            background:linear-gradient(135deg, var(--gold), #e8940a);
            color:var(--navy); padding:15px 40px; border-radius:10px;
            font-family:'Prompt',sans-serif; font-weight:700; font-size:1rem;
            text-decoration:none; transition:all .3s;
            box-shadow:0 4px 20px rgba(245,166,35,0.3);
        }
        .btn-primary:hover { transform:translateY(-2px); box-shadow:0 8px 28px rgba(245,166,35,0.4); }
        .btn-primary svg { width:18px; height:18px; transition:transform .3s; }
        .btn-primary:hover svg { transform:translateX(3px); }

        .footer-note {
            margin-top:20px; color:rgba(255,255,255,0.3); font-size:0.8rem;
            animation:slideUp .5s ease-out .9s both;
        }
        .footer-note a { color:rgba(0,194,255,0.7); text-decoration:none; }
    </style>
</head>
<body>

<!-- Confetti -->
<div class="confetti">
    <span></span><span></span><span></span><span></span><span></span>
    <span></span><span></span><span></span><span></span><span></span>
</div>

<!-- Navbar -->
<nav>
    <div class="nav-inner">
        <a href="/" class="logo">Procure<span>Thai</span></a>
        <div class="nav-badge">
            <div class="dot"></div>
            บัญชีถูกสร้างแล้ว
        </div>
    </div>
</nav>

<!-- Main -->
<div class="page">
    <div class="container">

        <!-- Check animation -->
        <div class="check-wrap">
            <div class="glow-ring"></div>
            <div class="check-ring">
                <svg class="check-svg" viewBox="0 0 48 48">
                    <polyline class="check-path" points="10,26 20,36 38,16"/>
                </svg>
            </div>
        </div>

        <!-- Headline -->
        <h1 class="headline">ยินดีต้อนรับสู่ <span>ProcureThai</span>!</h1>
        <p class="subheadline">
            ระบบจัดซื้อของคุณพร้อมใช้งานแล้ว<br>
            ลงชื่อเข้าใช้และตั้งค่าเริ่มต้นภายใน 5 นาที
        </p>

        <!-- URL -->
        <div class="url-card">
            <div class="url-label">URL สำหรับเข้าใช้งานระบบของคุณ</div>
            <div class="url-row">
                <span class="url-text" id="urlText">{{ $subdomain }}.procurethai.uk/app</span>
                <button class="copy-btn" id="copyBtn" onclick="copyUrl()">
                    📋 คัดลอก
                </button>
            </div>
        </div>

        <!-- Next Steps -->
        <div class="steps-label">ขั้นตอนถัดไป</div>
        <div class="steps">
            <div class="step">
                <div class="step-num">1</div>
                <div class="step-title">เข้าสู่ระบบ</div>
                <div class="step-desc">ใช้ email และ password ที่เพิ่งสมัคร</div>
            </div>
            <div class="step">
                <div class="step-num">2</div>
                <div class="step-title">ตั้งค่าข้อมูลหลัก</div>
                <div class="step-desc">นำเข้าสินค้า, Vendor, คลังสินค้า และ Open PO</div>
            </div>
            <div class="step">
                <div class="step-num">3</div>
                <div class="step-title">เชิญทีมงาน</div>
                <div class="step-desc">ส่ง link ให้ Vendor เข้ามาจัดการข้อมูลตัวเอง</div>
            </div>
        </div>

        <!-- CTA -->
        <div class="cta-wrap">
            <a href="http://{{ $subdomain }}.procurethai.uk/app/login" class="btn-primary">
                เข้าสู่ระบบและเริ่มตั้งค่า
                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                </svg>
            </a>
        </div>

        <p class="footer-note">
            มีปัญหา? <a href="mailto:support@procurethai.uk">ติดต่อทีมงาน</a>
            &nbsp;·&nbsp; ข้อมูลเข้าสู่ระบบถูกส่งไปยัง email ของคุณแล้ว
        </p>

    </div>
</div>

<script>
function copyUrl() {
    const url = document.getElementById('urlText').textContent.trim();
    navigator.clipboard.writeText('https://' + url).then(() => {
        const btn = document.getElementById('copyBtn');
        btn.textContent = '✅ คัดลอกแล้ว';
        btn.classList.add('copied');
        setTimeout(() => {
            btn.textContent = '📋 คัดลอก';
            btn.classList.remove('copied');
        }, 2500);
    });
}
</script>
</body>
</html>
