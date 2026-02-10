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
$status_filter = $_GET['status'] ?? 'all';

// 2. สร้าง Query ตามตัวกรองสถานะ
$sql = "SELECT o.*, u.fullname AS customer_name 
        FROM orders o 
        JOIN users u ON o.customer_id = u.id 
        WHERE o.shop_id = ?";
$params = [$shop_id];

if ($status_filter !== 'all') {
    $sql .= " AND o.status = ?";
    $params[] = $status_filter;
}

$sql .= " ORDER BY o.created_at DESC";
$orders = select($sql, $params);

$theme = $config['theme'];
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-purple mb-0"><i class="fas fa-box-open me-2"></i>รายการคำสั่งซื้อ</h3>
        
        <div class="btn-group shadow-sm">
            <a href="order_list.php?status=all" class="btn btn-outline-nia <?= $status_filter=='all'?'active':'' ?>">ทั้งหมด</a>
            <a href="order_list.php?status=pending" class="btn btn-outline-warning <?= $status_filter=='pending'?'active':'' ?>">รอตรวจสอบ</a>
            <a href="order_list.php?status=paid" class="btn btn-outline-info <?= $status_filter=='paid'?'active':'' ?>">ชำระแล้ว</a>
            <a href="order_list.php?status=completed" class="btn btn-outline-success <?= $status_filter=='completed'?'active':'' ?>">สำเร็จ</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">เลขที่คำสั่งซื้อ</th>
                            <th>ลูกค้า</th>
                            <th>ยอดรวม</th>
                            <th>หลักฐาน</th>
                            <th>สถานะ</th>
                            <th>วันที่สั่งซื้อ</th>
                            <th class="text-end pe-4">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($orders) > 0): ?>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-primary">#<?= $order['order_no'] ?></td>
                                    <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                    <td class="fw-bold">฿<?= number_format($order['total_amount'], 2) ?></td>
                                    <td>
                                        <?php if ($order['slip_image']): ?>
                                            <span class="badge bg-success"><i class="fas fa-paperclip"></i> แนบสลิปแล้ว</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">รอชำระ</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                            // Badge สีตามสถานะ
                                            $badges = [
                                                'pending' => 'warning text-dark',
                                                'paid' => 'info text-dark',
                                                'shipped' => 'primary',
                                                'completed' => 'success',
                                                'cancelled' => 'danger'
                                            ];
                                            $status_th = [
                                                'pending' => 'รอตรวจสอบ',
                                                'paid' => 'ชำระแล้ว/รอส่ง',
                                                'shipped' => 'จัดส่งแล้ว',
                                                'completed' => 'เสร็จสิ้น',
                                                'cancelled' => 'ยกเลิก'
                                            ];
                                            $badge_class = $badges[$order['status']] ?? 'secondary';
                                            $status_text = $status_th[$order['status']] ?? $order['status'];
                                        ?>
                                        <span class="badge bg-<?= $badge_class ?> bg-opacity-10 text-dark border border-<?= explode(' ', $badge_class)[0] ?>">
                                            <?= $status_text ?>
                                        </span>
                                    </td>
                                    <td class="small text-muted"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                                    <td class="text-end pe-4">
                                        <a href="order_detail.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-nia shadow-sm">
                                            ตรวจสอบ <i class="fas fa-arrow-right ms-1"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i><br>
                                    ไม่มีรายการคำสั่งซื้อในสถานะนี้
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>