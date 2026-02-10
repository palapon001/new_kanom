<?php
session_start();
require_once '../config.php';
require_once '../function.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// ค้นหา
$search = $_GET['q'] ?? '';
$sql = "SELECT * FROM users WHERE (fullname LIKE ? OR shop_name LIKE ? OR email LIKE ?) ORDER BY role ASC, id DESC";
$users = select($sql, ["%$search%", "%$search%", "%$search%"]);

$theme = $config['theme'];
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-purple mb-0">จัดการสมาชิก</h3>
        <form class="d-flex" action="users_manage.php" method="GET">
            <input class="form-control me-2" type="search" name="q" placeholder="ค้นหาชื่อ/อีเมล..." value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-outline-purple" type="submit">ค้นหา</button>
        </form>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Role</th>
                        <th>ข้อมูลสมาชิก</th>
                        <th>ติดต่อ</th>
                        <th>สถานะ</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr class="<?= $u['role']=='admin' ? 'bg-light' : '' ?>">
                        <td class="ps-4">
                            <?php if($u['role']=='admin'): ?>
                                <span class="badge bg-dark">ADMIN</span>
                            <?php elseif($u['role']=='shop'): ?>
                                <span class="badge bg-warning">SHOP</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">USER</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="fw-bold"><?= htmlspecialchars($u['shop_name'] ?? $u['fullname']) ?></div>
                            <?php if($u['role']=='shop'): ?>
                                <small class="text-muted">เจ้าของ: <?= htmlspecialchars($u['fullname']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="small">
                            <div><i class="fas fa-envelope me-1"></i> <?= htmlspecialchars($u['email']) ?></div>
                            <div><i class="fas fa-phone me-1"></i> <?= htmlspecialchars($u['phone']) ?></div>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success">Active</span>
                        </td>
                        <td class="text-end pe-4">
                            <?php if($u['role'] !== 'admin'): ?>
                                <a href="../process/admin_process.php?action=delete&id=<?= $u['id'] ?>" 
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('ยืนยันที่จะลบสมาชิกคนนี้? ข้อมูลร้านค้าและสินค้าจะหายไปทั้งหมด!');">
                                    <i class="fas fa-trash-alt"></i> ลบ
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>