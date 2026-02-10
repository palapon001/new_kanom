<?php
include_once('function.php');

// ปิด Error ชั่วคราว (เปิดเฉพาะตอน Dev)
if (isset($_GET['debug']) && $_GET['debug'] == 'dev') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    error_reporting(0);
}

// ตั้งค่า Timezone
date_default_timezone_set('Asia/Bangkok');

// ตรวจสอบว่าไม่ได้เข้าถึงไฟล์นี้โดยตรง
// (บรรทัดนี้ใส่ไว้ตามที่คุณต้องการ แต่ถ้าไม่มีโค้ดเช็ค defined ก็จะเป็นแค่ comment ครับ)

$config = [
    // -------------------------------------------------------------------------
    // 1. Application Info
    // -------------------------------------------------------------------------
    'app' => [
        'name'        => 'KanomMuangPhet',
        'title'       => 'KanomMuangPhet | Smart Gastronomy Platform',
        'desc'        => 'โครงการยกระดับขนมหวานเมืองเพชรด้วยนวัตกรรมดิจิทัล เชื่อมโยงผู้ผลิตและผู้ซื้อ',
        'version'     => '1.0.0',
        'language'    => 'th',
        'timezone'    => 'Asia/Bangkok',
        'base_url'    => 'http://localhost/kanommuangphet',
    ],

    // -------------------------------------------------------------------------
    // 2. Theme Configuration
    // -------------------------------------------------------------------------
    'theme' => [
        'colors' => [
            'primary'    => '#E6007E',
            'secondary'  => '#2D1F57',
            'accent'     => '#FDB913',
            'success'    => '#00C853',
            'background' => '#F4F6F9',
            'text_main'  => '#333333',
        ],
        'fonts' => [
            'main' => "'Kanit', sans-serif",
        ],
        'ui' => [
            'radius' => '16px',
            'shadow' => '0 4px 20px rgba(0,0,0,0.05)',
        ]
    ],

    // -------------------------------------------------------------------------
    // 3. Database Connection
    // -------------------------------------------------------------------------
    'database' => [
        'driver'    => 'mysql',
        'host'      => 'localhost',
        'port'      => '3306',
        'dbname'    => 'kanom_muangphet_db',
        'username'  => 'root',
        'password'  => 'root',
        'charset'   => 'utf8mb4',
        'options'   => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    ],

    // -------------------------------------------------------------------------
    // 4. Third-Party Services
    // -------------------------------------------------------------------------
    'services' => [
        'line' => [
            'client_id'     => 'YOUR_LINE_CHANNEL_ID',
            'client_secret' => 'YOUR_LINE_CHANNEL_SECRET',
            'callback_url'  => 'http://localhost/kanommuangphet/callback_line.php',
            'notify_token'  => 'YOUR_NOTIFY_TOKEN',
        ],
        'google_maps' => [
            'api_key'       => 'YOUR_GOOGLE_MAPS_API_KEY',
            'default_lat'   => 13.107044,
            'default_long'  => 99.939885,
        ],
    ],

    // -------------------------------------------------------------------------
    // 5. File Upload Settings
    // -------------------------------------------------------------------------
    'upload' => [
        'max_size'      => 5 * 1024 * 1024,
        'allowed_types' => ['jpg', 'jpeg', 'png', 'webp'],
        'paths' => [
            'shops'       => 'uploads/shops/',
            'menus'       => 'uploads/menus/',
            'ingredients' => 'uploads/ingredients/',
            'profiles'    => 'uploads/profiles/',
        ],
    ],

    // -------------------------------------------------------------------------
    // 6. Business Logic
    // -------------------------------------------------------------------------
    'business' => [
        'vat_rate'        => 7,
        'currency_symbol' => '฿',
        'items_per_page'  => 12,
        'shipping_fee'    => 50,
    ],

    // -------------------------------------------------------------------------
    // 7. Contact Info
    // -------------------------------------------------------------------------
    'contact' => [
        'email'   => 'support@kanommuangphet.com',
        'phone'   => '02-XXX-XXXX',
        'address' => 'สำนักงานนวัตกรรมแห่งชาติ (NIA), เพชรบุรี',
    ]
];

// ==================================================================================
// 🔌 Database Connection Logic (เชื่อมต่อฐานข้อมูล)
// ==================================================================================
$db_conf = $config['database'];

// สร้างการเชื่อมต่อแบบ MySQLi
$conn = new mysqli(
    $db_conf['host'], 
    $db_conf['username'], 
    $db_conf['password'], 
    $db_conf['dbname'],
    $db_conf['port']
);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    // ถ้าเชื่อมต่อไม่ได้ ให้แสดง Error (ใน Production ควรทำหน้า Error สวยๆ แทนการ die)
    die("Connection failed: " . $conn->connect_error . " (ตรวจสอบ config.php)");
}

// ตั้งค่าภาษาไทย
$conn->set_charset($db_conf['charset']);

// ตั้งค่า Timezone ของ MySQL ให้ตรงกับ PHP
$conn->query("SET time_zone = '+07:00'");