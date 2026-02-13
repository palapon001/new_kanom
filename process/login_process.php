<?php
session_start();
require_once '../config.php';
require_once '../function.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // 1. รับค่า identity (อาจเป็นเบอร์ หรือ อีเมล)
    $identity = trim($_POST['identity']); 
    $password = $_POST['password'] ?? '';
    
    // ตรวจสอบว่าเป็น Email หรือ Phone
    $is_email = filter_var($identity, FILTER_VALIDATE_EMAIL);
    
    if ($is_email) {
        // 📧 กรณี Login ด้วย Email
        $user = selectOne("SELECT * FROM users WHERE email = ?", [$identity]);
        
        if (!$user) {
            $_SESSION['error'] = "ไม่พบอีเมลนี้ในระบบ";
            header("Location: ../login.php");
            exit();
        }
        
        // อีเมลต้องเช็ค Password เสมอ
        if (empty($password) || md5($password) !== $user['password']) {
            $_SESSION['error'] = "รหัสผ่านไม่ถูกต้อง";
            header("Location: ../login.php");
            exit();
        }
        
    } else {
        // 📱 กรณี Login ด้วยเบอร์โทร
        $phone = preg_replace('/[^0-9]/', '', $identity); // คลีนเบอร์
        
        if (strlen($phone) !== 10) {
            $_SESSION['error'] = "รูปแบบเบอร์โทรศัพท์หรืออีเมลไม่ถูกต้อง";
            header("Location: ../login.php");
            exit();
        }

        $user = selectOne("SELECT * FROM users WHERE phone = ?", [$phone]);

        if (!$user) {
            $_SESSION['error'] = "ไม่พบเบอร์โทรศัพท์นี้ในระบบ";
            header("Location: ../login.php");
            exit();
        }

        // เช็ค Role ถ้าไม่ใช่ User ต้องตรวจรหัส
        if ($user['role'] !== 'user') {
            if (empty($password) || md5($password) !== $user['password']) {
                $_SESSION['error'] = "รหัสผ่านไม่ถูกต้อง";
                header("Location: ../login.php");
                exit();
            }
        }
    }

    // ✅ Login สำเร็จ (Common Logic)
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['user_name'] = ($user['role'] == 'shop') ? $user['shop_name'] : $user['fullname'];

    // Redirect logic...
    if ($user['role'] == 'admin') header("Location: ../admin/dashboard.php");
    elseif ($user['role'] == 'shop') header("Location: ../shop/dashboard.php");
    else header("Location: ../index.php");
    exit();

} else {
    header("Location: ../login.php");
    exit();
}