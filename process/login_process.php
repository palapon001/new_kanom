<?php
session_start();
require_once '../config.php';
require_once '../function.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // 1. คลีนเบอร์โทรศัพท์: ลบทุกอย่างที่ไม่ใช่ตัวเลข (เช่น -, ช่องว่าง, วงเล็บ)
    $phone = preg_replace('/[^0-9]/', '', $_POST['phone']);
    $password = $_POST['password'] ?? '';

    // 2. Validation เบื้องต้น
    if (empty($phone)) {
        $_SESSION['error'] = "กรุณากรอกเบอร์โทรศัพท์";
        header("Location: ../login.php");
        exit();
    }

    if (strlen($phone) !== 10) {
        $_SESSION['error'] = "เบอร์โทรศัพท์ต้องมี 10 หลัก (พบ: " . strlen($phone) . " หลัก)";
        header("Location: ../login.php");
        exit();
    }

    // 3. ค้นหา User จากฐานข้อมูล
    $user = selectOne("SELECT * FROM users WHERE phone = ?", [$phone]);

    if (!$user) {
        $_SESSION['error'] = "ไม่พบเบอร์โทรศัพท์นี้ในระบบ";
        header("Location: ../login.php");
        exit();
    }

    // 4. ตรวจสอบเงื่อนไขการ Login แยกตามบทบาท (Role)
    $can_login = false;

    if ($user['role'] == 'user') {
        // ✅ สำหรับลูกค้าทั่วไป: เข้าได้ทันทีโดยไม่ต้องใช้รหัสผ่าน
        $can_login = true;
    } else {
        // 🔒 สำหรับ Admin หรือ Shop: ต้องตรวจสอบรหัสผ่าน
        if (empty($password)) {
            $_SESSION['error'] = "บัญชีระดับเจ้าของร้าน/ผู้ดูแล ต้องระบุรหัสผ่าน";
            header("Location: ../login.php");
            exit();
        }

        // ตรวจสอบ MD5 (ตามมาตรฐานเดิมของระบบคุณ)
        if (md5($password) === $user['password']) {
            $can_login = true;
        } else {
            $_SESSION['error'] = "รหัสผ่านไม่ถูกต้อง";
            header("Location: ../login.php");
            exit();
        }
    }

    // 5. ดำเนินการสร้าง Session หากผ่านเงื่อนไข
    if ($can_login) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['email'] = $user['email']; // เก็บไว้เผื่อเรียกใช้
        
        // เลือกชื่อที่จะแสดงผล
        $_SESSION['user_name'] = ($user['role'] == 'shop') ? $user['shop_name'] : $user['fullname'];

        // ส่งตัวแปร Success (Optional)
        $_SESSION['success'] = "เข้าสู่ระบบสำเร็จ! สวัสดีคุณ " . $_SESSION['user_name'];

        // 6. Redirect ตามระดับสิทธิ์
        switch ($user['role']) {
            case 'admin':
                header("Location: ../admin/dashboard.php");
                break;
            case 'shop':
                header("Location: ../shop/dashboard.php");
                break;
            default:
                header("Location: ../index.php");
                break;
        }
        exit();
    }

} else {
    header("Location: ../login.php");
    exit();
}