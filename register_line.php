<?php
session_start();

// 1. เรียกใช้ Config และ Function (ไม่ต้องถอยหลังแล้ว)
require_once 'config.php';
require_once 'function.php';

// ดึงตัวแปร Config มาใช้
$theme = $config['theme'];

// ตรวจสอบ Session จาก LINE
if (!isset($_SESSION['line_profile'])) {
    // ถ้าไม่มีข้อมูล ให้เด้งกลับหน้า Login (แก้ path)
    header("Location: login.php"); 
    exit();
}

$line_profile = $_SESSION['line_profile'];
$line_id      = $line_profile['userId'] ?? 'U123456789 (Demo)';
$displayName  = $line_profile['displayName'] ?? 'LINE User';
$pictureUrl   = $line_profile['pictureUrl'] ?? 'https://source.unsplash.com/100x100/?face';

// กำหนด Path Prefix เป็นค่าว่าง (เพราะอยู่หน้าบ้านแล้ว)
$path_prefix = ''; 

// เรียก Header/Navbar (ชี้เข้าโฟลเดอร์ includes)
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container py-5 d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card border-0 shadow-lg p-4" style="max-width: 500px; width: 100%; border-radius: <?= $theme['ui']['radius'] ?>;">
        <div class="card-body text-center">
            
            <div class="position-relative d-inline-block mb-3">
                <img src="<?= $pictureUrl ?>" class="rounded-circle shadow-sm border border-4 border-white" width="100" height="100" alt="Profile">
                <span class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle p-2">
                    <i class="fab fa-line text-white"></i>
                </span>
            </div>

            <h4 class="fw-bold text-purple">ยินดีต้อนรับ, <?= htmlspecialchars($displayName) ?></h4>
            <p class="text-muted small mb-4">กรุณากรอกข้อมูลอีกเล็กน้อยเพื่อเริ่มต้นใช้งาน</p>
            
            <form action="process/register_process.php" method="POST" class="text-start">
                
                <input type="hidden" name="register_type" value="line">
                <input type="hidden" name="line_id" value="<?= $line_id ?>">
                <input type="hidden" name="profile_image" value="<?= $pictureUrl ?>">

                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">คุณต้องการใช้งานแบบไหน?</label>
                    <div class="d-flex gap-2">
                        <input type="radio" class="btn-check" name="role" id="role_user" value="user" checked>
                        <label class="btn btn-outline-nia w-50" for="role_user">
                            <i class="fas fa-shopping-basket me-1"></i> ผู้ซื้อ
                        </label>

                        <input type="radio" class="btn-check" name="role" id="role_shop" value="shop">
                        <label class="btn btn-outline-nia w-50" for="role_shop">
                            <i class="fas fa-store me-1"></i> ร้านค้า
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">ชื่อที่ใช้แสดง / ชื่อร้าน</label>
                    <input type="text" name="name" class="form-control bg-light" value="<?= htmlspecialchars($displayName) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">เบอร์โทรศัพท์</label>
                    <input type="tel" name="phone" class="form-control bg-light" placeholder="08X-XXX-XXXX" required>
                </div>

                <div class="mb-4">
                    <label class="form-label text-muted small fw-bold">อีเมล (ถ้ามี)</label>
                    <input type="email" name="email" class="form-control bg-light" placeholder="ระบุอีเมลเพื่อรับใบเสร็จ/การแจ้งเตือน">
                </div>
                
                <button type="submit" class="btn btn-success w-100 py-2 shadow-sm fw-bold">
                    <i class="fas fa-check-circle me-2"></i> ยืนยันการลงทะเบียน
                </button>
            </form>

        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>