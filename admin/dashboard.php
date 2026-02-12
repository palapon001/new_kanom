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
// จำนวนผู้ซื้อ
$count_users = selectOne("SELECT COUNT(*) as c FROM users WHERE role = 'user'")['c'];

// จำนวนร้านค้า
$count_shops = selectOne("SELECT COUNT(*) as c FROM users WHERE role = 'shop'")['c'];

// จำนวนออเดอร์ทั้งหมด
$count_orders = selectOne("SELECT COUNT(*) as c FROM orders")['c'];

// ยอดขายรวมทั้งหมด (เฉพาะที่สถานะ completed)
$total_income = selectOne("SELECT SUM(total_amount) as s FROM orders WHERE status = 'completed'")['s'] ?? 0;

// 3. ดึงออเดอร์ล่าสุด 5 รายการ (Recent Orders)
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
    
    <div class="mb-4">
        <h2 class="fw-bold text-purple"><i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard</h2>
        <p class="text-muted">ภาพรวมของระบบ</p>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-primary bg-opacity-10">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="bg-white rounded-circle p-3 text-primary shadow-sm">
                            <i class="fas fa-users fa-lg"></i>
                        </div>
                        <span class="badge bg-primary rounded-pill">Users</span>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark"><?= number_format($count_users) ?></h3>
                    <small class="text-muted">ลูกค้าทั้งหมด</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-warning bg-opacity-10">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="bg-white rounded-circle p-3 text-warning shadow-sm">
                            <i class="fas fa-store fa-lg"></i>
                        </div>
                        <span class="badge bg-warning text-dark rounded-pill">Shops</span>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark"><?= number_format($count_shops) ?></h3>
                    <small class="text-muted">ร้านค้าทั้งหมด</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-info bg-opacity-10">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="bg-white rounded-circle p-3 text-info shadow-sm">
                            <i class="fas fa-clipboard-list fa-lg"></i>
                        </div>
                        <span class="badge bg-info text-dark rounded-pill">Orders</span>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark"><?= number_format($count_orders) ?></h3>
                    <small class="text-muted">คำสั่งซื้อทั้งหมด</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-success bg-opacity-10">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="bg-white rounded-circle p-3 text-success shadow-sm">
                            <i class="fas fa-coins fa-lg"></i>
                        </div>
                        <span class="badge bg-success rounded-pill">Income</span>
                    </div>
                    <h4 class="fw-bold mb-0 text-dark">฿<?= number_format($total_income) ?></h4>
                    <small class="text-muted">ยอดขายสำเร็จรวม</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold text-purple mb-0"><i class="fas fa-history me-2"></i>รายการสั่งซื้อล่าสุด</h5>
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
                                        $st_class = 'secondary';
                                        if($order['status']=='pending') $st_class = 'warning text-dark';
                                        if($order['status']=='paid') $st_class = 'info text-dark';
                                        if($order['status']=='completed') $st_class = 'success';
                                        if($order['status']=='cancelled') $st_class = 'danger';
                                    ?>
                                    <span class="badge bg-<?= $st_class ?> rounded-pill"><?= $order['status'] ?></span>
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
    .btn-outline-purple { color: var(--nia-purple); border-color: var(--nia-purple); }
    .btn-outline-purple:hover { background-color: var(--nia-purple); color: white; }
</style>

<?php include '../includes/footer.php'; ?>