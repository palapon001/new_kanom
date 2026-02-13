<?php
session_start();
require_once '../config.php';
require_once '../function.php';

// 1. ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// 2. รับค่า Config จาก URL (รองรับ user, shop, admin)
$allowed_roles = ['user', 'shop', 'admin'];
$current_role = isset($_GET['role']) && in_array($_GET['role'], $allowed_roles) ? $_GET['role'] : 'user';

$search_q = $_GET['q'] ?? '';
$sort_date = $_GET['sort'] ?? 'newest';

// 3. Process: ลบผู้ใช้
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // 🛡️ ป้องกันการลบตัวเอง (สำคัญมาก!)
    if ($id == $_SESSION['user_id']) {
        echo "<script>alert('❌ ไม่สามารถลบบัญชีตัวเองได้'); window.location.href='users_manage.php?role=admin';</script>";
        exit();
    }
    
    // ลบข้อมูล
    delete('users', "id = ?", [$id]);
    header("Location: users_manage.php?role=$current_role&msg=deleted");
    exit();
}

// 4. สร้าง SQL Query แบบ Dynamic
$sql = "SELECT * FROM users WHERE role = ?";
$params = [$current_role];

// กรองคำค้นหา
if (!empty($search_q)) {
    $sql .= " AND (fullname LIKE ? OR email LIKE ? OR phone LIKE ? OR shop_name LIKE ?)";
    $params[] = "%$search_q%";
    $params[] = "%$search_q%";
    $params[] = "%$search_q%";
    $params[] = "%$search_q%";
}

// เรียงลำดับ
if ($sort_date == 'oldest') {
    $sql .= " ORDER BY created_at ASC";
} else {
    $sql .= " ORDER BY created_at DESC"; // Default
}

// ดึงข้อมูล
$users = select($sql, $params);

// ตั้งชื่อหัวข้อหน้า
if ($current_role == 'shop') $page_title = 'จัดการร้านค้า (Shops)';
elseif ($current_role == 'admin') $page_title = 'จัดการผู้ดูแล (Admins)';
else $page_title = 'จัดการผู้ซื้อ (Users)';

