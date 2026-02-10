<?php
function pre_var($data)
{
    echo '<pre>';
    print_r($data);
    echo '</pre>';
}

// 1. ฟังก์ชันเชื่อมต่อฐานข้อมูล (Connection Database)
function condb()
{
    global $config; // เรียกใช้ตัวแปร $config จากไฟล์ config.php

    $db_host = $config['database']['host'];
    $db_name = $config['database']['dbname'];
    $db_user = $config['database']['username'];
    $db_pass = $config['database']['password'];
    $charset = $config['database']['charset'];

    try {
        $dsn = "mysql:host=$db_host;dbname=$db_name;charset=$charset";
        $options = $config['database']['options'];

        $pdo = new PDO($dsn, $db_user, $db_pass, $options);
        return $pdo;
    } catch (PDOException $e) {
        // กรณีเชื่อมต่อไม่ได้ ให้แสดง Error และหยุดทำงาน
        die("Connection failed: " . $e->getMessage());
    }
}

// 2. ฟังก์ชันเรียกดูข้อมูล (Generate/Select Table)
// ใช้สำหรับ SELECT ข้อมูลออกมาเป็น Array
function select($sql, $params = [])
{
    $pdo = condb();
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(); // ส่งคืนค่าเป็น Array ทั้งหมด
    } catch (PDOException $e) {
        return []; // ถ้า Error ให้คืนค่า Array ว่าง
    }
}

// แบบดึงแถวเดียว (Single Row) เช่น Login หรือดูรายละเอียดสินค้า
function selectOne($sql, $params = [])
{
    $pdo = condb();
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(); // ส่งคืนค่าแค่แถวเดียว
    } catch (PDOException $e) {
        return false;
    }
}

// 3. ฟังก์ชันเพิ่มข้อมูล (Insert Data)
// วิธีใช้: insert('users', ['name'=>'A', 'email'=>'b@b.com']);
function insert($table, $data)
{
    $pdo = condb();

    // แยก Key (ชื่อคอลัมน์) และ Value (ค่าที่จะใส่)
    $fields = array_keys($data);
    $values = array_values($data);

    // สร้าง String สำหรับ SQL เช่น name, email
    $fieldsStr = implode(",", $fields);
    // สร้าง String สำหรับ Placeholder เช่น ?, ?
    $placeholders = implode(",", array_fill(0, count($fields), "?"));

    $sql = "INSERT INTO $table ($fieldsStr) VALUES ($placeholders)";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        return $pdo->lastInsertId(); // คืนค่า ID ล่าสุดที่เพิ่งเพิ่ม
    } catch (PDOException $e) {
        return false;
    }
}

// 4. ฟังก์ชันอัปเดตข้อมูล (Update Data)
// วิธีใช้: update('users', ['name'=>'NewName'], 'id = 1');
function update($table, $data, $condition, $params = [])
{
    $pdo = condb();

    $setParts = [];
    foreach ($data as $key => $value) {
        $setParts[] = "$key = ?";
    }
    $setStr = implode(", ", $setParts);

    // รวมค่าที่จะอัปเดต กับ ค่าในเงื่อนไข (ถ้ามี)
    $values = array_values($data);
    if (!empty($params)) {
        $values = array_merge($values, $params);
    }

    $sql = "UPDATE $table SET $setStr WHERE $condition";

    try {
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($values); // คืนค่า True ถ้าสำเร็จ
    } catch (PDOException $e) { 
        return false;
    }
}

// 5. ฟังก์ชันลบข้อมูล (Delete Data)
// วิธีใช้: delete('users', 'id = ?', [5]);
function delete($table, $condition, $params = [])
{
    $pdo = condb();

    $sql = "DELETE FROM $table WHERE $condition";

    try {
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params); // คืนค่า True ถ้าสำเร็จ
    } catch (PDOException $e) {
        return false;
    }
}

// 6. ฟังก์ชันอัปโหลดรูปภาพ (Upload Image)
// วิธีใช้: $filename = uploadImage($_FILES['img'], '../uploads/shops/');
function uploadImage($fileInput, $targetDir)
{
    global $config;

    // ตรวจสอบว่ามีไฟล์ส่งมาจริงไหม และไม่มี Error
    if (isset($fileInput) && $fileInput['error'] == 0) {

        // ตรวจสอบนามสกุลไฟล์
        $allowed = $config['upload']['allowed_types'] ?? ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($fileInput['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            // ตั้งชื่อไฟล์ใหม่เป็น "วันที่_เลขสุ่ม.นามสกุล" ป้องกันชื่อซ้ำ
            $newFilename = date('Ymd_His_') . rand(1000, 9999) . '.' . $ext;
            $targetFile = $targetDir . $newFilename;

            // ตรวจสอบว่ามีโฟลเดอร์ไหม ถ้าไม่มีให้สร้าง
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            // ย้ายไฟล์
            if (move_uploaded_file($fileInput['tmp_name'], $targetFile)) {
                return $newFilename; // สำเร็จ: ส่งคืนชื่อไฟล์
            }
        }
    }
    return false; // ล้มเหลว
}
