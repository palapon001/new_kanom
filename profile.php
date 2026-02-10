<?php
session_start();
require_once 'config.php';
require_once 'function.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ดึงข้อมูล User ล่าสุด
$user = selectOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
$role = $user['role']; // 'shop' หรือ 'user'

$theme = $config['theme'];
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-purple text-white py-4 text-center" style="background-color: <?= $theme['colors']['secondary'] ?>;">
                    
                    <div class="position-relative d-inline-block mb-2">
                        <?php 
                            $img = $user['profile_image'] ?? 'https://source.unsplash.com/150x150/?person';
                            if(!filter_var($img, FILTER_VALIDATE_URL)) $img = 'uploads/profiles/' . $img;
                        ?>
                        <img src="<?= $img ?>" class="rounded-circle border border-4 border-white shadow" width="120" height="120" style="object-fit: cover;">
                        <label for="profile_upload" class="position-absolute bottom-0 end-0 btn btn-sm btn-light rounded-circle shadow-sm" style="width: 35px; height: 35px; cursor: pointer;">
                            <i class="fas fa-camera text-muted mt-1"></i>
                        </label>
                    </div>
                    <h4 class="fw-bold mb-0"><?= htmlspecialchars($user['fullname'] ?? $user['shop_name']) ?></h4>
                    <span class="badge bg-white text-purple bg-opacity-75 rounded-pill px-3 mt-2">
                        <?= ($role == 'shop') ? 'ผู้ประกอบการ (Shop)' : 'สมาชิกทั่วไป (User)' ?>
                    </span>
                </div>

                <div class="card-body p-4 p-md-5">
                    
                    <form action="process/profile_update.php" method="POST" enctype="multipart/form-data">
                        
                        <input type="file" id="profile_upload" name="profile_image" class="d-none" accept="image/*">

                        <h6 class="fw-bold text-muted border-bottom pb-2 mb-3">ข้อมูลทั่วไป</h6>
                        
                        <?php if ($role == 'shop'): ?>
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted">ชื่อร้านค้า</label>
                                <input type="text" name="shop_name" class="form-control" value="<?= htmlspecialchars($user['shop_name']) ?>" required>
                            </div>
                        <?php endif; ?>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">ชื่อ-นามสกุล (ผู้ติดต่อ)</label>
                                <input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($user['fullname']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">เบอร์โทรศัพท์</label>
                                <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted">ที่อยู่ (สำหรับจัดส่ง / ที่ตั้งร้าน)</label>
                            <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($user['address']) ?></textarea>
                        </div>

                        <?php if ($role == 'shop'): ?>
                            <h6 class="fw-bold text-muted border-bottom pb-2 mb-3 mt-4">ข้อมูลการเงิน (สำหรับรับชำระเงิน)</h6>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-muted">ธนาคาร</label>
                                    <input type="text" name="bank_name" class="form-control" placeholder="เช่น กสิกรไทย" value="<?= htmlspecialchars($user['bank_name']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-muted">เลขที่บัญชี</label>
                                    <input type="text" name="bank_account" class="form-control" placeholder="XXX-X-XXXXX-X" value="<?= htmlspecialchars($user['bank_account']) ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold small text-muted">ชื่อบัญชี</label>
                                    <input type="text" name="bank_account_name" class="form-control" value="<?= htmlspecialchars($user['bank_account_name']) ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold small text-muted">QR Code (PromptPay)</label>
                                    <input type="file" name="qrcode_image" class="form-control" accept="image/*">
                                    <?php if($user['qrcode_image']): ?>
                                        <div class="mt-2 small text-success"><i class="fas fa-check-circle"></i> มี QR Code แล้ว</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <h6 class="fw-bold text-muted border-bottom pb-2 mb-3 mt-4">เปลี่ยนรหัสผ่าน (ถ้าต้องการ)</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <input type="password" name="old_password" class="form-control bg-light" placeholder="รหัสผ่านเดิม">
                            </div>
                            <div class="col-md-4">
                                <input type="password" name="new_password" class="form-control bg-light" placeholder="รหัสผ่านใหม่">
                            </div>
                            <div class="col-md-4">
                                <input type="password" name="confirm_password" class="form-control bg-light" placeholder="ยืนยันรหัสผ่านใหม่">
                            </div>
                        </div>

                        <div class="d-grid mt-5">
                            <button type="submit" class="btn btn-nia py-3 fw-bold shadow-sm">
                                <i class="fas fa-save me-2"></i> บันทึกการเปลี่ยนแปลง
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>