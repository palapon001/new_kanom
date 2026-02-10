<?php
session_start();
require_once '../config.php';
require_once '../function.php';

// 1. ความปลอดภัย: ต้องล็อกอินก่อน
if (!isset($_SESSION['user_id'])) {
    die("Access Denied");
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role']; // 'user' หรือ 'shop'

// รับค่าจากฟอร์ม
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // =========================================================
    // PART 1: ข้อมูลพื้นฐาน (อัปเดตได้ทุกคน)
    // =========================================================
    $data = [
        'fullname' => $_POST['fullname'],
        'phone' => $_POST['phone'],
        'address' => $_POST['address']
    ];

    // อัปโหลดรูปโปรไฟล์ (ถ้ามี)
    if (!empty($_FILES['profile_image']['name'])) {
        $upload_dir = '../uploads/profiles/';
        $filename = uploadImage($_FILES['profile_image'], $upload_dir);
        if ($filename) {
            $data['profile_image'] = $filename;
            
            // (Optional) ลบรูปเก่าออกจาก Server เพื่อประหยัดพื้นที่
            // $old_img = selectOne("SELECT profile_image FROM users WHERE id=?", [$user_id]);
            // if($old_img['profile_image']) unlink($upload_dir . $old_img['profile_image']);
        }
    }

    // =========================================================
    // PART 2: ข้อมูลเฉพาะร้านค้า (Shop Only)
    // =========================================================
    if ($role == 'shop') {
        $data['shop_name'] = $_POST['shop_name'];
        $data['bank_name'] = $_POST['bank_name'];
        $data['bank_account'] = $_POST['bank_account'];
        $data['bank_account_name'] = $_POST['bank_account_name'];

        // อัปเดตชื่อใน Session ให้ตรงกับชื่อร้านใหม่
        $_SESSION['user_name'] = $_POST['shop_name'];

        // อัปโหลด QR Code (ถ้ามี)
        if (!empty($_FILES['qrcode_image']['name'])) {
            $upload_dir_shop = '../uploads/shop/'; // หรือ uploads/profiles ตามสะดวก
            $qr_filename = uploadImage($_FILES['qrcode_image'], $upload_dir_shop);
            if ($qr_filename) {
                $data['qrcode_image'] = $qr_filename;
            }
        }
    } else {
        // ถ้าเป็น User ธรรมดา อัปเดตชื่อใน Session เป็น Fullname
        $_SESSION['user_name'] = $_POST['fullname'];
    }

    // =========================================================
    // PART 3: เปลี่ยนรหัสผ่าน (Password Change)
    // =========================================================
    // เช็คว่ามีการกรอกรหัสใหม่มาไหม?
    if (!empty($_POST['new_password'])) {
        $old_password = $_POST['old_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // ดึงรหัสผ่านเก่าจาก DB มาเทียบ
        $user = selectOne("SELECT password FROM users WHERE id = ?", [$user_id]);
        
        // กรณี Login ผ่าน LINE อาจจะไม่มี Password เก่า (ให้ข้ามเช็ค old_password ไปเลย หรือบังคับตั้งใหม่)
        $has_password = !empty($user['password']);

        if ($has_password && !password_verify($old_password, $user['password'])) {
            echo "<script>alert('รหัสผ่านเดิมไม่ถูกต้อง'); history.back();</script>";
            exit();
        }

        if ($new_password !== $confirm_password) {
            echo "<script>alert('รหัสผ่านใหม่ไม่ตรงกัน'); history.back();</script>";
            exit();
        }

        // Hash รหัสผ่านใหม่และเพิ่มลงใน Data array
        $data['password'] = password_hash($new_password, PASSWORD_DEFAULT);
    }

    // =========================================================
    // PART 4: บันทึกลง Database
    // =========================================================
    $result = update('users', $data, "id = ?", [$user_id]);

    if ($result) {
        // ถ้าเป็น Shop ให้กลับ Dashboard, ถ้าเป็น User ให้กลับหน้า Profile หรือหน้าแรก
        $redirect = ($role == 'shop') ? '../shop/dashboard.php' : '../index.php';
        
        // ถ้าคุณสร้างไฟล์ profile.php แล้ว ให้เปลี่ยน redirect เป็น '../profile.php'
        // $redirect = '../profile.php'; 

        echo "<script>alert('บันทึกข้อมูลเรียบร้อยแล้ว'); location.href='$redirect';</script>";
    } else {
        echo "<script>alert('เกิดข้อผิดพลาดในการบันทึกข้อมูล'); history.back();</script>";
    }

} else {
    header("Location: ../index.php");
    exit();
}
?>