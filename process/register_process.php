<?php
session_start();
require_once '../config.php'; // เชื่อมต่อฐานข้อมูล

// ตรวจสอบว่ามีการส่งข้อมูลแบบ POST มาหรือไม่
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // 1. รับค่าจากฟอร์ม และป้องกัน SQL Injection เบื้องต้น
    $role = trim($_POST['role']); // user หรือ shop
    $fullname = trim($_POST['fullname']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // รับชื่อร้านค้า (ถ้ามี)
    $shop_name = isset($_POST['shop_name']) ? trim($_POST['shop_name']) : '';

    // -------------------------------------------------------------------------
    // 2. ตรวจสอบความถูกต้องของข้อมูล (Validation)
    // -------------------------------------------------------------------------

    // 2.1 เช็คว่ากรอกข้อมูลครบไหม
    if (empty($fullname) || empty($phone) || empty($password)) {
        $_SESSION['error'] = "กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน";
        header("Location: ../register.php");
        exit();
    }

    // 2.2 เช็คถ้ารหัสผ่านไม่ตรงกัน
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "รหัสผ่านยืนยันไม่ตรงกัน";
        header("Location: ../register.php");
        exit();
    }

    // 2.3 เช็คเบอร์โทรศัพท์ซ้ำ (สำคัญ! เพราะใช้เป็น ID Login)
    // ใช้ Prepared Statement เพื่อความปลอดภัย
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE phone = ?");
    $check_stmt->bind_param("s", $phone);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $_SESSION['error'] = "เบอร์โทรศัพท์นี้มีผู้ใช้งานแล้ว";
        header("Location: ../register.php");
        exit();
    }
    $check_stmt->close();

    // 2.4 ตรวจสอบ Role และ Shop Name
    if ($role == 'shop') {
        if (empty($shop_name)) {
            $_SESSION['error'] = "กรุณาระบุชื่อร้านค้า";
            header("Location: ../register.php");
            exit();
        }
    } else {
        // ถ้าเป็น User ธรรมดา ให้ shop_name เป็น null หรือว่าง
        $role = 'user'; // บังคับค่าเพื่อความชัวร์
        $shop_name = NULL;
    }

    // -------------------------------------------------------------------------
    // 3. บันทึกข้อมูลลงฐานข้อมูล (Insert)
    // -------------------------------------------------------------------------

    // เข้ารหัสรหัสผ่าน (ใช้ MD5 เพื่อให้ตรงกับระบบ Login เดิมของคุณ)
    // *หมายเหตุ: ในอนาคตควรเปลี่ยนไปใช้ password_hash() เพื่อความปลอดภัยที่สูงกว่า
    $password_hashed = md5($password);
    
    // รูปโปรไฟล์เริ่มต้น (ถ้าอยากใส่ Default)
    $default_image = ''; 

    $sql = "INSERT INTO users (role, shop_name, fullname, phone, email, password, profile_image, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        // bind parameters (s = string)
        $stmt->bind_param("sssssss", $role, $shop_name, $fullname, $phone, $email, $password_hashed, $default_image);
        
        if ($stmt->execute()) {
            // ✅ สมัครสมาชิกสำเร็จ
            $_SESSION['success'] = "สมัครสมาชิกเรียบร้อยแล้ว! กรุณาเข้าสู่ระบบ";
            header("Location: ../login.php"); // ส่งไปหน้า Login
        } else {
            // ❌ เกิดข้อผิดพลาดตอน Insert
            $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $stmt->error;
            header("Location: ../register.php");
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "Database Error: " . $conn->error;
        header("Location: ../register.php");
    }

} else {
    // ถ้าไม่ได้มาด้วยท่า POST ให้ดีดกลับไป
    header("Location: ../register.php");
    exit();
}
?>