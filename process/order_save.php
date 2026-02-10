<?php
session_start();
require_once '../config.php';
require_once '../function.php';

// 1. ตรวจสอบสิทธิ์
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบก่อนทำรายการ';
    header("Location: ../login.php");
    exit();
}

// 2. ตรวจสอบข้อมูลที่ส่งมา
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../cart.php");
    exit();
}

$shop_id = $_POST['shop_id'] ?? 0;
// รับค่าที่อยู่แยกส่วนมา แล้วรวมเป็นก้อนเดียว
$fullname = trim($_POST['fullname']);
$phone = trim($_POST['phone']);
$address_text = trim($_POST['address']);

// ✅ รวมข้อมูลจัดส่งเพื่อใส่ใน field 'shipping_address'
$shipping_address = "ชื่อผู้รับ: $fullname\nเบอร์โทร: $phone\nที่อยู่: $address_text";

$customer_id = $_SESSION['user_id']; // ไอดีคนซื้อ

// ตรวจสอบว่าตะกร้ามีสินค้าไหม
if (empty($_SESSION['cart'])) {
    $_SESSION['error'] = 'ตะกร้าสินค้าว่างเปล่า';
    header("Location: ../cart.php");
    exit();
}

// 3. จัดการอัปโหลดสลิป (Payment Slip)
$slip_filename = null;
if (isset($_FILES['payment_slip']) && $_FILES['payment_slip']['error'] == 0) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $ext = strtolower(pathinfo($_FILES['payment_slip']['name'], PATHINFO_EXTENSION));
    
    if (in_array($ext, $allowed)) {
        $new_name = "slip_" . uniqid() . "_" . time() . "." . $ext;
        $dest = '../uploads/slips/' . $new_name;
        
        if (move_uploaded_file($_FILES['payment_slip']['tmp_name'], $dest)) {
            $slip_filename = $new_name;
        } else {
            $_SESSION['error'] = 'อัปโหลดสลิปไม่สำเร็จ';
            header("Location: ../checkout.php?shop_id=$shop_id");
            exit();
        }
    } else {
        $_SESSION['error'] = 'ไฟล์สลิปต้องเป็นรูปภาพเท่านั้น';
        header("Location: ../checkout.php?shop_id=$shop_id");
        exit();
    }
} else {
    $_SESSION['error'] = 'กรุณาแนบหลักฐานการโอนเงิน';
    header("Location: ../checkout.php?shop_id=$shop_id");
    exit();
}

// 4. ดึงสินค้า (เฉพาะของ Shop นี้)
$cart_ids = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($cart_ids), '?'));

$sql = "SELECT * FROM products WHERE id IN ($placeholders) AND shop_id = ?";
$params = array_merge($cart_ids, [$shop_id]);
$products = select($sql, $params);

if (empty($products)) {
    $_SESSION['error'] = 'ไม่พบสินค้าของร้านนี้ในตะกร้า';
    header("Location: ../cart.php");
    exit();
}

// 5. คำนวณยอดรวม
$total_amount = 0;
$order_items = [];

foreach ($products as $p) {
    $qty = $_SESSION['cart'][$p['id']];
    $price = $p['price'];
    $subtotal = $price * $qty;
    
    $total_amount += $subtotal;
    
    $order_items[] = [
        'product_id' => $p['id'],
        'product_name' => $p['name'],
        'price' => $price,
        'quantity' => $qty
    ];
}

// 6. บันทึกลง Database
global $conn;
mysqli_begin_transaction($conn);

try {
    // สร้างเลข Order เช่น OR-2024XXXX
    $order_no = 'OR-' . date('Y') . rand(1000, 9999);
    
    // ✅ เตรียมข้อมูลลงตาราง 'orders' (ชื่อคอลัมน์ต้องตรงกับ DB เป๊ะๆ)
    $order_data = [
        'order_no' => $order_no,
        'customer_id' => $customer_id,   // แก้จาก user_id เป็น customer_id
        'shop_id' => $shop_id,
        'total_amount' => $total_amount, // แก้จาก total_price เป็น total_amount
        'status' => 'pending',
        'slip_image' => $slip_filename,
        'shipping_address' => $shipping_address, // รวมชื่อ+เบอร์+ที่อยู่ ใส่ในนี้
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    // Insert ลงตาราง orders
    if (insert('orders', $order_data)) {
        $order_id = mysqli_insert_id($conn); 
        
        // Insert ลงตาราง order_items
        foreach ($order_items as $item) {
            insert('order_items', [
                'order_id' => $order_id,
                'product_id' => $item['product_id'],
                'product_name' => $item['product_name'],
                'price' => $item['price'],
                'quantity' => $item['quantity']
            ]);
            
            // ลบสินค้าออกจากตะกร้า (เฉพาะชิ้นที่สั่ง)
            unset($_SESSION['cart'][$item['product_id']]);
        }
        
        mysqli_commit($conn);
        
        $_SESSION['success'] = 'สั่งซื้อเรียบร้อยแล้ว!';
        header("Location: ../my_orders.php"); // เดี๋ยวไปสร้างหน้านี้กันต่อ
        exit();
        
    } else {
        throw new Exception("Insert Orders Failed");
    }

} catch (Exception $e) {
    mysqli_rollback($conn);
    $_SESSION['error'] = 'บันทึกข้อมูลไม่สำเร็จ: ' . $e->getMessage();
    header("Location: ../checkout.php?shop_id=$shop_id");
    exit();
}
?>