<?php
session_start();
require_once '../config.php';
require_once '../function.php';

// 1. ความปลอดภัยขั้นสูง: ต้องเป็น Admin เท่านั้น
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // ถ้าไม่ใช่ Admin ดีดออกไปหน้า Login พร้อมแจ้งเตือน
    $_SESSION['error'] = 'คุณไม่มีสิทธิ์เข้าถึงส่วนนี้ (Access Denied)';
    header("Location: ../login.php");
    exit();
}

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

if ($action == 'delete' && $id > 0) {
    
    // 2. ป้องกันการลบตัวเอง (สำคัญมาก)
    if ($id == $_SESSION['user_id']) {
        $_SESSION['error'] = 'ไม่สามารถลบบัญชีตัวเองได้!';
        header("Location: ../admin/users_manage.php");
        exit();
    }

    // 3. เริ่มการลบ User
    // (Database ควรตั้ง Foreign Key แบบ ON DELETE CASCADE ไว้ เพื่อให้ข้อมูลที่เกี่ยวข้องหายไปเอง)
    $result = delete('users', "id = ?", [$id]);

    if ($result) {
        // ✅ ลบสำเร็จ: ส่ง Session Success
        $_SESSION['success'] = 'ลบสมาชิกออกจากระบบเรียบร้อยแล้ว';
    } else {
        // ❌ ลบไม่สำเร็จ: ส่ง Session Error
        $_SESSION['error'] = 'เกิดข้อผิดพลาดในการลบข้อมูลจากฐานข้อมูล';
    }

    // 4. Redirect กลับไปหน้ารายการ
    header("Location: ../admin/users_manage.php");
    exit();

} else {
    // กรณีไม่มี Action หรือ ID
    $_SESSION['error'] = 'คำสั่งไม่ถูกต้อง (Invalid Request)';
    header("Location: ../admin/users_manage.php");
    exit();
}
?>