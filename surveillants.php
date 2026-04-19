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

$salles = [];
$etab_info = null;
if($etab_id > 0) {
    $data = supabase_request('candidats?etablissement_id=eq.' . $etab_id . '&order=salle_exam');
    $etab_res = supabase_request('etablissements?id=eq.' . $etab_id);
    $etab_info = !empty($etab_res) ? $etab_res[0] : null;
    foreach($data as $row) {
        $salle = $row['salle_exam'];
        if(!isset($salles[$salle])) {
            $salles[$salle] = [
                'centre' => $row['centre_exam'],
                'niveau' => $row['niveau'],
                'candidats' => []
            ];
        }
        $salles[$salle]['candidats'][] = $row;
    }
}
$etabs = supabase_request('etablissements?order=nom_fr');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوائح القاعات</title>
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
            max-width: 900px;
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
        .filter-bar .etab-name { color: #2e7d32; font-weight: bold; font-size: 14px; }
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
        .stats {
            background: #e8f5e9;
            padding: 8px 15px;
            border-radius: 8px;
            margin-bottom: 12px;
            color: #2e7d32;
            font-size: 13px;
            font-weight: bold;
        }
        .salle-block {
            page-break-after: always;
            padding: 20px;
            border: 1px solid #ddd;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        .salle-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #2e7d32;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .salle-header img { width: 65px; height: 65px; object-fit: contain; }
        .salle-header .titre { text-align: center; flex: 1; }
        .salle-header .titre h3 { color: #2e7d32; font-size: 14px; margin-bottom: 3px; }
        .salle-header .titre p { font-size: 12px; color: #555; }
        .salle-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            color: #1a5276;
            margin: 10px 0;
        }
        .salle-info {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 15px;
            padding: 8px 15px;
            background: #f0f8f0;
            border-radius: 8px;
            border-right: 4px solid #2e7d32;
        }
        .salle-info p { font-size: 13px; }
        .salle-info strong { color: #2e7d32; }
        table { width: 100%; border-collapse: collapse; }
        table th {
            background: #2e7d32;
            color: white;
            padding: 8px;
            font-size: 13px;
            text-align: center;
        }
        table td {
            border: 1px solid #ddd;
            padding: 8px;
            font-size: 12px;
            text-align: center;
        }
        table tr:nth-child(even) { background: #f9f9f9; }
        .salle-footer {
            margin-top: 15px;
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #555;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .empty { text-align: center; color: #999; padding: 40px; font-size: 14px; }
        @media print {
            .header, h2, .filter-bar, .btn-print, .stats { display: none !important; }
            body { background: white; }
            .container { box-shadow: none; padding: 0; margin: 0; max-width: 100%; }
            .salle-block { border: none; }
        }
    </style>
</head>
<body>
<div class="header">
    <h1>النيابة الإقليمية - نظام تدبير الامتحانات</h1>
    <a href="dashboard.php" class="back">رجوع</a>
</div>
<div class="container">
    <h2> لوائح القاعات</h2>
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

    <?php if(!empty($salles)): ?>
        <div class="stats"> عدد القاعات : <?= count($salles) ?></div>
        <button class="btn-print" onclick="window.print()">🖨️ طباعة لوائح القاعات</button>
        <?php foreach($salles as $salle_nom => $salle): ?>
        <div class="salle-block">
            <div class="salle-header">
                <img src="logo.png" alt="logo">
                <div class="titre">
                    <h3>المملكة المغربية - وزارة التربية الوطنية</h3>
                    <p>النيابة الإقليمية - ورزازات</p>
                    <p><?= htmlspecialchars($salle['centre']) ?></p>
                </div>
                <img src="logo.png" alt="logo">
            </div>
            <div class="salle-title">لائحة المترشحين</div>
            <div class="salle-info">
                <p><strong>القاعة :</strong> <?= htmlspecialchars($salle_nom) ?></p>
                <p><strong>المستوى :</strong> <?= htmlspecialchars($salle['niveau']) ?></p>
                <p><strong>عدد المترشحين :</strong> <?= count($salle['candidats']) ?></p>
                <p><strong>التاريخ :</strong> <?= date('d/m/Y') ?></p>
            </div>
            <table>
                <tr>
                    <th>الرقم</th>
                    <th>رقم الامتحان</th>
                    <th>الاسم الكامل</th>
                    <th>المؤسسة الأصلية</th>
                    <th>القسم</th>
                    <th>التوقيع</th>
                </tr>
                <?php foreach($salle['candidats'] as $i => $c): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($c['num_exam']) ?></td>
                    <td><?= htmlspecialchars($c['nom_prenom']) ?></td>
                    <td><?= htmlspecialchars($c['etab_orig']) ?></td>
                    <td><?= htmlspecialchars($c['classe']) ?></td>
                    <td style="width:80px;"></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <div class="salle-footer">
                <p>حرر بورزازات، بتاريخ : <?= date('d/m/Y') ?></p>
                <p>المراقب : ........................</p>
            </div>
        </div>
        <?php endforeach; ?>
    <?php elseif($etab_id == 0): ?>
        <div class="empty"> ارجع للوحة التحكم واختر مؤسسة أولاً</div>
    <?php else: ?>
        <div class="empty">⚠️ لا توجد بيانات. يرجى شحن قاعدة البيانات أولاً.</div>
    <?php endif; ?>
</div>
</body>
</html>
