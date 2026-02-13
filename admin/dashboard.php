<?php
session_start();
require_once '../config.php';
require_once '../function.php';

// 1. ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// 2. ดึงข้อมูลสถิติ (Statistics)
$count_users = selectOne("SELECT COUNT(*) as c FROM users WHERE role = 'user'")['c'];
$count_shops = selectOne("SELECT COUNT(*) as c FROM users WHERE role = 'shop'")['c'];
$count_orders = selectOne("SELECT COUNT(*) as c FROM orders")['c'];
$total_income = selectOne("SELECT SUM(total_amount) as s FROM orders WHERE status = 'completed'")['s'] ?? 0;

// 3. ดึงออเดอร์ล่าสุด 5 รายการ
$recent_orders = select("SELECT o.*, u.fullname, s.shop_name 
                          FROM orders o 
                          JOIN users u ON o.customer_id = u.id
                          JOIN users s ON o.shop_id = s.id
                          ORDER BY o.created_at DESC LIMIT 5");

$theme = $config['theme'];
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-purple mb-0"><i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard</h2>
            <p class="text-muted small mb-0">ระบบจัดการหลังบ้าน</p>
        </div>
        </div>

    <div class="row g-3 mb-5">
        <div class="col-md-3">
            <a href="users_manage.php?role=user" class="card border-0 shadow-sm h-100 text-decoration-none hover-up bg-white">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 me-3">
                        <i class="fas fa-users-cog fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-0">จัดการผู้ใช้งาน</h6>
                        <small class="text-muted">อนุมัติ/ระงับ User</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="shops_manage.php?role=shop" class="card border-0 shadow-sm h-100 text-decoration-none hover-up bg-white">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 text-dark rounded-circle p-3 me-3">
                        <i class="fas fa-store-alt fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-0">จัดการร้านค้า</h6>
                        <small class="text-muted">ตรวจสอบร้านใหม่</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="central_ingredients.php" class="card border-0 shadow-sm h-100 text-decoration-none hover-up bg-purple text-white" 
               style="background: linear-gradient(135deg, <?= $theme['colors']['primary'] ?>, <?= $theme['colors']['secondary'] ?>);">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-white bg-opacity-25 text-white rounded-circle p-3 me-3">
                        <i class="fas fa-tags fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-white mb-0">ราคากลางวัตถุดิบ</h6>
                        <small class="text-white-50">กำหนดชนิดมาตรฐาน</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="report.php" class="card border-0 shadow-sm h-100 text-decoration-none hover-up bg-white">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 text-success rounded-circle p-3 me-3">
                        <i class="fas fa-chart-line fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-0">รายงานสรุป</h6>
                        <small class="text-muted">ดูยอดขายรายเดือน</small>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <h5 class="fw-bold text-dark mb-3"><i class="fas fa-chart-pie me-2 text-muted"></i>ภาพรวมสถิติ</h5>
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white border-start border-4 border-primary">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between">
                        <div>
                            <span class="text-muted small text-uppercase fw-bold">ลูกค้าทั้งหมด</span>
                            <h3 class="fw-bold text-primary mt-1"><?= number_format($count_users) ?></h3>
                        </div>
                        <i class="fas fa-users fa-2x text-primary opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white border-start border-4 border-warning">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between">
                        <div>
                            <span class="text-muted small text-uppercase fw-bold">ร้านค้า</span>
                            <h3 class="fw-bold text-warning mt-1"><?= number_format($count_shops) ?></h3>
                        </div>
                        <i class="fas fa-store fa-2x text-warning opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white border-start border-4 border-info">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between">
                        <div>
                            <span class="text-muted small text-uppercase fw-bold">ออเดอร์</span>
                            <h3 class="fw-bold text-info mt-1"><?= number_format($count_orders) ?></h3>
                        </div>
                        <i class="fas fa-clipboard-list fa-2x text-info opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white border-start border-4 border-success">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between">
                        <div>
                            <span class="text-muted small text-uppercase fw-bold">รายได้รวม</span>
                            <h3 class="fw-bold text-success mt-1">฿<?= number_format($total_income) ?></h3>
                        </div>
                        <i class="fas fa-coins fa-2x text-success opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold text-purple mb-0"><i class="fas fa-history me-2"></i>รายการสั่งซื้อล่าสุด (All Shops)</h5>
            <a href="orders_manage.php" class="btn btn-sm btn-outline-purple rounded-pill">ดูทั้งหมด</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0 table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">#Order</th>
                            <th>ลูกค้า</th>
                            <th>ร้านค้า</th>
                            <th>ยอดรวม</th>
                            <th>สถานะ</th>
                            <th>เวลา</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-primary">#<?= $order['order_no'] ?></td>
                                <td><?= htmlspecialchars($order['fullname']) ?></td>
                                <td><i class="fas fa-store text-warning me-1"></i> <?= htmlspecialchars($order['shop_name']) ?></td>
                                <td class="fw-bold">฿<?= number_format($order['total_amount']) ?></td>
                                <td>
                                    <?php 
                                        $st_class = match($order['status']) {
                                            'pending' => 'warning text-dark',
                                            'paid' => 'info text-dark',
                                            'completed' => 'success',
                                            'cancelled' => 'danger',
                                            default => 'secondary'
                                        };
                                    ?>
                                    <span class="badge bg-<?= $st_class ?> rounded-pill"><?= ucfirst($order['status']) ?></span>
                                </td>
                                <td class="text-muted small"><?= date('d/m H:i', strtotime($order['created_at'])) ?></td>
                                <td class="text-end pe-4">
                                    <a href="order_view.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-light text-purple">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<style>
    .hover-up:hover { transform: translateY(-5px); transition: 0.3s; cursor: pointer; }
    .btn-outline-purple { color: var(--nia-purple); border-color: var(--nia-purple); }
    .btn-outline-purple:hover { background-color: var(--nia-purple); color: white; }
</style>

<?php include '../includes/footer.php'; ?>