<?php
// ไฟล์: api/search_materials.php
header('Content-Type: application/json');
require_once '../config.php';
require_once '../function.php';

$q = $_GET['q'] ?? '';

// ถ้าคำค้นหาสั้นเกินไป ให้คืนค่าว่าง
if (mb_strlen($q) < 2) {
    echo json_encode([]);
    exit();
}

// SQL ค้นหาสินค้าที่เป็น "วัตถุดิบ (material)" และ "วางขาย (active)"
// Join ตาราง users เพื่อเอาชื่อร้านค้ามาโชว์ด้วย
$sql = "SELECT p.id, p.name, p.price, u.shop_name 
        FROM products p 
        JOIN users u ON p.shop_id = u.id 
        WHERE p.status = 'active' 
        AND p.category = 'material' 
        AND (p.name LIKE ? OR u.shop_name LIKE ?) 
        LIMIT 10";

$params = ["%$q%", "%$q%"];
$results = select($sql, $params);

// ส่งผลลัพธ์กลับเป็น JSON
echo json_encode($results);
?>