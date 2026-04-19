<?php
session_start();
require_once 'db.php';
if(!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$erreur = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nouveau = trim($_POST['nouveau']);
    $confirm = trim($_POST['confirm']);
    $email = trim($_POST['email']);
    
    if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = 'يرجى إدخال بريد إلكتروني صحيح !';
    } elseif(strlen($nouveau) < 6) {
        $erreur = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل !';
    } elseif($nouveau !== $confirm) {
        $erreur = 'كلمتا المرور غير متطابقتين !';
    } else {
        $user_id = $_SESSION['user']['id'];
        supabase_request(
            'utilisateurs?id=eq.' . $user_id,
            'PATCH',
            [
                'mot_de_passe' => $nouveau,
                'email' => $email,
                'premier_connexion' => false
            ]
        );
        $_SESSION['user']['premier_connexion'] = false;
        $_SESSION['user']['email'] = $email;
        header('Location: dashboard.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تغيير كلمة المرور</title>
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
            width: 420px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(14,165,233,0.15);
            border-top: 4px solid #0ea5e9;
            border-bottom: 4px solid #fbbf24;
        }
        h2 { color: #0ea5e9; font-size: 18px; margin-bottom: 10px; }
        p { color: #777; font-size: 13px; margin-bottom: 20px; }
        .info {
            background: #fff8e1;
            color: #f59e0b;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
            border: 1px solid #fbbf24;
        }
        .field-label {
            text-align: right;
            font-size: 13px;
            color: #0ea5e9;
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }
        input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            text-align: right;
            margin-bottom: 15px;
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
        .erreur {
            background: #ffebee;
            color: #c0392b;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 13px;
        }
        .divider {
            border: none;
            border-top: 1px dashed #ddd;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="box">
        <h2> إعداد الحساب</h2>
        <p>مرحباً ! يرجى إكمال إعداد حسابك قبل المتابعة</p>

        <div class="info">
            ⚠️ هذه أول مرة تسجل فيها الدخول.<br>
            أدخل بريدك الإلكتروني وكلمة مرور جديدة.
        </div>

        <?php if($erreur): ?>
            <div class="erreur"> <?= $erreur ?></div>
        <?php endif; ?>

        <form method="POST">
            <label class="field-label">البريد الإلكتروني الشخصي :</label>
            <input type="email" name="email"
                   placeholder="exemple@gmail.com"
                   required>

            <hr class="divider">

            <label class="field-label">🔑 كلمة المرور الجديدة :</label>
            <input type="password" name="nouveau"
                   placeholder="6 أحرف على الأقل"
                   required>

            <label class="field-label">🔑 تأكيد كلمة المرور :</label>
            <input type="password" name="confirm"
                   placeholder="أعد كتابة كلمة المرور"
                   required>

            <button type="submit"> تأكيد وحفظ</button>
        </form>
    </div>
</body>
</html>
