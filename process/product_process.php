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
    
    // 🛠️ Logic: ถ้าเป็น material ให้รับ central_id, ถ้าไม่ใช่ให้เป็น NULL
    $central_id = ($category == 'material' && !empty($_POST['central_id'])) ? $_POST['central_id'] : NULL;

    // --- 📸 จัดการรูปภาพ ---
    $image_filename = 'default_kanom.png';
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
        $new_name = uniqid() . '_' . time() . '.' . $ext;
        $dest = '../uploads/kanom/' . $new_name;
        if (move_uploaded_file($_FILES['product_image']['tmp_name'], $dest)) {
            $image_filename = $new_name; 
        }
    }

    $data = [
        'shop_id' => $shop_id,
        'name' => $name,
        'description' => $description,
        'category' => $category,
        'price' => $price,
        'stock_qty' => $stock_qty,
        'image' => $image_filename,
        'status' => $status,
        'central_id' => $central_id,
        'created_at' => date('Y-m-d H:i:s')
    ];

    if (insert('products', $data)) {
        $product_id = mysqli_insert_id($conn); 

        // 🛠️ Logic: บันทึกส่วนผสม/แพ็คเกจ เฉพาะเมื่อ *ไม่ใช่* วัตถุดิบ
        if ($category !== 'material') {
            saveIngredients($product_id, $_POST);
            savePackages($product_id, $_POST);
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

    // เช็คสิทธิ์ความเป็นเจ้าของ
    $check = selectOne("SELECT id FROM products WHERE id = ? AND shop_id = ?", [$id, $shop_id]);
    if (!$check) {
        $_SESSION['error'] = 'คุณไม่มีสิทธิ์แก้ไขสินค้านี้';
        header("Location: ../shop/menu_manage.php");
        exit();
    }

    $category = $_POST['category'];
    
    // 🛠️ Logic: จัดการ Central ID
    $central_id = ($category == 'material' && !empty($_POST['central_id'])) ? $_POST['central_id'] : NULL;

    $update_data = [
        'name' => trim($_POST['name']),
        'description' => trim($_POST['description']),
        'category' => $category,
        'price' => $_POST['price'],
        'stock_qty' => $_POST['stock_qty'],
        'status' => $_POST['status'],
        'central_id' => $central_id
    ];

    // อัปเดตรูปภาพ (ถ้ามีการอัปโหลดใหม่)
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
        $new_name = uniqid() . '_' . time() . '.' . $ext;
        $dest = '../uploads/kanom/' . $new_name;
        if (move_uploaded_file($_FILES['product_image']['tmp_name'], $dest)) {
            $update_data['image'] = $new_name; 
        }
    }

    if(update('products', $update_data, "id = ?", [$id])) {
        
        // 1. ลบข้อมูลเก่าออกทั้งหมดก่อน (ทั้งส่วนผสม และ แพ็คเกจ)
        delete('product_ingredients', "product_id = ?", [$id]);
        delete('product_packages', "product_id = ?", [$id]);
        
        // 2. 🛠️ Logic: บันทึกใหม่ เฉพาะเมื่อ *ไม่ใช่* วัตถุดิบ
        if ($category !== 'material') {
            saveIngredients($id, $_POST);
            savePackages($id, $_POST);
        }

        $_SESSION['success'] = 'แก้ไขข้อมูลสินค้าเรียบร้อยแล้ว';
        header("Location: ../shop/menu_manage.php");
        exit();
    } else {
        $_SESSION['error'] = 'บันทึกข้อมูลไม่สำเร็จ';
        header("Location: ../shop/menu_manage.php");
        exit();
    }
}

// ==========================================
// 🔴 ลบสินค้า
// ==========================================
elseif ($action == 'delete') {
    $id = $_GET['id'];
    $check = selectOne("SELECT id FROM products WHERE id = ? AND shop_id = ?", [$id, $shop_id]);
    
    if ($check) {
        // ลบข้อมูลที่เกี่ยวข้องก่อน (Foreign Key Constraints)
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

// ==========================================
// 🔧 Helper Functions (ช่วยลดโค้ดซ้ำ)
// ==========================================

function saveIngredients($product_id, $post_data) {
    if (isset($post_data['ing_name']) && is_array($post_data['ing_name'])) {
        for ($i = 0; $i < count($post_data['ing_name']); $i++) {
            if (!empty($post_data['ing_name'][$i])) {
                
                $linked_id = !empty($post_data['linked_product_id'][$i]) ? $post_data['linked_product_id'][$i] : NULL;

                insert('product_ingredients', [
                    'product_id' => $product_id,
                    'ingredient_name' => trim($post_data['ing_name'][$i]),
                    'amount' => $post_data['ing_amount'][$i],
                    'unit' => trim($post_data['ing_unit'][$i]),
                    'linked_product_id' => $linked_id
                ]);
            }
        }
    }
}

function savePackages($product_id, $post_data) {
    if (isset($post_data['pack_name']) && is_array($post_data['pack_name'])) {
        for ($i = 0; $i < count($post_data['pack_name']); $i++) {
            if (!empty($post_data['pack_name'][$i])) {
                insert('product_packages', [
                    'product_id' => $product_id,
                    'package_name' => trim($post_data['pack_name'][$i]),
                    'qty_per_pack' => $post_data['pack_amount'][$i],
                    'price' => $post_data['pack_price'][$i]
                ]);
            }
        }
    }
}
?>