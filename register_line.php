<?php
session_start();
require_once 'config.php';
require_once 'function.php';

if (!isset($_SESSION['line_profile'])) {
    header("Location: login.php");
    exit();
}

$line = $_SESSION['line_profile'];
$theme = $config['theme'];
include 'includes/header.php';
?>

<style>
    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
    }
    .card-register {
        border: none;
        border-radius: 24px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        backdrop-filter: blur(10px);
        background: rgba(255, 255, 255, 0.9);
    }
    .line-avatar {
        width: 110px;
        height: 110px;
        border: 5px solid white;
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }
    .line-avatar:hover {
        transform: scale(1.05);
    }
    .role-selection .btn-check:checked + .btn-outline-custom {
        background-color: var(--nia-purple);
        color: white;
        border-color: var(--nia-purple);
        box-shadow: 0 8px 15px rgba(111, 66, 193, 0.3);
        transform: translateY(-2px);
    }
    .btn-outline-custom {
        border: 2px solid #eee;
        background: white;
        color: #666;
        transition: all 0.3s ease;
        border-radius: 20px;
    }
    .btn-outline-custom:hover {
        border-color: var(--nia-purple);
        color: var(--nia-purple);
    }
    .form-control-custom {
        border-radius: 12px;
        padding: 12px 20px;
        border: 2px solid #f0f0f0;
        transition: all 0.3s;
    }
    .form-control-custom:focus {
        border-color: var(--nia-purple);
        box-shadow: 0 0 0 0.25 row rgba(111, 66, 193, 0.1);
    }
    .fade-in {
        animation: fadeIn 0.5s ease-in-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            
            <div class="text-center mb-4">
                <img src="assets/images/logo.png" height="60" class="mb-3"> </div>

            <div class="card card-register overflow-hidden">
                <div class="py-4 text-center" style="background: linear-gradient(45deg, #00b900, #009900);">
                    <h5 class="mb-0 fw-bold text-white"><i class="fab fa-line me-2"></i>LINE Connected</h5>
                </div>

                <div class="card-body p-4 p-lg-5">
                    <div class="text-center mb-4">
                        <img src="<?= $line['pictureUrl'] ?>" class="rounded-circle line-avatar mb-3">
                        <h3 class="fw-bold text-dark mb-1">ยินดีต้อนรับ</h3>
                        <p class="text-muted">คุณ <?= htmlspecialchars($line['displayName']) ?></p>
                    </div>

                    <form action="process/register_line_process.php" method="POST">
                        
                        <div class="mb-4 role-selection">
                            <label class="form-label fw-bold text-dark mb-3">คุณจะใช้งานในฐานะใด?</label>
                            <div class="row g-3">
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="role" id="role_user" value="user" checked onchange="toggleShopInput(false)">
                                    <label class="btn btn-outline-custom w-100 py-3" for="role_user">
                                        <i class="fas fa-user-tag fs-3 mb-2 d-block"></i>
                                        <span class="fw-bold">ผู้ซื้อ</span>
                                    </label>
                                </div>
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="role" id="role_shop" value="shop" onchange="toggleShopInput(true)">
                                    <label class="btn btn-outline-custom w-100 py-3" for="role_shop">
                                        <i class="fas fa-store fs-3 mb-2 d-block"></i>
                                        <span class="fw-bold">ร้านค้า</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div id="shop_info_section" style="display: none;" class="fade-in mb-3">
                                <label class="form-label small fw-bold text-muted">ชื่อร้านค้าของคุณ</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0" style="border-radius: 12px 0 0 12px;"><i class="fas fa-store text-muted"></i></span>
                                    <input type="text" name="shop_name" class="form-control form-control-custom border-start-0" placeholder="ระบุชื่อร้านค้า">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted">เบอร์โทรศัพท์ติดต่อ</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0" style="border-radius: 12px 0 0 12px;"><i class="fas fa-phone text-muted"></i></span>
                                    <input type="text" name="phone" class="form-control form-control-custom border-start-0" required placeholder="08x-xxx-xxxx">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success btn-lg w-100 rounded-pill fw-bold shadow-sm mt-2 py-3" style="background: #00b900; border: none;">
                            เริ่มใช้งานระบบเลย <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                        
                        <div class="text-center mt-4">
                            <a href="logout.php" class="text-decoration-none text-muted small">ไม่ใช่คุณ? ยกเลิกการเชื่อมต่อ</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <p class="text-center mt-4 text-muted small">
                &copy; <?= date('Y') ?> Kanom Platform. All rights reserved.
            </p>
        </div>
    </div>
</div>

<script>
function toggleShopInput(show) {
    const section = document.getElementById('shop_info_section');
    const shopInput = document.querySelector('input[name="shop_name"]');
    
    if(show) {
        section.style.display = 'block';
        shopInput.required = true;
        shopInput.focus();
    } else {
        section.style.display = 'none';
        shopInput.required = false;
    }
}
</script>

<?php include 'includes/footer.php'; ?>