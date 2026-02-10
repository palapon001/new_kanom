<?php
require_once 'config.php';
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container py-5 d-flex justify-content-center">
    <div class="card border-0 shadow-lg p-4" style="max-width: 500px; width: 100%; border-radius: 16px;">
        <div class="card-body">
            <h3 class="text-center fw-bold text-purple mb-4">ลงทะเบียนร้านค้าใหม่</h3>
            
            <form action="process/register_process.php" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">ชื่อร้านค้า</label>
                    <input type="text" name="shop_name" class="form-control bg-light" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">อีเมล</label>
                    <input type="email" name="email" class="form-control bg-light" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">รหัสผ่าน</label>
                    <input type="password" name="password" class="form-control bg-light" required>
                </div>
                <div class="mb-4">
                    <label class="form-label text-muted small fw-bold">ยืนยันรหัสผ่าน</label>
                    <input type="password" name="confirm_password" class="form-control bg-light" required>
                </div>
                
                <button type="submit" class="btn btn-nia w-100 py-2 shadow-sm fw-bold">ลงทะเบียน</button>
            </form>
            
            <div class="text-center mt-3">
                <small>มีบัญชีอยู่แล้ว? <a href="login.php" class="text-magenta fw-bold">เข้าสู่ระบบ</a></small>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>