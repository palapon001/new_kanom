<?php
session_start();
require_once 'config.php';

// ถ้าล็อกอินอยู่แล้ว ให้เด้งไปตามบทบาท
if (isset($_SESSION['user_id'])) {
    header("Location: " . ($_SESSION['role'] == 'shop' ? 'shop/dashboard.php' : 'index.php'));
    exit();
}

// สร้าง URL สำหรับปุ่ม LINE Login
$line_client_id = $config['services']['line']['client_id'];
$line_callback_url = urlencode($config['services']['line']['callback_url']);
$line_state = bin2hex(random_bytes(16));
$_SESSION['line_state'] = $line_state;

$line_login_url = "https://access.line.me/oauth2/v2.1/authorize?response_type=code&client_id={$line_client_id}&redirect_uri={$line_callback_url}&state={$line_state}&scope=profile%20openid";

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container d-flex align-items-center justify-content-center flex-grow-1 py-5" 
     style="min-height: 80vh; background-image: url('https://www.transparenttextures.com/patterns/cubes.png'); background-color: #f8f9fa;">
    
    <div class="card border-0 shadow-lg p-3 p-md-4" 
         style="max-width: 450px; width: 100%; border-radius: <?= $config['theme']['ui']['radius'] ?>;">
        
        <div class="card-body">
            <div class="text-center mb-4">
                <div class="bg-white text-purple rounded-circle d-inline-flex justify-content-center align-items-center mb-3 shadow-sm" 
                     style="width: 70px; height: 70px;">
                    <i class="fas fa-crown fa-2x" style="color: <?= $config['theme']['colors']['primary'] ?>;"></i>
                </div>
                <h3 class="fw-bold" style="color: <?= $config['theme']['colors']['secondary'] ?>;">เข้าสู่ระบบ</h3>
                <p class="text-muted small">KanomMuangPhet Smart Platform</p>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger small fade show mb-4 rounded-3">
                    <i class="fas fa-exclamation-circle me-1"></i> <?= $_SESSION['error'] ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form action="process/login_process.php" method="POST" id="loginForm">
                
                <div class="mb-4">
                    <label class="form-label text-muted small fw-bold">เบอร์โทรศัพท์ หรือ อีเมล</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-user text-muted" id="icon_identity"></i>
                        </span>
                        <input type="text" name="identity" id="identity_input" class="form-control bg-light border-start-0 shadow-none" 
                               placeholder="กรอกเบอร์โทร หรือ อีเมล..." required autofocus autocomplete="off">
                    </div>
                    <div id="identity_info" class="form-text small mt-2">ระบุข้อมูลที่ลงทะเบียนไว้เพื่อเข้าสู่ระบบ</div>
                </div>

                <div id="password_section" style="display: none;" class="mb-4 animate__animated animate__fadeIn">
                    <label class="form-label text-danger small fw-bold"><i class="fas fa-key me-1"></i> รหัสผ่าน</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-danger"></i></span>
                        <input type="password" name="password" id="password_input" class="form-control bg-light border-start-0 shadow-none" 
                               placeholder="ระบุรหัสผ่านของคุณ">
                    </div>
                    <div id="password_hint" class="form-text text-danger small mt-2">กรุณาระบุรหัสผ่านเพื่อยืนยันตัวตน</div>
                </div>

                <div class="d-grid mb-4">
                    <button type="submit" class="btn btn-primary shadow-sm fw-bold py-3">
                        <span id="btn_text">เข้าสู่ระบบทันที</span> <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                </div>

                <div class="d-flex align-items-center mb-4">
                    <hr class="flex-grow-1 text-muted opacity-25">
                    <span class="px-2 small text-muted">หรือ</span>
                    <hr class="flex-grow-1 text-muted opacity-25">
                </div>

                <div class="d-grid mb-4">
                    <a href="<?= $line_login_url ?>" class="btn btn-success text-white shadow-sm fw-bold py-3" style="background-color: #00B900; border: none; border-radius: 12px;">
                        <i class="fab fa-line me-2 fa-lg"></i> เข้าสู่ระบบด้วย LINE
                    </a>
                </div>

                <div class="text-center text-muted small">
                    ยังไม่มีบัญชีสมาชิก? 
                    <a href="register.php" class="fw-bold text-decoration-none" style="color: <?= $config['theme']['colors']['primary'] ?>;">สมัครใหม่ที่นี่</a>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
const inputField = document.getElementById('identity_input');
const iconField = document.getElementById('icon_identity');
const passSection = document.getElementById('password_section');
const passInput = document.getElementById('password_input');
const btnText = document.getElementById('btn_text');
const passHint = document.getElementById('password_hint');

inputField.addEventListener('input', function() {
    const val = this.value.trim();

    // 1. เช็คว่าเป็น Email หรือไม่ (มีเครื่องหมาย @)
    if (val.includes('@')) {
        // 📧 กรณีเป็น Email: บังคับใส่รหัสผ่านเสมอ
        iconField.className = 'fas fa-envelope text-purple';
        showPassword(true, 'อีเมลต้องใช้รหัสผ่านในการเข้าสู่ระบบ');
        return; 
    }

    // 2. เช็คว่าเป็นเบอร์โทรศัพท์หรือไม่ (ตัวเลขล้วน)
    const phoneClean = val.replace(/[^0-9]/g, '');
    if (phoneClean.length > 0) {
        iconField.className = 'fas fa-phone text-muted';
    } else {
        iconField.className = 'fas fa-user text-muted';
    }

    if (phoneClean.length === 10 && !val.includes('@')) {
        // 📱 กรณีเป็นเบอร์โทร 10 หลัก: เช็ค Role ผ่าน API
        iconField.className = 'fas fa-phone text-success';
        
        fetch('api/check_role.php?phone=' + phoneClean)
            .then(response => response.json())
            .then(data => {
                if (data.role === 'shop' || data.role === 'admin') {
                    // Shop/Admin -> ต้องใส่รหัส
                    showPassword(true, 'บัญชีร้านค้า/ผู้ดูแล ต้องระบุรหัสผ่าน');
                } else {
                    // User ทั่วไป -> ไม่ต้องใส่รหัส
                    showPassword(false);
                }
            })
            .catch(err => console.error(err));
    } else if (!val.includes('@')) {
        // ยังพิมพ์ไม่เสร็จ หรือไม่ใช่ทั้งเมลและเบอร์ -> ซ่อนรหัสไปก่อน
        showPassword(false);
    }
});

function showPassword(show, hintText = '') {
    if (show) {
        passSection.style.display = 'block';
        passInput.required = true;
        btnText.innerText = 'ยืนยันเพื่อเข้าสู่ระบบ';
        if(hintText) passHint.innerText = hintText;
    } else {
        passSection.style.display = 'none';
        passInput.required = false;
        btnText.innerText = 'เข้าสู่ระบบทันที';
    }
}
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<?php include 'includes/footer.php'; ?>