<?php
session_start();
require_once 'config.php';
require_once 'function.php';

// 1. รับค่า Code จาก LINE
$code = $_GET['code'] ?? null;
$state = $_GET['state'] ?? null;

if (!$code) {
    die("Error: No code received from LINE");
}

// 2. ตั้งค่า API Key จาก Config
$client_id     = $config['services']['line']['client_id'];
$client_secret = $config['services']['line']['client_secret'];
$callback_url  = $config['services']['line']['callback_url'];

// 3. ขอ Access Token
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
$response = curl_exec($ch);
curl_close($ch);

$json = json_decode($response, true);
$access_token = $json['access_token'] ?? null;

if (!$access_token) {
    // ถ้า Error ให้แสดงผลเพื่อ Debug
    echo "Error getting token: ";
    print_r($json);
    exit();
}

// 4. ขอข้อมูลโปรไฟล์ผู้ใช้ (Profile)
$profile_url = "https://api.line.me/v2/profile";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $profile_url);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . $access_token
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$profile_response = curl_exec($ch);
curl_close($ch);

$profile = json_decode($profile_response, true);
$line_user_id = $profile['userId'];

// 5. เช็คว่ามี User นี้ใน Database หรือยัง?
$user = selectOne("SELECT * FROM users WHERE line_id = ?", [$line_user_id]);

if ($user) {
    // === กรณี A: มีบัญชีแล้ว (Login เลย) ===
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['fullname'] ?? $user['shop_name'];
    $_SESSION['role'] = $user['role'];

    // เด้งไปตามบทบาท
    if ($user['role'] == 'shop') {
        header("Location: shop/dashboard.php");
    } else {
        header("Location: index.php");
    }
    exit();

} else {
    // === กรณี B: ยังไม่มีบัญชี (ส่งไปหน้าสมัครสมาชิก) ===
    // เก็บข้อมูล Profile ไว้ใน Session เพื่อให้หน้า register_line.php ดึงไปใช้
    $_SESSION['line_profile'] = $profile;
    
    header("Location: register_line.php");
    exit();
}
?>