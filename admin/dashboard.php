<?php
session_start();
require_once '../config.php';
require_once '../function.php';

// เช็คสิทธิ์ Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// 1. ดึงสถิติรวม
$stats = [
    'users' => selectOne("SELECT COUNT(*) as c FROM users WHERE role = 'user'")['c'],
    'shops' => selectOne("SELECT COUNT(*) as c FROM users WHERE role = 'shop'")['c'],
    'orders' => selectOne("SELECT COUNT(*) as c FROM orders WHERE status = 'completed'")['c'],
    'income' => selectOne("SELECT SUM(total_amount) as s FROM orders WHERE status = 'completed'")['s'] ?? 0
];

// 2. ร้านค้าที่สมัครใหม่ล่าสุด (รอตรวจสอบ)
$new_shops = select("SELECT * FROM users WHERE role = 'shop' ORDER BY created_at DESC LIMIT 5");

$theme = $config['theme'];
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    <h2 class="fw-bold text-purple mb-4"><i class="fas fa-user-shield me-2"></i>ผู้ดูแลระบบ (Super Admin)</h2>

    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card bg-primary text-white h-100 shadow-sm border-0">
                <div class="card-body">
                    <h6 class="opacity-75">รายได้หมุนเวียนในระบบ</h6>
                    <h3 class="fw-bold">฿<?= number_format($stats['income']) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-white h-100 shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">ร้านค้าทั้งหมด</h6>
                    <h3 class="fw-bold text-purple"><?= number_format($stats['shops']) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-white h-100 shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">สมาชิกทั่วไป</h6>
                    <h3 class="fw-bold text-purple"><?= number_format($stats['users']) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-white h-100 shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">ออเดอร์สำเร็จ</h6>
                    <h3 class="fw-bold text-success"><?= number_format($stats['orders']) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0 text-purple">ร้านค้าล่าสุด</h5>
        </div>
        <div class="card-body p-0">
            <table class="table align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>ชื่อร้าน</th>
                        <th>เจ้าของ</th>
                        <th>วันที่สมัคร</th>
                        <th class="text-end pe-4">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($new_shops as $s): ?>
                    <tr>
                        <td class="ps-4">#<?= $s['id'] ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($s['shop_name']) ?></td>
                        <td><?= htmlspecialchars($s['fullname']) ?></td>
                        <td class="small text-muted"><?= date('d/m/Y', strtotime($s['created_at'])) ?></td>
                        <td class="text-end pe-4">
                            <a href="users_manage.php" class="btn btn-sm btn-outline-primary">ตรวจสอบ</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>