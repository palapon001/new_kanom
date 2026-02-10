<?php
session_start();
require_once 'config.php';

// ถ้าล็อกอินอยู่แล้ว ให้เด้งไป Dashboard หรือหน้าแรกเลย
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'shop') {
        header("Location: shop/dashboard.php");
    } else {
        header("Location: index.php");
    }
    exit();
}

// สร้าง URL สำหรับปุ่ม LINE Login
$line_client_id = $config['services']['line']['client_id'];
$line_callback_url = urlencode($config['services']['line']['callback_url']);
$line_state = bin2hex(random_bytes(16)); // สร้างรหัสสุ่มเพื่อความปลอดภัย
$_SESSION['line_state'] = $line_state; // เก็บไว้เช็คตอน Callback

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
                <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger small fade show">
                    <i class="fas fa-exclamation-circle me-1"></i> <?= $_SESSION['error'] ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form action="process/login_process.php" method="POST">
                
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">อีเมล</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                        <input type="email" name="email" class="form-control bg-light border-start-0" placeholder="user@example.com" required>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between">
                        <label class="form-label text-muted small fw-bold">รหัสผ่าน</label>
                        </div>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                        <input type="password" name="password" class="form-control bg-light border-start-0" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-nia btn-lg shadow-sm fw-bold">
                        เข้าสู่ระบบ
                    </button>
                </div>

                <div class="d-flex align-items-center mb-3">
                    <hr class="flex-grow-1 text-muted opacity-25">
                    <span class="px-2 small text-muted">หรือ</span>
                    <hr class="flex-grow-1 text-muted opacity-25">
                </div>

                <div class="d-grid mb-4">
                    <a href="<?= $line_login_url ?>" class="btn btn-success text-white btn-lg shadow-sm fw-bold" style="background-color: #00B900; border: none;">
                        <i class="fab fa-line me-2 fa-lg"></i> เข้าสู่ระบบด้วย LINE
                    </a>
                </div>

                <div class="text-center text-muted small">
                    ยังไม่มีบัญชีสมาชิก? 
                    <a href="register.php" class="fw-bold text-decoration-none" style="color: <?= $config['theme']['colors']['primary'] ?>;">
                        สมัครสมาชิกใหม่
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>