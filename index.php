<?php
session_start();
require_once 'db.php';

if(isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $code = trim($_POST['code']);
    $password = trim($_POST['password']);

    // Chercher par code établissement
    $etab = supabase_request('etablissements?code_etab=eq.' . urlencode($code));
    
    if (!empty($etab)) {
        $users = supabase_request('utilisateurs?etablissement_id=eq.' . $etab[0]['id']);
        if (!empty($users)) {
            foreach($users as $u) {
                if(trim($u['mot_de_passe']) === trim($password)) {
                    $_SESSION['user'] = $u;
                    $_SESSION['etablissement'] = $etab[0];
                    $_SESSION['role'] = 'directeur';
                    if($u['premier_connexion'] == true) {
                        header('Location: changer_mdp.php');
                    } else {
                        header('Location: dashboard.php');
                    }
                    exit;
                }
            }
        }
    }

    // Chercher admin par email
    $admins = supabase_request('utilisateurs?email=eq.' . urlencode($code));
    if (!empty($admins)) {
        foreach($admins as $a) {
            if(trim($a['mot_de_passe']) === trim($password)) {
                $_SESSION['user'] = $a;
                $_SESSION['role'] = 'admin';
                if($a['premier_connexion'] == true) {
                    header('Location: changer_mdp.php');
                } else {
                    header('Location: dashboard.php');
                }
                exit;
            }
        }
    }

    $erreur = 'الكود أو كلمة المرور غير صحيحة !';
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>النيابة الإقليمية - دخول</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #e8f5e9, #fff8e1, #e8f5e9);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .logo-container { margin-bottom: 20px; text-align: center; }
        .logo-container img {
            width: 160px;
            height: 160px;
            object-fit: contain;
            border-radius: 50%;
            background: white;
            padding: 12px;
            box-shadow: 0 6px 25px rgba(0,0,0,0.15);
            border: 3px solid #f39c12;
        }
        .login-box {
            background: white;
            padding: 35px 40px;
            border-radius: 15px;
            width: 380px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            border-top: 4px solid #2e7d32;
            border-bottom: 4px solid #f39c12;
        }
        h2 { color: #2e7d32; font-size: 16px; margin-bottom: 5px; line-height: 1.5; }
        h3 { color: #777; font-size: 13px; margin-bottom: 25px; font-weight: normal; }
        input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            text-align: right;
            margin-bottom: 10px;
            box-sizing: border-box;
        }
        input:focus { border-color: #2e7d32; outline: none; }
        button {
            background: #2e7d32;
            color: white;
            padding: 12px;
            width: 100%;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            margin-top: 5px;
            transition: background 0.2s;
        }
        button:hover { background: #f39c12; }
        .erreur {
            background: #ffebee;
            color: #c0392b;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 13px;
        }
        .info {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 8px 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 12px;
        }
        .forgot {
            display: block;
            margin-top: 12px;
            color: #2e7d32;
            text-decoration: none;
            font-size: 13px;
        }
        .forgot:hover { color: #f39c12; }
    </style>
</head>
<body>
    <div class="logo-container">
        <img src="logo.png" alt="وزارة التربية الوطنية">
    </div>
    <div class="login-box">
        <h2>النيابة الإقليمية لوزارة التربية الوطنية</h2>
        <h3>نظام تدبير الامتحانات - ورزازات</h3>
        <div class="info">أدخل رمز المؤسسة أو البريد الإلكتروني + كلمة المرور</div>
        <?php if($erreur): ?>
            <div class="erreur"><?= $erreur ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="code" placeholder="رمز المؤسسة أو البريد الإلكتروني" required>
            <input type="password" name="password" placeholder="كلمة المرور" required>
            <button type="submit">دخول</button>
        </form>
        <a href="forgot_password.php" class="forgot"> نسيت كلمة المرور ؟</a>
    </div>
</body>
</html>
