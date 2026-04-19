<?php
session_start();
require_once 'db.php';
if(!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
$role = $_SESSION['role'];
$etablissement = isset($_SESSION['etablissement']) ? $_SESSION['etablissement'] : null;
// Pour admin : jamais de valeur par défaut, il doit choisir
// Pour directeur : son établissement automatiquement
if($role == 'admin') {
    $etab_id = isset($_GET['etab_id']) ? intval($_GET['etab_id']) : 0;
} else {
    $etab_id = isset($_GET['etab_id']) ? intval($_GET['etab_id']) :
               ($etablissement ? $etablissement['id'] : 0);
}

$candidats = [];
$etab_info = null;
if($etab_id > 0) {
    $candidats = supabase_request('candidats?etablissement_id=eq.' . $etab_id . '&order=num_exam');
    $etab_res = supabase_request('etablissements?id=eq.' . $etab_id);
    $etab_info = !empty($etab_res) ? $etab_res[0] : null;
}
$etabs = supabase_request('etablissements?order=nom_fr');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>ملصقات الطاولات</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .header {
            background: white;
            padding: 12px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid #f39c12;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        .header h1 { color: #2e7d32; font-size: 14px; }
        .back {
            background: #2e7d32;
            color: white;
            padding: 7px 15px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
        }
        .container {
            max-width: 1100px;
            margin: 20px auto;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.08);
        }
        h2 { color: #2e7d32; margin-bottom: 15px; font-size: 18px; text-align: center; }
        .filter-bar {
            background: #f9fbe7;
            padding: 12px 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            border: 1px solid #c8e6c9;
            display: flex;
            align-items: center;
            gap: 15px;
            justify-content: space-between;
        }
        .filter-bar .etab-name {
            color: #2e7d32;
            font-weight: bold;
            font-size: 14px;
        }
        .filter-bar select {
            padding: 6px 12px;
            border: 2px solid #2e7d32;
            border-radius: 8px;
            font-size: 13px;
            flex: 1;
        }
        .filter-bar button {
            background: #2e7d32;
            color: white;
            padding: 7px 18px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
        }
        .btn-print {
            background: #f39c12;
            color: white;
            padding: 10px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            margin-bottom: 15px;
            font-weight: bold;
            display: block;
            width: 100%;
        }
        .etiquettes-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: flex-start;
        }
        .etiquette {
            width: 200px;
            height: 130px;
            border: 2px solid #000;
            border-radius: 6px;
            padding: 8px;
            text-align: center;
            background: white;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            page-break-inside: avoid;
        }
        .etiquette .centre {
            font-size: 9px;
            color: #000;
            border-bottom: 1px solid #ccc;
            padding-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .etiquette .num {
            font-size: 30px;
            font-weight: bold;
            color: #000;
            line-height: 1;
        }
        .etiquette .nom {
            font-size: 11px;
            font-weight: bold;
            color: #000;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .etiquette .salle {
            font-size: 11px;
            color: #000;
            font-weight: bold;
            background: #f0f0f0;
            padding: 2px 5px;
            border-radius: 4px;
        }
        .etiquette .niveau {
            font-size: 9px;
            color: #555;
        }
        .empty {
            text-align: center;
            color: #999;
            padding: 40px;
            font-size: 14px;
        }
        .stats {
            background: #e8f5e9;
            padding: 8px 15px;
            border-radius: 8px;
            margin-bottom: 12px;
            color: #2e7d32;
            font-size: 13px;
            font-weight: bold;
        }
        @media print {
            .header, h2, .filter-bar, .btn-print, .stats { display: none !important; }
            body { background: white; }
            .container { box-shadow: none; padding: 0; margin: 0; max-width: 100%; }
            .etiquette { border: 2px solid #000; }
        }
    </style>
</head>
<body>
<div class="header">
    <h1>النيابة الإقليمية - نظام تدبير الامتحانات</h1>
    <a href="dashboard.php" class="back">رجوع</a>
</div>

<div class="container">
    <h2> ملصقات الطاولات</h2>

    <div class="filter-bar">
        <span class="etab-name">
             <?= $etab_info ? htmlspecialchars($etab_info['nom_fr']) : 'لم يتم اختيار مؤسسة' ?>
        </span>
        <?php if($role == 'admin'): ?>
        <form method="GET" style="display:flex; gap:10px; align-items:center; flex:1;">
            <select name="etab_id">
                <option value="">-- غير المؤسسة --</option>
                <?php foreach($etabs as $e): ?>
                <option value="<?= $e['id'] ?>" <?= $etab_id == $e['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($e['nom_fr']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">تغيير</button>
        </form>
        <?php endif; ?>
    </div>

    <?php if(!empty($candidats)): ?>
        <div class="stats"> عدد المترشحين : <?= count($candidats) ?></div>
        <button class="btn-print" onclick="window.print()">🖨️ طباعة الملصقات</button>
        <div class="etiquettes-container">
            <?php foreach($candidats as $c): ?>
            <div class="etiquette">
                <div class="centre"><?= htmlspecialchars($c['centre_exam']) ?></div>
                <div class="num"><?= htmlspecialchars($c['num_exam']) ?></div>
                <div class="nom"><?= htmlspecialchars($c['nom_prenom']) ?></div>
                <div class="salle"> <?= htmlspecialchars($c['salle_exam']) ?></div>
                <div class="niveau"><?= htmlspecialchars($c['niveau']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php elseif($etab_id == 0): ?>
        <div class="empty"> ارجع للوحة التحكم واختر مؤسسة أولاً</div>
    <?php else: ?>
        <div class="empty"> لا توجد بيانات لهذه المؤسسة. يرجى شحن قاعدة البيانات أولاً.</div>
    <?php endif; ?>
</div>
</body>
</html>