$theme = $config['theme'];
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-purple mb-0"><i class="fas fa-users-cog me-2"></i><?= $page_title ?></h3>
            <p class="text-muted small mb-0">บริหารจัดการบัญชีในระบบ</p>
        </div>
        
        <?php if($current_role == 'admin'): ?>
        <a href="admin_add.php" class="btn btn-nia shadow-sm rounded-pill fw-bold">
            <i class="fas fa-user-plus me-2"></i>เพิ่มผู้ดูแล
        </a>
        <?php endif; ?>
    </div>

    <ul class="nav nav-pills mb-4 bg-white p-2 rounded-pill shadow-sm d-inline-flex flex-wrap gap-1">
        <li class="nav-item">
            <a class="nav-link rounded-pill px-4 <?= $current_role == 'user' ? 'active bg-purple' : 'text-muted' ?>" 
               href="users_manage.php?role=user">
               <i class="fas fa-user me-2"></i>ผู้ซื้อ
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-pill px-4 <?= $current_role == 'shop' ? 'active bg-purple' : 'text-muted' ?>" 
               href="users_manage.php?role=shop">
               <i class="fas fa-store me-2"></i>ร้านค้า
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-pill px-4 <?= $current_role == 'admin' ? 'active bg-purple' : 'text-muted' ?>" 
               href="users_manage.php?role=admin">
               <i class="fas fa-user-shield me-2"></i>ผู้ดูแล
            </a>
        </li>
    </ul>

    <div class="card border-0 shadow-sm mb-4 rounded-4 bg-white">
        <div class="card-body p-3">
            <form action="" method="GET" class="row g-2 align-items-center">
                <input type="hidden" name="role" value="<?= $current_role ?>">
                
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="q" class="form-control bg-light border-0" 
                               placeholder="ค้นหาชื่อ, อีเมล, เบอร์โทร<?= $current_role=='shop'?', ชื่อร้าน':'' ?>..." 
                               value="<?= htmlspecialchars($search_q) ?>">
                    </div>
                </div>

                <div class="col-md-3">
                    <select name="sort" class="form-select bg-light border-0" onchange="this.form.submit()">
                        <option value="newest" <?= $sort_date=='newest'?'selected':'' ?>>สมัครล่าสุด</option>
                        <option value="oldest" <?= $sort_date=='oldest'?'selected':'' ?>>สมัครนานสุด</option>
                    </select>
                </div>

                <div class="col-md-3 d-grid">
                    <a href="users_manage.php?role=<?= $current_role ?>" class="btn btn-outline-secondary border-0"><i class="fas fa-undo me-1"></i> ล้างค่า</a>
                </div>
            </form>
        </div>
    </div>

    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
        <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4 d-flex align-items-center">
            <i class="fas fa-check-circle me-2 fs-4"></i>
            <div>ลบข้อมูลเรียบร้อยแล้ว</div>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3">ข้อมูลบัญชี</th>
                            <th>ช่องทางติดต่อ</th>
                            <?php if($current_role == 'shop'): ?>
                                <th>ข้อมูลร้านค้า</th>
                            <?php endif; ?>
                            <th>วันที่สมัคร</th>
                            <th class="text-end pe-4">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($users) > 0): ?>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <?php 
                                                $profile = !empty($u['profile_image']) ? '../uploads/profiles/'.$u['profile_image'] : 'https://placehold.co/100x100?text='.strtoupper(substr($current_role,0,1));
                                                if(!filter_var($profile, FILTER_VALIDATE_URL) && strpos($profile, 'http') === false && strpos($profile, '../') === false) {
                                                    $profile = '../uploads/profiles/' . $u['profile_image'];
                                                }
                                            ?>
                                            <div class="position-relative">
                                                <img src="<?= $profile ?>" class="rounded-circle shadow-sm me-3 border" width="50" height="50" style="object-fit: cover;" onerror="this.src='https://placehold.co/100x100?text=User'">
                                                <?php if($u['id'] == $_SESSION['user_id']): ?>
                                                    <span class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle p-1" style="width: 15px; height: 15px;"></span>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">
                                                    <?= htmlspecialchars($u['fullname']) ?>
                                                    <?php if($u['id'] == $_SESSION['user_id']): ?>
                                                        <span class="badge bg-success bg-opacity-10 text-success ms-1" style="font-size: 0.6rem;">YOU</span>
                                                    <?php endif; ?>
                                                </div>
                                                <small class="text-muted text-break" style="font-size: 0.8rem;">ID: #<?= $u['id'] ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column small">
                                            <span class="mb-1"><i class="fas fa-envelope text-secondary me-2" style="width:15px;"></i><?= htmlspecialchars($u['email']) ?></span>
                                            <span><i class="fas fa-phone text-secondary me-2" style="width:15px;"></i><?= htmlspecialchars($u['phone'] ?? '-') ?></span>
                                            <?php if(!empty($u['line_id'])): ?>
                                                <span class="text-success mt-1"><i class="fab fa-line me-2" style="width:15px;"></i>Line Login</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    
                                    <?php if($current_role == 'shop'): ?>
                                        <td>
                                            <div class="fw-bold text-purple"><i class="fas fa-store me-1"></i> <?= htmlspecialchars($u['shop_name']) ?></div>
                                            <small class="text-muted d-block mt-1">
                                                <i class="fas fa-university me-1"></i> <?= htmlspecialchars($u['bank_name']) ?> (<?= htmlspecialchars($u['bank_account']) ?>)
                                            </small>
                                        </td>
                                    <?php endif; ?>

                                    <td>
                                        <span class="badge bg-light text-dark border fw-normal">
                                            <i class="far fa-calendar-alt me-1"></i> <?= date('d/m/Y', strtotime($u['created_at'])) ?>
                                        </span>
                                    </td>
                                    
                                    <td class="text-end pe-4">
                                        <?php if($u['id'] == $_SESSION['user_id']): ?>
                                            <span class="text-muted small fst-italic">กำลังใช้งาน</span>
                                        <?php else: ?>
                                            <a href="?role=<?= $current_role ?>&action=delete&id=<?= $u['id'] ?>" 
                                               class="btn btn-sm btn-light text-danger shadow-sm hover-scale" 
                                               onclick="return confirm('⚠️ คำเตือน!\n\nยืนยันที่จะลบบัญชีนี้หรือไม่?');"
                                               title="ลบผู้ใช้งาน">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?= $current_role == 'shop' ? 5 : 4 ?>" class="text-center py-5 text-muted">
                                    <i class="fas fa-search fa-3x mb-3 opacity-25"></i><br>
                                    ไม่พบข้อมูลตามเงื่อนไขที่ค้นหา
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<style>
    .bg-purple { background-color: var(--nia-purple) !important; color: white !important; }
    .nav-pills .nav-link.active { background-color: var(--nia-purple); box-shadow: 0 4px 6px rgba(111, 66, 193, 0.3); }
    .nav-pills .nav-link { color: #6c757d; font-weight: bold; transition: 0.3s; }
    .nav-pills .nav-link:hover { background-color: #f8f9fa; color: var(--nia-purple); }
    .hover-scale:hover { transform: scale(1.1); transition: 0.2s; }
</style>

<?php include '../includes/footer.php'; ?>