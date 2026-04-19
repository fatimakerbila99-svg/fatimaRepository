<?php
session_start();
require_once 'db.php';
if(!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>شحن قاعدة البيانات</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #e8f5e9, #fff8e1, #e8f5e9);
            min-height: 100vh;
        }
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
            max-width: 650px;
            margin: 50px auto;
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            text-align: center;
            border-top: 4px solid #2e7d32;
            border-bottom: 4px solid #f39c12;
        }
        h2 { color: #2e7d32; font-size: 20px; margin-bottom: 10px; }
        .subtitle { color: #777; font-size: 13px; margin-bottom: 25px; }
        .upload-zone {
            border: 3px dashed #2e7d32;
            border-radius: 12px;
            padding: 40px;
            background: #f9fbe7;
            margin-bottom: 20px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .upload-zone:hover { background: #e8f5e9; }
        .upload-zone p { color: #2e7d32; margin-bottom: 15px; font-size: 15px; }
        input[type="file"] { display: none; }
        .btn-upload {
            background: #2e7d32;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            display: inline-block;
            transition: background 0.2s;
            border: none;
        }
        .btn-upload:hover { background: #f39c12; }
        .progress-container {
            display: none;
            margin-top: 20px;
        }
        .progress-bar-bg {
            background: #e0e0e0;
            border-radius: 10px;
            height: 20px;
            overflow: hidden;
            margin-bottom: 10px;
        }
