<?php
session_start();
require_once 'config.php';
require_once 'function.php';

// 1. รับค่า Code จาก LINE
$code = $_GET['code'] ?? null;

if (!$code) {
    // ถ้าไม่มี code ให้เด้งกลับไปหน้า login
    header("Location: login.php");
    exit();
}

// 2. ดึงค่าจาก Config
$client_id     = $config['services']['line']['client_id'];
$client_secret = $config['services']['line']['client_secret'];
$callback_url  = $config['services']['line']['callback_url'];

// 3. แลกเปลี่ยน Code เป็น Access Token
$token_url = "https://api.line.me/oauth2/v2.1/token";
$data = [
    'grant_type'    => 'authorization_code',
    'code'          => $code,
    'redirect_uri'  => $callback_url,
    'client_id'     => $client_id,
    'client_secret' => $client_secret
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $token_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// 🟢 เพิ่มส่วนนี้เพื่อแก้ปัญหา SSL บน localhost
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);
curl_close($ch);

$json = json_decode($response, true);

if (!isset($json['access_token'])) {
    // 🔴 หาก Error ให้ส่งกลับไปหน้า Login พร้อมข้อความแจ้งเตือน
    $_SESSION['error'] = "การเชื่อมต่อกับ LINE ล้มเหลว หรือ Code หมดอายุ กรุณาลองใหม่อีกครั้ง";
    header("Location: login.php");
    exit();
}

$access_token = $json['access_token'];

// 4. ขอข้อมูล Profile
$profile_url = "https://api.line.me/v2/profile";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $profile_url);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer " . $access_token]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 🟢 สำหรับ localhost
$profile_response = curl_exec($ch);
curl_close($ch);

$profile = json_decode($profile_response, true);
$line_user_id = $profile['userId'];

// 5. ตรวจสอบในฐานข้อมูล
// เช็คว่ามีใครใช้ LINE ID นี้ผูกไว้หรือยัง
$user = selectOne("SELECT * FROM users WHERE line_id = ?", [$line_user_id]);

if ($user) {
    // ✅ มีบัญชีแล้ว -> Login
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['fullname'] = $user['fullname'];
    $_SESSION['role'] = $user['role'];
    
    // แจ้งเตือนสำเร็จ
    $_SESSION['success'] = "ยินดีต้อนรับกลับคุณ " . $user['fullname'];

    // ส่งไปตามหน้าที่ควรจะเป็น
    if ($user['role'] == 'admin') {
        header("Location: admin/index.php");
    } elseif ($user['role'] == 'shop') {
        header("Location: shop/dashboard.php");
    } else {
        header("Location: index.php");
    }
    exit();

} else {
    // 🆕 ยังไม่มีบัญชี -> ไปหน้าสมัครสมาชิก
    $_SESSION['line_profile'] = $profile;
    header("Location: register_line.php");
    exit();
}