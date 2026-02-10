<?php
session_start();
require_once '../config.php';
require_once '../function.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "กรุณากรอกอีเมลและรหัสผ่าน";
        header("Location: ../login.php");
        exit();
    }

    $user = selectOne("SELECT * FROM users WHERE email = ?", [$email]);

    // ✅ แก้ไขตรงนี้: ใช้ MD5
    $h = md5($password);

    // เช็คว่าค่า MD5 ตรงกับในฐานข้อมูลไหม
    if ($user && $h === $user['password']) {

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['email'] = $user['email'];

        if ($user['role'] == 'shop') {
            $_SESSION['user_name'] = $user['shop_name'];
        } else {
            $_SESSION['user_name'] = $user['fullname'];
        }

        if ($user['role'] == 'admin') {
            header("Location: ../admin/dashboard.php");
        } elseif ($user['role'] == 'shop') {
            header("Location: ../shop/dashboard.php");
        } else {
            header("Location: ../index.php");
        }
        exit();
    } else {
        $_SESSION['error'] = "อีเมลหรือรหัสผ่านไม่ถูกต้อง";
        header("Location: ../login.php");
        exit();
    }
} else {
    header("Location: ../login.php");
    exit();
}
?>