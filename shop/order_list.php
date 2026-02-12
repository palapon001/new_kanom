<?php
session_start();
require_once '../config.php';
require_once '../function.php';

// 1. ตรวจสอบสิทธิ์ร้านค้า
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'shop') {
    header("Location: ../login.php");
    exit();
}

$shop_id = $_SESSION['user_id'];
$theme = $config['theme'];

// 2. รับค่า Filter สถานะ (ถ้ามี)
$status_filter = $_GET['status'] ?? 'all';

// 3. เตรียม Query
$sql = "SELECT o.*, u.fullname 
        FROM orders o 
        JOIN users u ON o.customer_id = u.id 
        WHERE o.shop_id = ?";
$params = [$shop_id];

// ถ้ามีการเลือกสถานะ ให้เพิ่มเงื่อนไข WHERE
if ($status_filter !== 'all') {
    $sql .= " AND o.status = ?";
    $params[] = $status_filter;
}

$sql .= " ORDER BY o.created_at DESC";

$orders = select($sql, $params);

// นับจำนวนออเดอร์แต่ละสถานะ (เพื่อแสดงตรงปุ่ม Filter)
$count_pending = selectOne("SELECT COUNT(*) as c FROM orders WHERE shop_id = ? AND status = 'pending'", [$shop_id])['c'];
$count_paid    = selectOne("SELECT COUNT(*) as c FROM orders WHERE shop_id = ? AND status = 'paid'", [$shop_id])['c'];

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-purple mb-1">
                <i class="fas fa-clipboard-list me-2"></i> จัดการคำสั่งซื้อ
            </h2>
            <p class="text-muted mb-0">รายการออเดอร์ทั้งหมดของร้านคุณ</p>
        </div>
        <div>
            <a href="order_list.php" class="btn btn-light shadow-sm text-purple fw-bold">
                <i class="fas fa-sync-alt me-1"></i> รีโหลด
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-2">
            <ul class="nav nav-pills nav-fill gap-2">
                <li class="nav-item">
                    <a href="order_list.php?status=all" class="nav-link rounded-pill <?= $status_filter=='all'?'active bg-purple':'' ?>">
                        ทั้งหมด
                    </a>
                </li>
                <li class="nav-item">
                    <a href="order_list.php?status=pending" class="nav-link rounded-pill <?= $status_filter=='pending'?'active bg-warning text-dark':'' ?>">
                        รอตรวจสอบ 
                        <?php if($count_pending > 0): ?>
                            <span class="badge bg-danger rounded-pill ms-1"><?= $count_pending ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="order_list.php?status=paid" class="nav-link rounded-pill <?= $status_filter=='paid'?'active bg-info text-dark':'' ?>">
                        ชำระแล้ว 
                        <?php if($count_paid > 0): ?>
                            <span class="badge bg-danger rounded-pill ms-1"><?= $count_paid ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="order_list.php?status=shipped" class="nav-link rounded-pill <?= $status_filter=='shipped'?'active bg-primary':'' ?>">
                        ส่งแล้ว
                    </a>
                </li>
                <li class="nav-item">
                    <a href="order_list.php?status=completed" class="nav-link rounded-pill <?= $status_filter=='completed'?'active bg-success':'' ?>">
                        สำเร็จ
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary">
                        <tr>
                            <th class="ps-4 py-3">เลขที่คำสั่งซื้อ</th>
                            <th>ลูกค้า</th>
                            <th>วันที่สั่งซื้อ</th>
                            <th>ยอดรวม</th>
                            <th>หลักฐาน</th>
                            <th class="text-center">สถานะ</th>
                            <th class="text-end pe-4">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                                    <p>ไม่พบรายการคำสั่งซื้อในสถานะนี้</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-primary">
                                        #<?= $order['order_no'] ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light rounded-circle p-2 me-2 text-purple">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div>
                                                <span class="fw-bold d-block"><?= htmlspecialchars($order['fullname']) ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-muted small">
                                        <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                                    </td>
                                    <td class="fw-bold text-dark">
                                        ฿<?= number_format($order['total_amount'], 2) ?>
                                    </td>
                                    <td>
                                        <?php if($order['slip_image']): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success">
                                                <i class="fas fa-check me-1"></i> แนบแล้ว
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border">
                                                ยังไม่แนบ
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php 
                                            $st_map = [
                                                'pending' => ['label'=>'รอตรวจสอบ', 'class'=>'warning text-dark'],
                                                'paid' => ['label'=>'ชำระแล้ว', 'class'=>'info text-dark'],
                                                'shipped' => ['label'=>'ส่งแล้ว', 'class'=>'primary'],
                                                'completed' => ['label'=>'สำเร็จ', 'class'=>'success'],
                                                'cancelled' => ['label'=>'ยกเลิก', 'class'=>'danger'],
                                            ];
                                            $s = $st_map[$order['status']] ?? ['label'=>$order['status'], 'class'=>'secondary'];
                                        ?>
                                        <span class="badge bg-<?= $s['class'] ?> rounded-pill px-3">
                                            <?= $s['label'] ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="order_detail.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-purple rounded-pill fw-bold">
                                            ตรวจสอบ <i class="fas fa-arrow-right ms-1"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .nav-pills .nav-link {
        color: #6c757d;
        font-weight: bold;
        transition: all 0.3s;
    }
    .nav-pills .nav-link:hover {
        background-color: #f8f9fa;
    }
    .btn-outline-purple {
        color: var(--nia-purple);
        border-color: var(--nia-purple);
    }
    .btn-outline-purple:hover {
        background-color: var(--nia-purple);
        color: white;
    }
    .bg-purple { background-color: var(--nia-purple) !important; color: white !important; }
</style>

<?php include '../includes/footer.php'; ?>