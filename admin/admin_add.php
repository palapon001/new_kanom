<?php
session_start();
require_once '../config.php';
require_once '../function.php';

// 1. ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$error = '';

// 2. Process: บันทึกข้อมูล
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($fullname) || empty($email) || empty($password)) {
        $error = 'กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน';
    } elseif ($password !== $confirm_password) {
        $error = 'รหัสผ่านยืนยันไม่ตรงกัน';
    } else {
        $check = selectOne("SELECT id FROM users WHERE email = ?", [$email]);
        if ($check) {
            $error = 'อีเมลนี้ถูกใช้งานแล้วในระบบ';
        } else {
            $data = [
                'role' => 'admin',
                'email' => $email,
                'password' => md5($password),
                'fullname' => $fullname,
                'phone' => $phone,
                'created_at' => date('Y-m-d H:i:s')
            ];

            if (insert('users', $data)) {
                // ✅ แก้ไขตรงนี้: ใช้ Session แทน Alert
                $_SESSION['success'] = 'เพิ่มผู้ดูแลระบบเรียบร้อยแล้ว';
                header("Location: users_manage.php?role=admin");
                exit();
            } else {
                $error = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล';
            }
        }
    }
}

$theme = $config['theme'];
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            
            <div class="mb-4">
                <a href="users_manage.php?role=admin" class="text-decoration-none text-muted small fw-bold hover-scale d-inline-block">
                    <i class="fas fa-arrow-left me-1"></i> ย้อนกลับ
                </a>
            </div>

            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-purple text-white py-3" 
                     style="background: linear-gradient(135deg, <?= $theme['colors']['primary'] ?>, <?= $theme['colors']['secondary'] ?>);">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-user-shield me-2"></i>เพิ่มผู้ดูแลระบบ (New Admin)</h5>
                </div>
                <div class="card-body p-4">

                    <?php if($error): ?>
                        <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4">
                            <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                            <input type="text" name="fullname" class="form-control bg-light border-0" required placeholder="เช่น Admin System" value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>">
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted">อีเมล (Login) <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control bg-light border-0" required placeholder="admin@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted">เบอร์โทรศัพท์</label>
                                <input type="text" name="phone" class="form-control bg-light border-0" placeholder="08x-xxx-xxxx" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted">รหัสผ่าน <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control bg-light border-0" required placeholder="กำหนดรหัสผ่าน">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-muted">ยืนยันรหัสผ่าน <span class="text-danger">*</span></label>
                            <input type="password" name="confirm_password" class="form-control bg-light border-0" required placeholder="กรอกรหัสผ่านอีกครั้ง">
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-nia btn-lg fw-bold shadow-sm rounded-pill">
                                <i class="fas fa-save me-2"></i> บันทึกข้อมูล
                            </button>
                            <a href="users_manage.php?role=admin" class="btn btn-light btn-lg rounded-pill text-muted">ยกเลิก</a>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    .hover-scale:hover { transform: translateX(-5px); transition: 0.2s; }
    .bg-purple { background-color: var(--nia-purple); }
</style>

<?php include '../includes/footer.php'; ?>