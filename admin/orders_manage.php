<?php
session_start();
require_once '../config.php';
require_once '../function.php';

// 1. ความปลอดภัย: ต้องเป็น Admin เท่านั้น
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// 2. รับค่าค้นหา
$search = $_GET['q'] ?? '';

// 3. Query ดึงข้อมูลออเดอร์ทั้งหมด (Join 3 ตาราง: orders + users(ลูกค้า) + users(ร้านค้า))
$sql = "SELECT o.*, 
               c.fullname AS customer_name, 
               s.shop_name 
        FROM orders o
        JOIN users c ON o.customer_id = c.id
        JOIN users s ON o.shop_id = s.id
        WHERE o.order_no LIKE ? OR s.shop_name LIKE ? OR c.fullname LIKE ?
        ORDER BY o.created_at DESC";

// ค้นหาได้ทั้ง เลขออเดอร์, ชื่อร้าน, หรือชื่อลูกค้า
$orders = select($sql, ["%$search%", "%$search%", "%$search%"]);

$theme = $config['theme'];
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold text-purple mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>รายการออเดอร์ทั้งหมด</h3>
            <p class="text-muted small mb-0">Monitor All Transactions</p>
        </div>
        
        <form class="d-flex" method="GET" action="orders_manage.php">
            <div class="input-group shadow-sm">
                <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                <input class="form-control border-start-0" type="search" name="q" placeholder="เลข Order / ชื่อร้าน / ลูกค้า..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-purple" type="submit">ค้นหา</button>
            </div>
        </form>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3"><i class="fas fa-clipboard-list fa-2x opacity-50"></i></div>
                    <div>
                        <h6 class="mb-0">ออเดอร์ทั้งหมด</h6>
                        <h3 class="fw-bold mb-0"><?= count($orders) ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-dark h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3"><i class="fas fa-clock fa-2x opacity-50"></i></div>
                    <div>
                        <h6 class="mb-0">รอตรวจสอบ</h6>
                        <h3 class="fw-bold mb-0">
                            <?= count(array_filter($orders, function($o){ return $o['status'] == 'pending'; })) ?>
                        </h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Order No.</th>
                        <th>วันที่สั่งซื้อ</th>
                        <th>ร้านค้า (ผู้ขาย)</th>
                        <th>ลูกค้า (ผู้ซื้อ)</th>
                        <th>ยอดรวม</th>
                        <th>สถานะ</th>
                        <th class="text-end pe-4">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($orders) > 0): ?>
                        <?php foreach ($orders as $o): ?>
                        <tr>
                            <td class="ps-4">
                                <span class="fw-bold text-primary">#<?= $o['order_no'] ?></span>
                            </td>
                            <td>
                                <div class="small text-muted">
                                    <i class="far fa-calendar-alt me-1"></i><?= date('d/m/Y', strtotime($o['created_at'])) ?>
                                </div>
                                <div class="small text-muted">
                                    <i class="far fa-clock me-1"></i><?= date('H:i', strtotime($o['created_at'])) ?> น.
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-purple bg-opacity-10 text-purple">
                                    <i class="fas fa-store me-1"></i> <?= htmlspecialchars($o['shop_name']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($o['customer_name']) ?></td>
                            <td class="fw-bold">฿<?= number_format($o['total_amount']) ?></td>
                            <td>
                                <?php 
                                    $st = $o['status'];
                                    $badge_color = 'secondary';
                                    $status_text = $st;

                                    if($st == 'pending') { $badge_color = 'warning text-dark'; $status_text = 'รอตรวจสอบ'; }
                                    elseif($st == 'paid') { $badge_color = 'info text-dark'; $status_text = 'ชำระเงินแล้ว'; }
                                    elseif($st == 'shipped') { $badge_color = 'primary'; $status_text = 'จัดส่งแล้ว'; }
                                    elseif($st == 'completed') { $badge_color = 'success'; $status_text = 'สำเร็จ'; }
                                    elseif($st == 'cancelled') { $badge_color = 'danger'; $status_text = 'ยกเลิก'; }
                                ?>
                                <span class="badge bg-<?= $badge_color ?> rounded-pill px-3"><?= $status_text ?></span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="order_view.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-outline-primary shadow-sm">
                                    <i class="fas fa-eye me-1"></i> ตรวจสอบ
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-folder-open fa-3x mb-3 opacity-25"></i><br>
                                ไม่พบรายการคำสั่งซื้อ
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>