<?php
session_start();
require_once 'db.php';

$message = '';
$erreur = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    
    // Chercher l'utilisateur
    $user = supabase_request('utilisateurs?email=eq.' . urlencode($email));
    
    if(!empty($user)) {
        // Générer token unique
        $token = bin2hex(random_bytes(32));
        $expire = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Sauvegarder token dans Supabase
        supabase_request('reset_tokens', 'POST', [
            'user_id'   => $user[0]['id'],
            'token'     => $token,
            'expire_at' => $expire,
            'used'      => false
        ]);
        
        // Lien de réinitialisation
        $link = "https://fatimarepository.great-site.net/reset_password.php?token=" . $token;
        
        // Envoyer email
        $to = $email;
        $subject = "إعادة تعيين كلمة المرور - النيابة الإقليمية ورزازات";
        $body = "
        <html>
        <body dir='rtl' style='font-family:Arial; color:#333;'>
            <div style='max-width:500px; margin:auto; padding:30px; border:1px solid #ddd; border-radius:10px;'>
                <h2 style='color:#0ea5e9;'>إعادة تعيين كلمة المرور</h2>
                <p>مرحباً،</p>
                <p>لقد طلبت إعادة تعيين كلمة المرور الخاصة بحسابك في نظام تدبير الامتحانات.</p>
                <p>اضغط على الزر أدناه لإعادة تعيين كلمة المرور :</p>
                <div style='text-align:center; margin:30px 0;'>
                    <a href='$link' style='background:#0ea5e9; color:white; padding:12px 30px; border-radius:8px; text-decoration:none; font-weight:bold;'>
                        إعادة تعيين كلمة المرور
                    </a>
                </div>
                <p style='color:#888; font-size:12px;'>هذا الرابط صالح لمدة ساعة واحدة فقط.</p>
                <p style='color:#888; font-size:12px;'>إذا لم تطلب هذا، تجاهل هذا البريد الإلكتروني.</p>
                <hr style='border:none; border-top:1px solid #eee; margin:20px 0;'>
                <p style='color:#888; font-size:11px; text-align:center;'>
                    النيابة الإقليمية لوزارة التربية الوطنية - ورزازات
                </p>
            </div>
        </body>
        </html>";
        
        // Configuration Gmail SMTP
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=utf-8\r\n";
        $headers .= "From: niyaba.ouarzazate@gmail.com\r\n";
        
        // Utiliser PHPMailer via stream
        $sent = sendGmail($to, $subject, $body);
        
        if($sent) {
            $message = 'تم إرسال رابط إعادة التعيين إلى بريدك الإلكتروني !';
        } else {
            $message = 'تم إنشاء رابط إعادة التعيين. تحقق من بريدك الإلكتروني.';
        }
    } else {
        $erreur = 'البريد الإلكتروني غير موجود في النظام !';
    }
}

function sendGmail($to, $subject, $body) {
    $gmail = 'fatima.kerbila.99@edu.uiz.ac.ma';
    $password = 'vodqtzdccawtgiob';
    
    $header = "From: $gmail\r\nReply-To: $gmail\r\nContent-Type: text/html; charset=UTF-8\r\n";
    
    ini_set('SMTP', 'smtp.gmail.com');
    ini_set('smtp_port', '587');
    
    // Méthode socket
    $socket = fsockopen('ssl://smtp.gmail.com', 465, $errno, $errstr, 30);
    if(!$socket) return false;
    
    fgets($socket, 515);
    fputs($socket, "EHLO localhost\r\n"); fgets($socket, 515);
    fputs($socket, "AUTH LOGIN\r\n"); fgets($socket, 515);
    fputs($socket, base64_encode($gmail) . "\r\n"); fgets($socket, 515);
    fputs($socket, base64_encode($password) . "\r\n"); fgets($socket, 515);
    fputs($socket, "MAIL FROM: <$gmail>\r\n"); fgets($socket, 515);
    fputs($socket, "RCPT TO: <$to>\r\n"); fgets($socket, 515);
    fputs($socket, "DATA\r\n"); fgets($socket, 515);
    fputs($socket, "Subject: $subject\r\n");
    fputs($socket, "From: $gmail\r\n");
    fputs($socket, "To: $to\r\n");
    fputs($socket, "MIME-Version: 1.0\r\n");
    fputs($socket, "Content-type: text/html; charset=utf-8\r\n");
    fputs($socket, "\r\n");
    fputs($socket, "$body\r\n");
    fputs($socket, ".\r\n"); fgets($socket, 515);
    fputs($socket, "QUIT\r\n");
    fclose($socket);
    return true;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>نسيت كلمة المرور</title>
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
        <h2> نسيت كلمة المرور ؟</h2>
        <p>أدخل بريدك الإلكتروني وسنرسل لك رابط إعادة التعيين</p>

        <?php if($message): ?>
            <div class="success"> <?= $message ?></div>
        <?php endif; ?>
        <?php if($erreur): ?>
            <div class="erreur"> <?= $erreur ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="email" name="email"
                   placeholder="البريد الإلكتروني"
                   required>
            <button type="submit">إرسال رابط إعادة التعيين</button>
        </form>
        <a href="index.php" class="back">← العودة إلى تسجيل الدخول</a>
    </div>
</body>
</html>
