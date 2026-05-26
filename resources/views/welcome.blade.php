<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProcureThai — ระบบจัดซื้อออนไลน์สำหรับธุรกิจไทย</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800&family=Sarabun:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy: #0d1b2a;
            --blue: #1a3a5c;
            --accent: #00c2ff;
            --gold: #f5a623;
            --red: #e74c3c;
            --green: #27ae60;
            --light: #f0f4f8;
            --white: #ffffff;
            --text: #2c3e50;
            --muted: #7f8c8d;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Sarabun', sans-serif;
            color: var(--text);
            background: var(--white);
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5 { font-family: 'Prompt', sans-serif; }

        /* ─── NAVBAR ─── */
        nav {
            position: fixed; top: 0; width: 100%; z-index: 1000;
            background: rgba(13, 27, 42, 0.97);
            backdrop-filter: blur(10px);
            padding: 14px 0;
            border-bottom: 1px solid rgba(0,194,255,0.2);
        }
        .nav-inner {
            max-width: 1200px; margin: 0 auto; padding: 0 24px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .logo { color: white; font-family: 'Prompt', sans-serif; font-weight: 700; font-size: 1.4rem; text-decoration: none; }
        .logo span { color: var(--accent); }
        .nav-btns { display: flex; gap: 12px; }
        .btn-nav-outline {
            border: 1px solid rgba(255,255,255,0.4); color: white;
            padding: 8px 20px; border-radius: 6px; text-decoration: none;
            font-family: 'Prompt', sans-serif; font-size: 0.85rem;
            transition: all 0.3s;
        }
        .btn-nav-outline:hover { border-color: var(--accent); color: var(--accent); }
        .btn-nav-primary {
            background: var(--accent); color: var(--navy);
            padding: 8px 20px; border-radius: 6px; text-decoration: none;
            font-family: 'Prompt', sans-serif; font-size: 0.85rem; font-weight: 600;
            transition: all 0.3s;
        }
        .btn-nav-primary:hover { background: white; }

        /* ─── HERO ─── */
        .hero {
            min-height: 100vh;
            background: var(--navy);
            display: flex; align-items: center;
            position: relative; overflow: hidden;
            padding-top: 80px;
        }
        .hero::before {
            content: '';
            position: absolute; inset: 0;
            background: radial-gradient(ellipse at 70% 50%, rgba(0,194,255,0.12) 0%, transparent 60%),
                        radial-gradient(ellipse at 20% 80%, rgba(26,58,92,0.8) 0%, transparent 50%);
        }
        .hero-grid {
            max-width: 1200px; margin: 0 auto; padding: 0 24px;
            display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center;
            position: relative; z-index: 1;
        }
        .hero-badge {
            display: inline-block;
            background: rgba(0,194,255,0.15); border: 1px solid rgba(0,194,255,0.4);
            color: var(--accent); padding: 6px 16px; border-radius: 20px;
            font-size: 0.8rem; font-weight: 600; margin-bottom: 20px;
            letter-spacing: 1px;
        }
        .hero h1 {
            font-size: 3rem; font-weight: 800; color: white;
            line-height: 1.2; margin-bottom: 20px;
        }
        .hero h1 span { color: var(--accent); }
        .hero p {
            color: rgba(255,255,255,0.7); font-size: 1.1rem;
            line-height: 1.8; margin-bottom: 32px;
        }
        .hero-btns { display: flex; gap: 16px; flex-wrap: wrap; }
        .btn-primary-lg {
            background: var(--gold); color: var(--navy);
            padding: 14px 32px; border-radius: 8px; text-decoration: none;
            font-family: 'Prompt', sans-serif; font-weight: 700; font-size: 1rem;
            transition: all 0.3s; display: inline-block;
        }
        .btn-primary-lg:hover { background: white; transform: translateY(-2px); }
        .btn-outline-lg {
            border: 2px solid rgba(255,255,255,0.3); color: white;
            padding: 14px 32px; border-radius: 8px; text-decoration: none;
            font-family: 'Prompt', sans-serif; font-weight: 600; font-size: 1rem;
            transition: all 0.3s; display: inline-block;
        }
        .btn-outline-lg:hover { border-color: var(--accent); color: var(--accent); }
        .hero-stats {
            display: flex; gap: 32px; margin-top: 40px;
        }
        .stat { border-left: 3px solid var(--accent); padding-left: 16px; }
        .stat-num { font-family: 'Prompt', sans-serif; font-size: 1.8rem; font-weight: 800; color: white; }
        .stat-label { color: rgba(255,255,255,0.5); font-size: 0.8rem; }

        /* ─── HERO VISUAL ─── */
        .hero-visual {
            position: relative;
        }
        .dashboard-mock {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(0,194,255,0.2);
            border-radius: 16px; padding: 24px;
            backdrop-filter: blur(10px);
        }
        .mock-header {
            display: flex; align-items: center; gap: 8px; margin-bottom: 20px;
        }
        .mock-dot { width: 12px; height: 12px; border-radius: 50%; }
        .mock-title { color: rgba(255,255,255,0.8); font-size: 0.9rem; font-family: 'Prompt', sans-serif; }
        .mock-cards { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 16px; }
        .mock-card {
            background: rgba(255,255,255,0.08); border-radius: 8px; padding: 14px;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .mock-card-num { font-family: 'Prompt', sans-serif; font-size: 1.5rem; font-weight: 700; }
        .mock-card-label { color: rgba(255,255,255,0.5); font-size: 0.7rem; margin-top: 4px; }
        .mock-table-row {
            display: flex; align-items: center; gap: 10px;
            background: rgba(255,255,255,0.05); border-radius: 6px;
            padding: 10px 12px; margin-bottom: 8px;
        }
        .mock-status {
            padding: 3px 8px; border-radius: 10px; font-size: 0.65rem; font-weight: 600;
        }
        .status-green { background: rgba(39,174,96,0.3); color: #6fcf97; }
        .status-yellow { background: rgba(245,166,35,0.3); color: #f5a623; }
        .status-blue { background: rgba(0,194,255,0.3); color: #00c2ff; }
        .mock-text { color: rgba(255,255,255,0.7); font-size: 0.75rem; }
        .mock-amount { color: white; font-family: 'Prompt', sans-serif; font-size: 0.8rem; font-weight: 600; margin-left: auto; }

        /* ─── PROMO BANNER ─── */
        .promo-banner {
            background: linear-gradient(135deg, #c0392b, #e74c3c);
            padding: 20px 0; position: relative; overflow: hidden;
        }
        .promo-banner::before {
            content: '🔥 โปรโมชั่นพิเศษ 🔥';
            position: absolute; right: -100px; top: 50%; transform: translateY(-50%);
            font-size: 5rem; opacity: 0.1; white-space: nowrap;
        }
        .promo-inner {
            max-width: 1200px; margin: 0 auto; padding: 0 24px;
            display: flex; align-items: center; justify-content: center; gap: 40px;
            flex-wrap: wrap;
        }
        .promo-text {
            color: white; font-family: 'Prompt', sans-serif; font-size: 1.1rem; font-weight: 600;
        }
        .promo-price {
            display: flex; align-items: center; gap: 16px;
        }
        .price-old {
            color: rgba(255,255,255,0.6); font-size: 1.2rem;
            text-decoration: line-through; font-family: 'Prompt', sans-serif;
        }
        .price-new {
            color: var(--gold); font-size: 2rem; font-weight: 800;
            font-family: 'Prompt', sans-serif;
        }
        .price-save {
            background: var(--gold); color: var(--navy);
            padding: 6px 14px; border-radius: 20px;
            font-family: 'Prompt', sans-serif; font-weight: 700; font-size: 0.9rem;
        }
        .btn-promo {
            background: white; color: var(--red);
            padding: 12px 28px; border-radius: 8px; text-decoration: none;
            font-family: 'Prompt', sans-serif; font-weight: 700; font-size: 0.95rem;
            transition: all 0.3s;
        }
        .btn-promo:hover { background: var(--gold); }

        /* ─── PROBLEM SECTION ─── */
        .section { padding: 80px 0; }
        .section-inner { max-width: 1200px; margin: 0 auto; padding: 0 24px; }
        .section-label {
            color: var(--accent); font-size: 0.8rem; font-weight: 700;
            letter-spacing: 2px; text-transform: uppercase; margin-bottom: 12px;
        }
        .section-title {
            font-size: 2.2rem; font-weight: 800; color: var(--navy);
            margin-bottom: 16px; line-height: 1.3;
        }
        .section-sub { color: var(--muted); font-size: 1.05rem; max-width: 600px; line-height: 1.7; }

        /* ─── LOOP INFOGRAPHIC ─── */
        .problem-bg { background: #fafbfc; }
        .loop-container {
            position: relative; max-width: 800px; margin: 60px auto 0;
        }
        .loop-title {
            text-align: center; color: var(--red);
            font-family: 'Prompt', sans-serif; font-size: 1.3rem; font-weight: 700;
            margin-bottom: 32px;
        }
        .loop-grid {
            display: grid; grid-template-columns: 1fr 60px 1fr;
            gap: 0; align-items: start;
        }
        .loop-col { display: flex; flex-direction: column; gap: 0; }
        .loop-item {
            background: white; border: 1px solid #e8edf2; border-radius: 10px;
            padding: 14px 16px; margin-bottom: 12px; position: relative;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        .loop-item.problem { border-left: 4px solid var(--red); }
        .loop-num {
            position: absolute; top: -10px; left: 12px;
            background: var(--red); color: white;
            width: 22px; height: 22px; border-radius: 50%;
            font-size: 0.7rem; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Prompt', sans-serif;
        }
        .loop-item-text { font-size: 0.85rem; color: var(--text); line-height: 1.5; margin-top: 4px; }
        .loop-arrows {
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            padding-top: 20px; gap: 12px;
        }
        .arrow-down { color: var(--red); font-size: 1.5rem; }
        .loop-center {
            background: var(--red); color: white;
            border-radius: 50%; width: 60px; height: 60px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; margin: 20px auto;
        }

        /* ─── HOW IT WORKS ─── */
        .steps-grid {
            display: grid; grid-template-columns: repeat(4, 1fr);
            gap: 0; margin-top: 60px; position: relative;
        }
        .steps-grid::before {
            content: '';
            position: absolute; top: 40px; left: 10%; right: 10%; height: 2px;
            background: linear-gradient(90deg, var(--accent), var(--blue));
            z-index: 0;
        }
        .step {
            text-align: center; padding: 0 16px; position: relative; z-index: 1;
        }
        .step-circle {
            width: 80px; height: 80px; border-radius: 50%;
            background: var(--navy); border: 3px solid var(--accent);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px; font-size: 1.8rem;
            box-shadow: 0 0 20px rgba(0,194,255,0.3);
        }
        .step-num {
            position: absolute; top: -5px; right: calc(50% - 50px);
            background: var(--accent); color: var(--navy);
            width: 24px; height: 24px; border-radius: 50%;
            font-size: 0.75rem; font-weight: 800;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Prompt', sans-serif;
        }
        .step h4 { font-size: 0.95rem; font-weight: 700; color: var(--navy); margin-bottom: 8px; }
        .step p { font-size: 0.82rem; color: var(--muted); line-height: 1.6; }

        /* ─── FEATURES ─── */
        .features-bg { background: var(--navy); }
        .features-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-top: 50px;
        }
        .feature-card {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(0,194,255,0.15);
            border-radius: 12px; padding: 28px;
            transition: all 0.3s;
        }
        .feature-card:hover {
            border-color: var(--accent);
            background: rgba(0,194,255,0.08);
            transform: translateY(-4px);
        }
        .feature-icon { font-size: 2rem; margin-bottom: 16px; }
        .feature-card h4 { color: white; font-size: 1rem; font-weight: 700; margin-bottom: 10px; }
        .feature-card p { color: rgba(255,255,255,0.6); font-size: 0.85rem; line-height: 1.7; }
        .feature-tag {
            display: inline-block; margin-top: 12px;
            background: rgba(0,194,255,0.2); color: var(--accent);
            padding: 3px 10px; border-radius: 10px; font-size: 0.72rem; font-weight: 600;
        }
        .feature-tag.locked {
            background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.4);
        }

        /* ─── PLAN ─── */
        .plan-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 30px;
            max-width: 800px; margin: 50px auto 0;
        }
        .plan-card {
            border: 2px solid #e8edf2; border-radius: 16px; padding: 36px;
            text-align: center; position: relative; overflow: hidden;
        }
        .plan-card.featured {
            border-color: var(--accent);
            box-shadow: 0 0 40px rgba(0,194,255,0.15);
        }
        .plan-card.featured::before {
            content: '🔥 โปรโมชั่น';
            position: absolute; top: 16px; right: -30px;
            background: var(--red); color: white;
            padding: 6px 40px; font-size: 0.75rem; font-weight: 700;
            transform: rotate(45deg); font-family: 'Prompt', sans-serif;
        }
        .plan-name { font-size: 1.3rem; font-weight: 800; color: var(--navy); margin-bottom: 8px; }
        .plan-price-block { margin: 20px 0; }
        .plan-original {
            color: var(--muted); font-size: 1rem;
            text-decoration: line-through; margin-bottom: 4px;
        }
        .plan-current {
            font-size: 2.5rem; font-weight: 800; color: var(--accent);
            font-family: 'Prompt', sans-serif;
        }
        .plan-current span { font-size: 1rem; font-weight: 400; color: var(--muted); }
        .plan-save {
            display: inline-block; background: #fff3cd; color: #856404;
            padding: 4px 12px; border-radius: 10px; font-size: 0.8rem; font-weight: 700;
            margin-top: 8px;
        }
        .plan-features { text-align: left; margin: 24px 0; list-style: none; }
        .plan-features li {
            padding: 8px 0; border-bottom: 1px solid #f0f0f0;
            font-size: 0.88rem; display: flex; align-items: center; gap: 10px;
        }
        .plan-features li:last-child { border-bottom: none; }
        .check { color: var(--green); font-size: 1rem; }
        .cross { color: #ccc; font-size: 1rem; }
        .btn-plan {
            width: 100%; padding: 14px; border-radius: 8px;
            font-family: 'Prompt', sans-serif; font-weight: 700; font-size: 1rem;
            text-decoration: none; display: block; transition: all 0.3s;
        }
        .btn-plan-primary { background: var(--accent); color: var(--navy); }
        .btn-plan-primary:hover { background: var(--gold); }
        .btn-plan-outline { border: 2px solid #dee2e6; color: var(--muted); }

        /* ─── CTA ─── */
        .cta-section {
            background: linear-gradient(135deg, var(--navy), #1a3a5c);
            padding: 80px 0; text-align: center;
        }
        .cta-section h2 { color: white; font-size: 2.2rem; font-weight: 800; margin-bottom: 16px; }
        .cta-section p { color: rgba(255,255,255,0.7); font-size: 1.05rem; margin-bottom: 32px; }
        .btn-cta {
            background: var(--gold); color: var(--navy);
            padding: 16px 40px; border-radius: 8px; text-decoration: none;
            font-family: 'Prompt', sans-serif; font-weight: 800; font-size: 1.1rem;
            display: inline-block; transition: all 0.3s;
        }
        .btn-cta:hover { background: white; transform: translateY(-3px); box-shadow: 0 10px 30px rgba(0,0,0,0.3); }

        /* ─── FOOTER ─── */
        footer {
            background: #0a1520; padding: 30px 0;
            text-align: center; color: rgba(255,255,255,0.4); font-size: 0.85rem;
        }

        /* ─── ANIMATIONS ─── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 20px rgba(0,194,255,0.3); }
            50% { box-shadow: 0 0 40px rgba(0,194,255,0.6); }
        }
        .hero-content { animation: fadeUp 0.8s ease forwards; }
        .hero-visual { animation: fadeUp 0.8s ease 0.2s both; }
        .step-circle { animation: pulse 3s ease-in-out infinite; }

        @media (max-width: 768px) {
            .hero-grid, .steps-grid, .features-grid, .plan-grid { grid-template-columns: 1fr; }
            .hero h1 { font-size: 2rem; }
            .steps-grid::before { display: none; }
            .mock-cards { grid-template-columns: 1fr 1fr; }
            .loop-grid { grid-template-columns: 1fr; }
            .loop-arrows { display: none; }
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav>
    <div class="nav-inner">
        <a href="/" class="logo">Procure<span>Thai</span></a>
        <div class="nav-btns">
            <a href="/masuk" class="btn-nav-outline">เข้าสู่ระบบ</a>
            <a href="/register" class="btn-nav-primary">ทดลองใช้ฟรี</a>
        </div>
    </div>
</nav>

<!-- HERO -->
<section class="hero">
    <div class="hero-grid">
        <div class="hero-content">
            <div class="hero-badge">🇹🇭 ออกแบบมาเพื่อธุรกิจไทย</div>
            <h1>ปิด <span>ลูปนรก</span><br>การจัดซื้อ<br>ให้หมดไป</h1>
            <p>ระบบบริหารจัดซื้อออนไลน์ครบวงจร ตั้งแต่ RQ จนถึง Invoice พร้อม QR จัดส่ง รองรับ SAP B1</p>
            <div class="hero-btns">
                <a href="/register" class="btn-primary-lg">🚀 ทดลองใช้ฟรี 30 วัน</a>
                <a href="#how-it-works" class="btn-outline-lg">ดูวิธีการทำงาน</a>
            </div>
            <div class="hero-stats">
                <div class="stat">
                    <div class="stat-num">100%</div>
                    <div class="stat-label">Web-based ไม่ต้องติดตั้ง</div>
                </div>
                <div class="stat">
                    <div class="stat-num">SAP</div>
                    <div class="stat-label">รองรับ SAP B1</div>
                </div>
                <div class="stat">
                    <div class="stat-num">30วัน</div>
                    <div class="stat-label">ทดลองใช้ฟรี</div>
                </div>
            </div>
        </div>
        <div class="hero-visual">
            <div class="dashboard-mock">
                <div class="mock-header">
                    <div class="mock-dot" style="background:#ff5f57"></div>
                    <div class="mock-dot" style="background:#ffbd2e"></div>
                    <div class="mock-dot" style="background:#28c840"></div>
                    <div class="mock-title" style="margin-left:8px">ProcureThai Dashboard</div>
                </div>
                <div class="mock-cards">
                    <div class="mock-card">
                        <div class="mock-card-num" style="color:#00c2ff">24</div>
                        <div class="mock-card-label">RQ รอดำเนินการ</div>
                    </div>
                    <div class="mock-card">
                        <div class="mock-card-num" style="color:#f5a623">8</div>
                        <div class="mock-card-label">PO รอยืนยัน</div>
                    </div>
                    <div class="mock-card">
                        <div class="mock-card-num" style="color:#e74c3c">3</div>
                        <div class="mock-card-label">⚠️ เกินกำหนด</div>
                    </div>
                </div>
                <div class="mock-table-row">
                    <div class="mock-status status-green">✅ อนุมัติ</div>
                    <div class="mock-text">PO-2026-0142 · บ.สยามแพ็ค</div>
                    <div class="mock-amount">฿84,500</div>
                </div>
                <div class="mock-table-row">
                    <div class="mock-status status-yellow">⏳ รออนุมัติ</div>
                    <div class="mock-text">RQ-2026-0289 · RM วัตถุดิบ A</div>
                    <div class="mock-amount">฿32,000</div>
                </div>
                <div class="mock-table-row">
                    <div class="mock-status status-blue">📦 รับของแล้ว</div>
                    <div class="mock-text">PO-2026-0138 · บ.ไทยพลาสติก</div>
                    <div class="mock-amount">฿120,000</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- PROMO BANNER -->
<div class="promo-banner">
    <div class="promo-inner">
        <div class="promo-text">🔥 โปรโมชั่นพิเศษ สำหรับลูกค้าใหม่!</div>
        <div class="promo-price">
            <div class="price-old">฿30,000/เดือน</div>
            <div class="price-new">฿15,000/เดือน</div>
            <div class="price-save">ลด 50%</div>
        </div>
        <a href="/register" class="btn-promo">รับโปรโมชั่นเลย →</a>
    </div>
</div>

<!-- PROBLEM SECTION -->
<section class="section problem-bg">
    <div class="section-inner">
        <div style="text-align:center">
            <div class="section-label">ปัญหาที่คุณเจอ</div>
            <h2 class="section-title">ลูปนรกการจัดซื้อ<br>ที่หลายบริษัทต้องเผชิญ</h2>
            <p class="section-sub" style="margin:0 auto">เมื่อเอกสารไม่ sync กับการเคลื่อนไหวของเงินและสินค้าจริง ทุกอย่างพังหมด</p>
        </div>
        <div class="loop-container">
            <div class="loop-title">🔄 วงจรปัญหาที่ไม่มีวันสิ้นสุด</div>
            <div class="loop-grid">
                <div class="loop-col">
                    <div class="loop-item problem">
                        <div class="loop-num">1</div>
                        <div class="loop-item-text">📦 คลังส่งบิลช้า → บัญชีไม่ได้รับ</div>
                    </div>
                    <div class="loop-item problem">
                        <div class="loop-num">3</div>
                        <div class="loop-item-text">🚫 Supplier ส่งของไม่ได้ เพราะติดเครดิต</div>
                    </div>
                    <div class="loop-item problem">
                        <div class="loop-num">5</div>
                        <div class="loop-item-text">🏃 ต้องซื้อของด่วน ราคาแพงกว่าปกติ</div>
                    </div>
                    <div class="loop-item problem">
                        <div class="loop-num">7</div>
                        <div class="loop-item-text">📋 ซื้อก่อน — เอกสารทีหลัง ไม่มี PO</div>
                    </div>
                </div>
                <div class="loop-arrows">
                    <div class="loop-center">🔄</div>
                    <div class="arrow-down">↓</div>
                    <div class="arrow-down">↑</div>
                </div>
                <div class="loop-col">
                    <div class="loop-item problem">
                        <div class="loop-num">2</div>
                        <div class="loop-item-text">💸 โอนเงินไม่ทันรอบ → ติดเครดิต</div>
                    </div>
                    <div class="loop-item problem">
                        <div class="loop-num">4</div>
                        <div class="loop-item-text">⚠️ ของในสต็อกไม่พอ → ไม่สามารถผลิตได้</div>
                    </div>
                    <div class="loop-item problem">
                        <div class="loop-num">6</div>
                        <div class="loop-item-text">💵 เบิกเงินสดย่อยบ่อย → เคลียร์ช้า</div>
                    </div>
                    <div class="loop-item problem">
                        <div class="loop-num">8</div>
                        <div class="loop-item-text">🔁 ไม่มี PR/PO → GR ไม่ได้ → กลับสู่ข้อ 1</div>
                    </div>
                </div>
            </div>
            <div style="text-align:center;margin-top:32px;padding:20px;background:#fff3f3;border-radius:12px;border:2px solid #ffd0d0">
                <p style="color:var(--red);font-family:'Prompt',sans-serif;font-weight:700;font-size:1.1rem">
                    💡 ProcureThai ตัดลูปนี้ออกด้วยการ sync เอกสารแบบ Real-time
                </p>
            </div>
        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section class="section" id="how-it-works">
    <div class="section-inner">
        <div style="text-align:center">
            <div class="section-label">วิธีการทำงาน</div>
            <h2 class="section-title">จัดการจัดซื้อครบวงจร<br>ใน 4 ขั้นตอน</h2>
        </div>
        <div class="steps-grid">
            <div class="step">
                <div style="position:relative;display:inline-block">
                    <div class="step-circle">📋</div>
                    <div class="step-num">1</div>
                </div>
                <h4>สร้างใบขอราคา (RQ)</h4>
                <p>ระบุสินค้าที่ต้องการ เลือก Vendor และส่งขอราคาได้ทันที ระบบคำนวณ Lead Time ให้อัตโนมัติ</p>
            </div>
            <div class="step">
                <div style="position:relative;display:inline-block">
                    <div class="step-circle">✅</div>
                    <div class="step-num">2</div>
                </div>
                <h4>อนุมัติ PR/PO</h4>
                <p>Workflow การอนุมัติหลายขั้น ผู้จัดการเห็นและอนุมัติได้จากมือถือ ไม่ต้องรอเข้าออฟฟิศ</p>
            </div>
            <div class="step">
                <div style="position:relative;display:inline-block">
                    <div class="step-circle">📦</div>
                    <div class="step-num">3</div>
                </div>
                <h4>รับสินค้า (GR)</h4>
                <p>บันทึกการรับสินค้าเชื่อมกับ PO อัตโนมัติ แจ้งบัญชีได้ทันที ไม่ต้องส่งเอกสารกระดาษ</p>
            </div>
            <div class="step">
                <div style="position:relative;display:inline-block">
                    <div class="step-circle">🧾</div>
                    <div class="step-num">4</div>
                </div>
                <h4>Invoice & QR จัดส่ง</h4>
                <p>ออก Invoice พร้อม QR Code สำหรับการจัดส่งได้ในคลิกเดียว ส่งออก PDF ส่งให้ Supplier ได้เลย</p>
            </div>
        </div>
    </div>
</section>

<!-- FEATURES -->
<section class="section features-bg">
    <div class="section-inner">
        <div style="text-align:center">
            <div class="section-label" style="color:var(--accent)">ฟีเจอร์ทั้งหมด</div>
            <h2 class="section-title" style="color:white">ทุกอย่างที่ต้องการ<br>อยู่ในที่เดียว</h2>
            <p class="section-sub" style="color:rgba(255,255,255,0.6);margin:0 auto">
                แพลน Demo ใช้ได้ทุก module ยกเว้นการนำเข้า Excel และ SAP Integration
            </p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">👥</div>
                <h4>จัดการ Vendor</h4>
                <p>เก็บข้อมูล Supplier ครบถ้วน ทั้งเครดิต ผู้ติดต่อ และประวัติการสั่งซื้อ</p>
                <span class="feature-tag">✅ Demo</span>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📋</div>
                <h4>ใบขอราคา (RQ)</h4>
                <p>สร้าง RQ พร้อมคำนวณ Lead Time อัตโนมัติ แยกตามประเภท RM/PK/MRO/SVC</p>
                <span class="feature-tag">✅ Demo</span>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🛒</div>
                <h4>ใบสั่งซื้อ (PO)</h4>
                <p>ออก PO เชื่อมกับ RQ ติดตามสถานะจาก Confirm จนถึง Paid ครบวงจร</p>
                <span class="feature-tag">✅ Demo</span>
            </div>
            <div class="feature-card">
                <div class="feature-icon">✅</div>
                <h4>Approval Workflow</h4>
                <p>กำหนดผู้อนุมัติหลายระดับ แจ้งเตือนผ่าน Email อัตโนมัติเมื่อมีเอกสารรอ</p>
                <span class="feature-tag">✅ Demo</span>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🧾</div>
                <h4>Invoice & QR จัดส่ง</h4>
                <p>ออก Invoice พร้อม QR Code ส่งออก PDF ให้ Supplier ได้ทันที</p>
                <span class="feature-tag">✅ Demo</span>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📊</div>
                <h4>Dashboard Real-time</h4>
                <p>ภาพรวมสถานะทั้งหมด แจ้งเตือนเมื่อเกินกำหนด และ payment overdue</p>
                <span class="feature-tag">✅ Demo</span>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📤</div>
                <h4>นำเข้าข้อมูล Excel</h4>
                <p>Import ข้อมูล RQ จำนวนมากผ่าน Excel ในครั้งเดียว ประหยัดเวลามาก</p>
                <span class="feature-tag locked">🔒 Pro เท่านั้น</span>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🏭</div>
                <h4>SAP B1 Integration</h4>
                <p>เชื่อมต่อกับ SAP Business One ผ่าน API ดึงข้อมูล PO/GR แบบ Real-time</p>
                <span class="feature-tag locked">🔒 Pro เท่านั้น</span>
            </div>
            <div class="feature-card">
                <div class="feature-icon">👤</div>
                <h4>Multi-user & Roles</h4>
                <p>กำหนดสิทธิ์ผู้ใช้แต่ละคน แยก Admin, Staff, Viewer ตาม workflow</p>
                <span class="feature-tag locked">🔒 Pro เท่านั้น</span>
            </div>
        </div>
    </div>
</section>

<!-- PRICING -->
<section class="section" id="pricing">
    <div class="section-inner">
        <div style="text-align:center">
            <div class="section-label">ราคา</div>
            <h2 class="section-title">เลือกแพลนที่เหมาะกับคุณ</h2>
            <p class="section-sub" style="margin:0 auto">ไม่มีสัญญาผูกมัด ยกเลิกได้ทุกเมื่อ</p>
        </div>
        <div class="plan-grid">
            <div class="plan-card">
                <div class="plan-name">🎯 Demo</div>
                <p style="color:var(--muted);font-size:0.85rem">ทดลองใช้ฟรี</p>
                <div class="plan-price-block">
                    <div class="plan-current">ฟรี <span>/ 30 วัน</span></div>
                </div>
                <ul class="plan-features">
                    <li><span class="check">✅</span> ทุก module ใช้งานได้</li>
                    <li><span class="check">✅</span> Vendor สูงสุด 10 ราย</li>
                    <li><span class="check">✅</span> RQ สูงสุด 50 ใบ/เดือน</li>
                    <li><span class="check">✅</span> 1 User</li>
                    <li><span class="cross">❌</span> นำเข้า Excel</li>
                    <li><span class="cross">❌</span> SAP Integration</li>
                </ul>
                <a href="/register" class="btn-plan btn-plan-outline">เริ่มทดลองใช้</a>
            </div>
            <div class="plan-card featured">
                <div class="plan-name">🚀 Pro</div>
                <p style="color:var(--muted);font-size:0.85rem">สำหรับธุรกิจที่เติบโต</p>
                <div class="plan-price-block">
                    <div class="plan-original">฿30,000/เดือน</div>
                    <div class="plan-current" style="color:var(--accent)">฿15,000 <span>/ เดือน</span></div>
                    <div class="plan-save">🔥 ประหยัด ฿15,000 ลด 50%</div>
                </div>
                <ul class="plan-features">
                    <li><span class="check">✅</span> ทุกอย่างใน Demo</li>
                    <li><span class="check">✅</span> Vendor ไม่จำกัด</li>
                    <li><span class="check">✅</span> RQ ไม่จำกัด</li>
                    <li><span class="check">✅</span> Multi-user & Roles</li>
                    <li><span class="check">✅</span> นำเข้า Excel ได้</li>
                    <li><span class="check">✅</span> SAP B1 Integration</li>
                </ul>
                <a href="/register" class="btn-plan btn-plan-primary">รับโปรโมชั่น ลด 50%</a>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <div class="section-inner">
        <h2>พร้อมปิดลูปนรกการจัดซื้อแล้วหรือยัง?</h2>
        <p>เริ่มต้นได้เลยวันนี้ ทดลองใช้ฟรี 30 วัน ไม่ต้องใช้บัตรเครดิต</p>
        <a href="/register" class="btn-cta">🚀 สมัครใช้งานฟรีเลย</a>
    </div>
</section>

<footer>
    <p>ProcureThai © 2026 — ระบบบริหารจัดซื้อออนไลน์สำหรับธุรกิจไทย | procurethai.uk</p>
</footer>

<script>
// Smooth scroll
document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
        e.preventDefault();
        document.querySelector(a.getAttribute('href'))?.scrollIntoView({ behavior: 'smooth' });
    });
});

// Animate on scroll
const observer = new IntersectionObserver(entries => {
    entries.forEach(e => {
        if (e.isIntersecting) {
            e.target.style.opacity = '1';
            e.target.style.transform = 'translateY(0)';
        }
    });
}, { threshold: 0.1 });

document.querySelectorAll('.feature-card, .step, .loop-item, .plan-card').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(20px)';
    el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    observer.observe(el);
});
</script>
</body>
</html>
