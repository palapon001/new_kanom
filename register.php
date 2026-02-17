<?php
require_once 'config.php';
// ถ้าล็อกอินอยู่แล้ว ให้เด้งออกไปหน้าแรก
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container py-5 d-flex justify-content-center align-items-center" 
     style="min-height: 80vh; background-image: url('https://www.transparenttextures.com/patterns/cubes.png');">
    
    <div class="card border-0 shadow-lg p-3 p-md-4" style="max-width: 500px; width: 100%; border-radius: 16px;">
        <div class="card-body">
            
            <h3 class="text-center fw-bold text-purple mb-2">สมัครสมาชิกใหม่</h3>
            <p class="text-center text-muted small mb-4">เข้าร่วมเป็นส่วนหนึ่งของอาณาจักรขนมหวานเมืองเพชร</p>

            <div class="bg-light p-1 rounded-pill d-flex mb-4 border relative">
                <button type="button" class="btn w-50 rounded-pill fw-bold transition-all active-role" id="btn-user" onclick="setRole('user')">
                    <i class="fas fa-user me-1"></i> ผู้ซื้อทั่วไป
                </button>
                <button type="button" class="btn w-50 rounded-pill fw-bold text-muted transition-all" id="btn-shop" onclick="setRole('shop')">
                    <i class="fas fa-store me-1"></i> ร้านค้า
                </button>
            </div>
            
            <form action="process/register_process.php" method="POST" enctype="multipart/form-data" id="registerForm">
                
                <input type="hidden" name="role" id="role_input" value="user">

                <div class="mb-3 animate__animated animate__fadeIn" id="shop_name_group" style="display: none;">
                    <label class="form-label text-purple small fw-bold">ชื่อร้านค้า <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-store text-purple"></i></span>
                        <input type="text" name="shop_name" id="shop_name" class="form-control bg-light border-start-0 shadow-none" placeholder="ระบุชื่อร้านของคุณ">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold" id="fullname_label">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-id-card text-muted"></i></span>
                        <input type="text" name="fullname" class="form-control bg-light border-start-0 shadow-none" required placeholder="ระบุชื่อและนามสกุล">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">เบอร์โทรศัพท์ <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-phone text-muted"></i></span>
                        <input type="tel" name="phone" class="form-control bg-light border-start-0 shadow-none" required placeholder="08xxxxxxxx" maxlength="10">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">อีเมล (ถ้ามี)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                        <input type="email" name="email" class="form-control bg-light border-start-0 shadow-none" placeholder="name@example.com">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small fw-bold">รหัสผ่าน <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control bg-light" required placeholder="กำหนดรหัสผ่าน">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small fw-bold">ยืนยันรหัสผ่าน <span class="text-danger">*</span></label>
                        <input type="password" name="confirm_password" class="form-control bg-light" required placeholder="ยืนยันอีกครั้ง">
                    </div>
                </div>
                
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" required id="agree">
                    <label class="form-check-label small text-muted" for="agree">
                        ฉันยอมรับ <a href="#" class="text-decoration-none text-purple">เงื่อนไขการใช้งาน</a> และ <a href="#" class="text-decoration-none text-purple">นโยบายความเป็นส่วนตัว</a>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-nia w-100 py-2 shadow-sm fw-bold">
                    <i class="fas fa-user-plus me-2"></i> <span id="btn_submit_text">สมัครสมาชิกทั่วไป</span>
                </button>
            </form>
            
            <div class="text-center mt-4">
                <small class="text-muted">มีบัญชีอยู่แล้ว? <a href="login.php" class="text-purple fw-bold text-decoration-none">เข้าสู่ระบบที่นี่</a></small>
            </div>
        </div>
    </div>
</div>

<style>
    /* CSS สำหรับปุ่ม Toggle */
    .active-role {
        background-color: var(--nia-purple) !important; /* สีม่วงตาม Theme */
        color: white !important;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .transition-all {
        transition: all 0.3s ease;
    }
    .text-purple { color: #6f42c1; }
    .btn-nia {
        background: linear-gradient(135deg, #E6007E 0%, #2D1F57 100%);
        color: white;
        border: none;
    }
    .btn-nia:hover {
        background: linear-gradient(135deg, #C2006B 0%, #201640 100%);
        color: white;
    }
</style>

<script>
function setRole(role) {
    const roleInput = document.getElementById('role_input');
    const btnUser = document.getElementById('btn-user');
    const btnShop = document.getElementById('btn-shop');
    const shopGroup = document.getElementById('shop_name_group');
    const shopInput = document.getElementById('shop_name');
    const fullnameLabel = document.getElementById('fullname_label');
    const btnSubmitText = document.getElementById('btn_submit_text');

    // ตั้งค่า Value
    roleInput.value = role;

    if (role === 'user') {
        // UI: เปลี่ยนปุ่ม
        btnUser.classList.add('active-role');
        btnUser.classList.remove('text-muted');
        btnShop.classList.remove('active-role');
        btnShop.classList.add('text-muted');

        // Form: ซ่อนช่องชื่อร้าน
        shopGroup.style.display = 'none';
        shopInput.required = false;
        shopInput.value = ''; // เคลียร์ค่า

        // Label
        fullnameLabel.innerHTML = 'ชื่อ-นามสกุล <span class="text-danger">*</span>';
        btnSubmitText.innerText = 'สมัครสมาชิกทั่วไป';

    } else if (role === 'shop') {
        // UI: เปลี่ยนปุ่ม
        btnShop.classList.add('active-role');
        btnShop.classList.remove('text-muted');
        btnUser.classList.remove('active-role');
        btnUser.classList.add('text-muted');

        // Form: แสดงช่องชื่อร้าน
        shopGroup.style.display = 'block';
        shopInput.required = true;

        // Label
        fullnameLabel.innerHTML = 'ชื่อเจ้าของร้าน <span class="text-danger">*</span>';
        btnSubmitText.innerText = 'ลงทะเบียนร้านค้า';
    }
}
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<?php include 'includes/footer.php'; ?>