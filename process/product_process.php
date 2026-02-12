<?php
session_start();
require_once '../config.php';
require_once '../function.php';

// 1. ตรวจสอบสิทธิ์
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'shop') {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบก่อนใช้งาน';
    header("Location: ../login.php");
    exit();
}

$shop_id = $_SESSION['user_id'];
$action = $_REQUEST['action'] ?? '';
global $conn; 

// ==========================================
// 🟢 เพิ่มสินค้าใหม่ (Add Product)
// ==========================================
if ($action == 'add_product' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $category = $_POST['category'];
    $price = $_POST['price'];
    $stock_qty = $_POST['stock_qty'];
    $status = $_POST['status'];
    
    // ✅ รับค่า central_id (ถ้าไม่มีให้เป็น NULL)
    $central_id = !empty($_POST['central_id']) ? $_POST['central_id'] : NULL;

    // --- 📸 ส่วนจัดการรูปภาพ ---
    $image_filename = 'default_kanom.png'; // ค่าเริ่มต้น
    
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
        $new_name = uniqid() . '_' . time() . '.' . $ext;
        $dest = '../uploads/kanom/' . $new_name;
        
        if (move_uploaded_file($_FILES['product_image']['tmp_name'], $dest)) {
            $image_filename = $new_name; 
        }
    }
    // -------------------------------------

    $data = [
        'shop_id' => $shop_id,
        'name' => $name,
        'description' => $description,
        'category' => $category,
        'price' => $price,
        'stock_qty' => $stock_qty,
        'image' => $image_filename,
        'status' => $status,
        'central_id' => $central_id, // ✅ บันทึก central_id
        'created_at' => date('Y-m-d H:i:s')
    ];

    if (insert('products', $data)) {
        $product_id = mysqli_insert_id($conn); 

        // บันทึกส่วนผสม
        if (isset($_POST['ing_name']) && is_array($_POST['ing_name'])) {
            for ($i = 0; $i < count($_POST['ing_name']); $i++) {
                if (!empty($_POST['ing_name'][$i])) {
                    insert('product_ingredients', [
                        'product_id' => $product_id,
                        'ingredient_name' => trim($_POST['ing_name'][$i]),
                        'amount' => $_POST['ing_amount'][$i],
                        'unit' => trim($_POST['ing_unit'][$i])
                    ]);
                }
            }
        }

        // บันทึกแพ็คเกจ
        if (isset($_POST['pack_name']) && is_array($_POST['pack_name'])) {
            for ($i = 0; $i < count($_POST['pack_name']); $i++) {
                if (!empty($_POST['pack_name'][$i])) {
                    insert('product_packages', [
                        'product_id' => $product_id,
                        'package_name' => trim($_POST['pack_name'][$i]),
                        'qty_per_pack' => $_POST['pack_amount'][$i],
                        'price' => $_POST['pack_price'][$i]
                    ]);
                }
            }
        }

        $_SESSION['success'] = 'เพิ่มสินค้าใหม่เรียบร้อยแล้ว';
        header("Location: ../shop/menu_manage.php");
        exit();

    } else {
        $_SESSION['error'] = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล';
        header("Location: ../shop/menu_manage.php");
        exit();
    }
}

// ==========================================
// 🟡 แก้ไขสินค้า (Edit Product)
// ==========================================
elseif ($action == 'edit_product' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $id = $_POST['id'];

    // เช็คความเป็นเจ้าของ
    $check = selectOne("SELECT id FROM products WHERE id = ? AND shop_id = ?", [$id, $shop_id]);
    if (!$check) {
        $_SESSION['error'] = 'คุณไม่มีสิทธิ์แก้ไขสินค้านี้';
        header("Location: ../shop/menu_manage.php");
        exit();
    }

    // ✅ รับค่า central_id
    $central_id = !empty($_POST['central_id']) ? $_POST['central_id'] : NULL;

    $update_data = [
        'name' => trim($_POST['name']),
        'description' => trim($_POST['description']),
        'category' => $_POST['category'],
        'price' => $_POST['price'],
        'stock_qty' => $_POST['stock_qty'],
        'status' => $_POST['status'],
        'central_id' => $central_id // ✅ อัปเดต central_id
    ];

    // --- 📸 ส่วนจัดการรูปภาพตอนแก้ไข ---
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
        $new_name = uniqid() . '_' . time() . '.' . $ext;
        $dest = '../uploads/kanom/' . $new_name;
        
        if (move_uploaded_file($_FILES['product_image']['tmp_name'], $dest)) {
            $update_data['image'] = $new_name; 
        }
    }
    // ---------------------------------------------

    // สั่งอัปเดต
    if(update('products', $update_data, "id = ?", [$id])) {
        
        // ลบและเพิ่มใหม่ (ส่วนผสม/แพ็คเกจ)
        delete('product_ingredients', "product_id = ?", [$id]);
        if (isset($_POST['ing_name'])) {
            for ($i = 0; $i < count($_POST['ing_name']); $i++) {
                if (!empty($_POST['ing_name'][$i])) {
                    insert('product_ingredients', [
                        'product_id' => $id,
                        'ingredient_name' => trim($_POST['ing_name'][$i]),
                        'amount' => $_POST['ing_amount'][$i],
                        'unit' => trim($_POST['ing_unit'][$i])
                    ]);
                }
            }
        }

        delete('product_packages', "product_id = ?", [$id]);
        if (isset($_POST['pack_name'])) {
            for ($i = 0; $i < count($_POST['pack_name']); $i++) {
                if (!empty($_POST['pack_name'][$i])) {
                    insert('product_packages', [
                        'product_id' => $id,
                        'package_name' => trim($_POST['pack_name'][$i]),
                        'qty_per_pack' => $_POST['pack_amount'][$i],
                        'price' => $_POST['pack_price'][$i]
                    ]);
                }
            }
        }

        $_SESSION['success'] = 'แก้ไขข้อมูลสินค้าเรียบร้อยแล้ว';
        header("Location: ../shop/menu_manage.php");
        exit();

    } else {
        $_SESSION['error'] = 'บันทึกข้อมูลไม่สำเร็จ (Database Error)';
        header("Location: ../shop/menu_manage.php");
        exit();
    }
}

// ==========================================
// 🔴 ลบสินค้า (Delete)
// ==========================================
elseif ($action == 'delete') {
    $id = $_GET['id'];
    $check = selectOne("SELECT id FROM products WHERE id = ? AND shop_id = ?", [$id, $shop_id]);
    
    if ($check) {
        delete('product_ingredients', "product_id = ?", [$id]);
        delete('product_packages', "product_id = ?", [$id]);
        delete('products', "id = ?", [$id]);
        
        $_SESSION['success'] = 'ลบสินค้าเรียบร้อยแล้ว';
        header("Location: ../shop/menu_manage.php");
        exit();
    } else {
        $_SESSION['error'] = 'ไม่พบสินค้า หรือคุณไม่มีสิทธิ์ลบ';
        header("Location: ../shop/menu_manage.php");
        exit();
    }
}
?>