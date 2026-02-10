<?php
session_start();
require_once '../config.php';

// ตรวจสอบว่ามีตะกร้าหรือยัง ถ้าไม่มีให้สร้าง Array ว่างรอไว้
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// รับค่า action ว่าจะให้ทำอะไร (add, update, remove, clear)
$action = $_REQUEST['action'] ?? null;

// =========================================================
// 1. ADD: เพิ่มสินค้าลงตะกร้า
// =========================================================
if ($action == 'add') {
    $product_id = $_POST['product_id'];
    // รับค่า qty (จากฟอร์มหน้า shop_detail ใช้ชื่อ name="qty")
    $qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;

    // ป้องกันจำนวนติดลบ หรือเป็น 0
    if ($qty <= 0) $qty = 1;

    // เช็คว่าสินค้านี้มีในตะกร้าอยู่แล้วไหม?
    if (isset($_SESSION['cart'][$product_id])) {
        // ถ้ามี: ให้บวกเพิ่มไปจากเดิม
        $_SESSION['cart'][$product_id] += $qty;
    } else {
        // ถ้าไม่มี: ให้สร้างใหม่
        $_SESSION['cart'][$product_id] = $qty;
    }

    // ✅ ใช้ Session แจ้งเตือน (SweetAlert จะเด้งที่หน้าเดิม)
    $_SESSION['success'] = 'เพิ่มสินค้าลงตะกร้าเรียบร้อยแล้ว';
    
    // เด้งกลับไปหน้าเดิม (เช่น shop_detail.php)
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

// =========================================================
// 2. UPDATE: อัปเดตจำนวนสินค้า (จากหน้า cart.php)
// =========================================================
elseif ($action == 'update') {
    // รับค่า Array ของจำนวนสินค้า เช่น name="qty[product_id]"
    $qtys = $_POST['qty']; 

    if (is_array($qtys)) {
        foreach ($qtys as $product_id => $qty) {
            $qty = (int)$qty;
            
            // ถ้าจำนวนมากกว่า 0 ให้อัปเดต
            if ($qty > 0) {
                $_SESSION['cart'][$product_id] = $qty;
            } else {
                // ถ้าปรับเป็น 0 หรือติดลบ ให้ลบสินค้านั้นออก
                unset($_SESSION['cart'][$product_id]);
            }
        }
    }
    
    $_SESSION['success'] = 'อัปเดตตะกร้าเรียบร้อยแล้ว';
    header("Location: ../cart.php");
    exit();
}

// =========================================================
// 3. REMOVE: ลบสินค้าทีละชิ้น
// =========================================================
elseif ($action == 'remove') {
    $product_id = $_GET['id'] ?? 0;

    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
        $_SESSION['success'] = 'ลบสินค้าเรียบร้อยแล้ว';
    } else {
        $_SESSION['error'] = 'ไม่พบสินค้าที่ต้องการลบ';
    }

    // กลับไปหน้าตะกร้า
    header("Location: ../cart.php");
    exit();
}

// =========================================================
// 4. CLEAR: ล้างตะกร้าทั้งหมด
// =========================================================
elseif ($action == 'clear') {
    unset($_SESSION['cart']);
    $_SESSION['success'] = 'ล้างตะกร้าเรียบร้อยแล้ว';
    header("Location: ../cart.php");
    exit();
}

// กรณีเรียกไฟล์โดยไม่มี Action
else {
    header("Location: ../index.php");
    exit();
}
?>