<?php
session_start();
require_once '../config.php';
require_once '../function.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['line_profile'])) {
    $line = $_SESSION['line_profile'];
    $role = $_POST['role'];
    $phone = $_POST['phone'];
    $shop_name = $_POST['shop_name'] ?? null;

    // เตรียมข้อมูลลงตาราง users
    $data = [
        'line_id'       => $line['userId'],
        'fullname'      => $line['displayName'],
        'profile_image' => $line['pictureUrl'], // บันทึกรูปจาก LINE
        'role'          => $role,
        'phone'         => $phone,
        'shop_name'     => ($role == 'shop') ? $shop_name : null,
        'created_at'    => date('Y-m-d H:i:s')
    ];

    if (insert('users', $data)) {
        // เมื่อบันทึกสำเร็จ ให้ดึง ID มาทำ Session Login
        $new_user_id = mysqli_insert_id($conn);
        
        $_SESSION['user_id'] = $new_user_id;
        $_SESSION['user_name'] = ($role == 'shop') ? $shop_name : $line['displayName'];
        $_SESSION['role'] = $role;

        // ล้างข้อมูลโปรไฟล์ LINE ออกจาก Session เพราะใช้เสร็จแล้ว
        unset($_SESSION['line_profile']);

        if ($role == 'shop') {
            header("Location: ../shop/dashboard.php");
        } else {
            header("Location: ../index.php");
        }
    } else {
        die("Registration failed.");
    }
}