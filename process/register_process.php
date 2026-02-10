<?php
session_start();
require_once '../config.php';
require_once '../function.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $phone = trim($_POST['phone']);
    $role = $_POST['role'] ?? 'user'; // รับค่า role (user หรือ shop)

    // 1. ตรวจสอบข้อมูลเบื้องต้น
    if (empty($fullname) || empty($email) || empty($password) || empty($phone)) {
        $_SESSION['error'] = "กรุณากรอกข้อมูลให้ครบถ้วน";
        header("Location: ../register.php");
        exit();
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "รหัสผ่านไม่ตรงกัน";
        header("Location: ../register.php");
        exit();
    }

    // 2. เช็คว่าอีเมลซ้ำไหม
    $checkEmail = selectOne("SELECT email FROM users WHERE email = ?", [$email]);
    if ($checkEmail) {
        $_SESSION['error'] = "อีเมลนี้ถูกใช้งานแล้ว";
        header("Location: ../register.php");
        exit();
    }

    // 3. เข้ารหัสรหัสผ่าน (ใช้ MD5 ให้ตรงกับหน้า Login)
    // ⚠️ แก้ไขตรงนี้จาก password_hash เป็น md5
    $password_hashed = md5($password);

    // 4. บันทึกข้อมูลลงฐานข้อมูล
    $data = [
        'fullname' => $fullname,
        'email' => $email,
        'password' => $password_hashed,
        'phone' => $phone,
        'role' => $role,
        'created_at' => date('Y-m-d H:i:s')
    ];

    $result = insert('users', $data);

    if ($result) {
        $_SESSION['success'] = "สมัครสมาชิกสำเร็จ! กรุณาเข้าสู่ระบบ";
        header("Location: ../login.php");
    } else {
        $_SESSION['error'] = "เกิดข้อผิดพลาด กรุณาลองใหม่";
        header("Location: ../register.php");
    }

} else {
    header("Location: ../register.php");
    exit();
}
?>