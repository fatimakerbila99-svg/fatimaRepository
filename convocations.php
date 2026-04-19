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
    <title>الاستدعاءات</title>
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

        /* CONVOCATION */
        .convocation {
            page-break-after: always;
            padding: 25px;
            border: 1px solid #ddd;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        .conv-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #2e7d32;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .conv-header img { width: 70px; height: 70px; object-fit: contain; }
        .conv-header .titre { text-align: center; flex: 1; }
        .conv-header .titre h3 { color: #2e7d32; font-size: 14px; margin-bottom: 3px; }
        .conv-header .titre p { font-size: 12px; color: #555; }
        .conv-title {
            text-align: center;
            font-size: 17px;
            font-weight: bold;
            color: #1a5276;
            margin: 12px 0 5px;
            text-decoration: underline;
        }
        .conv-subtitle { text-align: center; font-size: 13px; color: #555; margin-bottom: 15px; }
        .conv-info { margin-bottom: 15px; }
        .conv-info p {
            font-size: 13px;
            padding: 5px 0;
            border-bottom: 1px dashed #eee;
        }
        .conv-info strong { color: #2e7d32; margin-left: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
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
        .conv-footer {
            margin-top: 20px;
            text-align: center;
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
            .convocation { border: none; }
        }
    </style>
</head>
<body>
<div class="header">
    <h1>النيابة الإقليمية - نظام تدبير الامتحانات</h1>
    <a href="dashboard.php" class="back">رجوع</a>
</div>

<div class="container">
    <h2> الاستدعاءات</h2>

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
        <button class="btn-print" onclick="window.print()">🖨️ طباعة جميع الاستدعاءات</button>

        <?php foreach($candidats as $c): ?>
        <div class="convocation">
            <div class="conv-header">
                <img src="logo.png" alt="logo">
                <div class="titre">
                    <h3>المملكة المغربية - وزارة التربية الوطنية</h3>
                    <p>النيابة الإقليمية - ورزازات</p>
                    <p><?= htmlspecialchars($c['centre_exam']) ?></p>
                </div>
                <img src="logo.png" alt="logo">
            </div>
            <div class="conv-title">استدعاء للامتحانات</div>
            <div class="conv-subtitle"><?= htmlspecialchars($c['niveau']) ?></div>
            <div class="conv-info">
                <p><strong>الاسم الكامل :</strong> <?= htmlspecialchars($c['nom_prenom']) ?></p>
                <p><strong>رقم التعريف الوطني :</strong> <?= htmlspecialchars($c['cd_elev']) ?></p>
                <p><strong>رقم الامتحان :</strong> <?= htmlspecialchars($c['num_exam']) ?></p>
                <p><strong>المؤسسة الأصلية :</strong> <?= htmlspecialchars($c['etab_orig']) ?></p>
                <p><strong>القسم :</strong> <?= htmlspecialchars($c['classe']) ?></p>
            </div>
            <table>
                <tr>
                    <th>المستوى</th>
                    <th>القاعة</th>
                    <th>مركز الامتحان</th>
                </tr>
                <tr>
                    <td><?= htmlspecialchars($c['niveau']) ?></td>
                    <td><?= htmlspecialchars($c['salle_exam']) ?></td>
                    <td><?= htmlspecialchars($c['centre_exam']) ?></td>
                </tr>
            </table>
            <div class="conv-footer">
                <p>حرر بورزازات، بتاريخ : <?= date('d/m/Y') ?></p>
                <p style="margin-top:5px; font-style:italic; font-size:11px;">
                    وثيقة رسمية صادرة عن النيابة الإقليمية لوزارة التربية الوطنية - ورزازات
                </p>
            </div>
        </div>
        <?php endforeach; ?>

    <?php elseif($etab_id == 0): ?>
        <div class="empty"> ارجع للوحة التحكم واختر مؤسسة أولاً</div>
    <?php else: ?>
        <div class="empty"> لا توجد بيانات. يرجى شحن قاعدة البيانات أولاً.</div>
    <?php endif; ?>
</div>
</body>
</html>
