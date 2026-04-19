<?php
session_start();
require_once 'db.php';
if(!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
$user = $_SESSION['user'];
$role = $_SESSION['role'];
$etablissement = isset($_SESSION['etablissement']) ? $_SESSION['etablissement'] : null;
$communes = supabase_request('communes?order=nom_fr');
$commune_etab = null;
if($role == 'directeur' && $etablissement) {
    $res = supabase_request('communes?id=eq.' . $etablissement['commune_id']);
    $commune_etab = !empty($res) ? $res[0] : null;
}
$etab_param = '';
if($role == 'directeur' && $etablissement) {
    $etab_param = '?etab_id=' . $etablissement['id'];
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة التحكم - النيابة الإقليمية</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #e8f5e9, #fff8e1, #e8f5e9);
            padding-bottom: 60px;
        }
        .header {
            background: linear-gradient(135deg, #2e7d32, #388e3c);
            color: white;
            padding: 12px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        .header-left { display: flex; align-items: center; gap: 15px; }
        .header-left img {
            width: 50px; height: 50px;
            object-fit: contain;
            border-radius: 50%;
            border: 2px solid #f39c12;
            background: white;
            padding: 3px;
        }
        .header-left h1 { font-size: 13px; line-height: 1.7; color: white; }
        .header-left h1 span { color: #f39c12; font-size: 15px; font-weight: bold; }
        .logout {
            background: #f39c12;
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-size: 13px;
            font-weight: bold;
        }
        .logout:hover { background: #e67e22; }
        .info-bar {
            background: #2e7d32;
            color: white;
            padding: 10px 30px;
            display: flex;
            gap: 20px;
            align-items: center;
            border-bottom: 3px solid #f39c12;
            flex-wrap: wrap;
        }
        .info-item { display: flex; align-items: center; gap: 8px; font-size: 13px; }
        .info-item label { font-weight: bold; color: #f39c12; }
        .info-item span {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(251,191,36,0.5);
            padding: 4px 14px;
            border-radius: 20px;
            color: white;
        }
        .info-item select {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 1px solid rgba(251,191,36,0.5);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
        }
        .info-item select option { color: #2e7d32; background: white; }
        .separator { color: #f39c12; font-size: 18px; }
        .welcome {
            background: white;
            margin: 25px auto;
            padding: 25px 35px;
            border-radius: 16px;
            text-align: center;
            max-width: 520px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            border-top: 4px solid #2e7d32;
            border-bottom: 4px solid #f39c12;
            animation: fadeUp 0.5s ease;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .welcome h2 { font-size: 19px; color: #2e7d32; margin-bottom: 8px; }
        .welcome .date { font-size: 13px; color: #94a3b8; }
        .welcome .badge {
            display: inline-block;
            background: linear-gradient(135deg, #2e7d32, #f39c12);
            color: white;
            padding: 4px 15px;
            border-radius: 20px;
            font-size: 11px;
            margin-top: 8px;
        }
        .cards-title {
            text-align: center;
            color: #2e7d32;
            font-size: 16px;
            margin: 5px 0 20px;
            font-weight: bold;
        }
        .cards {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin: 0 25px 30px;
            justify-content: center;
        }
        .card {
            background: white;
            padding: 30px 20px;
            border-radius: 16px;
            text-align: center;
            width: 165px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: block;
            border-top: 4px solid #2e7d32;
            animation: cardUp 0.5s ease forwards;
            opacity: 0;
        }
        .card:nth-child(1) { animation-delay: 0.1s; }
        .card:nth-child(2) { animation-delay: 0.2s; }
        .card:nth-child(3) { animation-delay: 0.3s; }
        .card:nth-child(4) { animation-delay: 0.4s; }
        .card:nth-child(5) { animation-delay: 0.5s; }
        .card:nth-child(6) { animation-delay: 0.6s; }
        @keyframes cardUp {
            from { opacity: 0; transform: translateY(25px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
            border-top-color: #f39c12;
        }
        .card .icon { font-size: 38px; margin-bottom: 12px; display: block; transition: transform 0.3s; }
        .card:hover .icon { transform: scale(1.15); }
        .card h3 { color: #2e7d32; font-size: 13px; line-height: 1.5; transition: color 0.3s; }
        .card:hover h3 { color: #f39c12; }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            background: #2e7d32;
            color: white;
            text-align: center;
            padding: 12px;
            font-size: 12px;
            border-top: 3px solid #f39c12;
        }
        .footer span { color: #f39c12; font-weight: bold; }
    </style>
</head>
<body>

<div class="header">
    <div class="header-left">
        <img src="logo.png" alt="logo">
        <h1>
            <span>النيابة الإقليمية - ورزازات</span><br>
            وزارة التربية الوطنية<br>
            نظام تدبير الامتحانات
        </h1>
    </div>
    <a href="index.php?logout=1" class="logout">خروج ⬅</a>
</div>

<div class="info-bar">
    <div class="info-item">
        <label>الإقليم :</label>
        <span>ورزازات</span>
    </div>
    <span class="separator">|</span>
    <div class="info-item">
        <label>الجماعة :</label>
        <?php if($role == 'admin'): ?>
            <select id="select-commune" onchange="chargerEtablissements(this.value)">
                <option value="">-- اختر --</option>
                <?php foreach($communes as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= $c['nom_fr'] ?></option>
                <?php endforeach; ?>
            </select>
        <?php else: ?>
            <span><?= $commune_etab ? $commune_etab['nom_fr'] : '-' ?></span>
        <?php endif; ?>
    </div>
    <span class="separator">|</span>
    <div class="info-item">
        <label>المؤسسة :</label>
        <?php if($role == 'admin'): ?>
            <select id="select-etab" onchange="updateCardLinks()">
                <option value="">-- اختر --</option>
            </select>
        <?php else: ?>
            <span><?= $etablissement ? $etablissement['nom_fr'] : '-' ?></span>
        <?php endif; ?>
    </div>
</div>

<div class="welcome">
    <h2>مرحباً بك في نظام تدبير الامتحانات</h2>
    <p class="date"> <?= date('l, d F Y') ?></p>
    <span class="badge">
        <?= $role == 'admin' ? ' مدير النيابة' : 'مدير المؤسسة' ?>
    </span>
</div>

<p class="cards-title"> الوحدات الرئيسية</p>
<div class="cards">
    <?php if($role == 'admin'): ?>
    <a href="upload.php" class="card" style="border-top-color:#e74c3c;">
        <span class="icon">📤</span>
        <h3 style="color:#e74c3c;">شحن قاعدة البيانات</h3>
    </a>
    <?php endif; ?>
    <a href="etiquettes.php<?= $etab_param ?>" class="card">
        <span class="icon"></span>
        <h3>ملصقات الطاولات</h3>
    </a>
    <a href="convocations.php<?= $etab_param ?>" class="card">
        <span class="icon"></span>
        <h3>الاستدعاءات</h3>
    </a>
    <a href="surveillants.php<?= $etab_param ?>" class="card">
        <span class="icon"></span>
        <h3>لوائح القاعات</h3>
    </a>
    <a href="pv.php<?= $etab_param ?>" class="card">
        <span class="icon"></span>
        <h3>محاضر الحضور</h3>
    </a>
    <a href="resultats.php<?= $etab_param ?>" class="card">
        <span class="icon"></span>
        <h3>استثمار النتائج</h3>
    </a>
</div>

<div class="footer">
    © <span><?= date('Y') ?></span> - النيابة الإقليمية لوزارة التربية الوطنية - <span>ورزازات</span>
</div>

<script>
function chargerEtablissements(commune_id) {
    if(!commune_id) return;
    fetch('get_etablissements.php?commune_id=' + commune_id)
        .then(r => r.json())
        .then(data => {
            let select = document.getElementById('select-etab');
            select.innerHTML = '<option value="">-- اختر --</option>';
            data.forEach(e => {
                select.innerHTML += `<option value="${e.id}">${e.nom_fr}</option>`;
            });
        });
}

function updateCardLinks() {
    const etabId = document.getElementById('select-etab').value;
    document.querySelectorAll('.card').forEach(card => {
        const href = card.getAttribute('href');
        if(href && !href.includes('upload.php')) {
            const base = href.split('?')[0];
            card.setAttribute('href', etabId ? base + '?etab_id=' + etabId : base);
        }
    });
}
</script>
</body>
</html>
