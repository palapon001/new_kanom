<?php
session_start();
require_once '../config.php';
require_once '../function.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    
    $user_id = $_SESSION['user_id'];
    $fullname = trim($_POST['fullname']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    // รับค่าพิกัด
    $latitude = !empty($_POST['latitude']) ? $_POST['latitude'] : NULL;
    $longitude = !empty($_POST['longitude']) ? $_POST['longitude'] : NULL;

    // ข้อมูลพื้นฐานที่จะอัปเดต
    $update_data = [
        'fullname' => $fullname,
        'phone' => $phone,
        'address' => $address
    ];

    // ถ้าเป็นร้านค้า ให้เพิ่มข้อมูลร้านและพิกัดลงไป
    if ($_SESSION['role'] == 'shop') {
        $update_data['shop_name'] = trim($_POST['shop_name']);
        $update_data['bank_name'] = trim($_POST['bank_name']);
        $update_data['bank_account'] = trim($_POST['bank_account']);
        $update_data['bank_account_name'] = trim($_POST['bank_account_name']);
        
        // เพิ่มพิกัดลง array
        $update_data['latitude'] = $latitude;
        $update_data['longitude'] = $longitude;
    }

    // ... (ส่วนจัดการรูปภาพ และ รหัสผ่าน เหมือนเดิม) ...

    // สั่ง Update
    if (update('users', $update_data, "id = ?", [$user_id])) {
        $_SESSION['success'] = 'บันทึกข้อมูลเรียบร้อยแล้ว';
    } else {
        $_SESSION['error'] = 'เกิดข้อผิดพลาดในการบันทึก';
    }

    header("Location: ../profile.php");
    exit();
}
?>