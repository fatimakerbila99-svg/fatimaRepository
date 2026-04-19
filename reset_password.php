<?php
session_start();
require_once 'db.php';

$erreur = '';
$message = '';
$token_valid = false;
$token = isset($_GET['token']) ? $_GET['token'] : '';

// Vérifier le token
if($token) {
    $reset = supabase_request('reset_tokens?token=eq.' . urlencode($token) . '&used=eq.false');
    if(!empty($reset)) {
        $expire = strtotime($reset[0]['expire_at']);
        if($expire > time()) {
            $token_valid = true;
        } else {
            $erreur = 'انتهت صلاحية الرابط ! طلب رابطاً جديداً.';
        }
    } else {
        $erreur = 'الرابط غير صالح أو تم استخدامه مسبقاً !';
    }
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && $token_valid) {
    $nouveau = trim($_POST['nouveau']);
    $confirm = trim($_POST['confirm']);
    
    if(strlen($nouveau) < 6) {
        $erreur = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل !';
    } elseif($nouveau !== $confirm) {
        $erreur = 'كلمتا المرور غير متطابقتين !';
    } else {
        $user_id = $reset[0]['user_id'];
        
        // Mettre à jour mot de passe
        supabase_request(
            'utilisateurs?id=eq.' . $user_id,
            'PATCH',
            ['mot_de_passe' => $nouveau, 'premier_connexion' => false]
        );
        
        // Marquer token comme utilisé
        supabase_request(
            'reset_tokens?token=eq.' . urlencode($token),
            'PATCH',
            ['used' => true]
        );
        
        $message = 'تم تغيير كلمة المرور بنجاح ! يمكنك الآن تسجيل الدخول.';
        $token_valid = false;
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إعادة تعيين كلمة المرور</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(160deg, #e0f4ff, #ffffff, #fff8e1);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .box {
            background: white;
            padding: 40px;
            border-radius: 16px;
            width: 400px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(14,165,233,0.15);
            border-top: 4px solid #0ea5e9;
            border-bottom: 4px solid #fbbf24;
        }
        h2 { color: #0ea5e9; font-size: 18px; margin-bottom: 10px; }
        p { color: #777; font-size: 13px; margin-bottom: 20px; }
        input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            text-align: right;
            margin-bottom: 12px;
            box-sizing: border-box;
        }
        input:focus { border-color: #0ea5e9; outline: none; }
        button {
            background: #0ea5e9;
            color: white;
            padding: 12px;
            width: 100%;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            transition: background 0.2s;
        }
        button:hover { background: #fbbf24; }
        .success {
            background: #e0f4ff;
            color: #0ea5e9;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 13px;
        }
        .erreur {
            background: #ffebee;
            color: #c0392b;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 13px;
        }
        .back {
            display: block;
            margin-top: 15px;
            color: #0ea5e9;
            text-decoration: none;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="box">
        <h2> إعادة تعيين كلمة المرور</h2>

        <?php if($message): ?>
            <div class="success"> <?= $message ?></div>
            <a href="index.php" class="back">← تسجيل الدخول</a>
        <?php elseif($erreur && !$token_valid): ?>
            <div class="erreur"> <?= $erreur ?></div>
            <a href="forgot_password.php" class="back">← طلب رابط جديد</a>
        <?php elseif($token_valid): ?>
            <p>أدخل كلمة المرور الجديدة</p>
            <?php if($erreur): ?>
                <div class="erreur"> <?= $erreur ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <input type="password" name="nouveau"
                       placeholder="كلمة المرور الجديدة"
                       required>
                <input type="password" name="confirm"
                       placeholder="تأكيد كلمة المرور"
                       required>
                <button type="submit">حفظ كلمة المرور الجديدة</button>
            </form>
        <?php endif; ?>

        <a href="index.php" class="back">← العودة إلى تسجيل الدخول</a>
    </div>
</body>
</html>
