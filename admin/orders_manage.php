<?php
session_start();
require_once '../config.php';
require_once '../function.php';

// 1. ความปลอดภัย: ต้องเป็น Admin เท่านั้น
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// 2. รับค่าตัวแปรค้นหาและตัวกรอง
$search = $_GET['q'] ?? '';
$filter_status = $_GET['status'] ?? 'all';

// 3. สร้าง SQL แบบ Dynamic
$sql = "SELECT o.*, 
               c.fullname AS customer_name, 
               s.shop_name 
        FROM orders o
        JOIN users c ON o.customer_id = c.id
        JOIN users s ON o.shop_id = s.id
        WHERE (o.order_no LIKE ? OR s.shop_name LIKE ? OR c.fullname LIKE ?)";

$params = ["%$search%", "%$search%", "%$search%"];

// เพิ่มเงื่อนไขสถานะถ้าไม่ใช่ 'all'
if ($filter_status !== 'all') {
    $sql .= " AND o.status = ?";
    $params[] = $filter_status;
}

$sql .= " ORDER BY o.created_at DESC";

$orders = select($sql, $params);

// 4. ข้อมูลสถิติสำหรับ Card ด้านบน (ดึงแยกเพื่อความแม่นยำ)
$stats = [
    'total' => selectOne("SELECT COUNT(*) as c FROM orders")['c'],
    'pending' => selectOne("SELECT COUNT(*) as c FROM orders WHERE status = 'pending'")['c'],
    'completed' => selectOne("SELECT COUNT(*) as c FROM orders WHERE status = 'completed'")['c'],
    'income' => selectOne("SELECT SUM(total_amount) as s FROM orders WHERE status = 'completed'")['s'] ?? 0
];

$theme = $config['theme'];
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold text-purple mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>จัดการออเดอร์ทั้งหมด</h3>
            <p class="text-muted small mb-0">ตรวจสอบและกำกับดูแลรายการธุรกรรมในระบบ</p>
        </div>
        
        <form class="d-flex gap-2" method="GET" action="">
            <div class="input-group shadow-sm overflow-hidden rounded-pill">
                <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-search text-muted"></i></span>
                <input class="form-control border-0 shadow-none" type="search" name="q" placeholder="เลข Order / ร้าน / ลูกค้า" value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-purple px-4" type="submit">ค้นหา</button>
            </div>
        </form>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white border-start border-4 border-primary">
                <div class="card-body p-3">
                    <div class="text-muted small text-uppercase fw-bold">ออเดอร์ทั้งหมด</div>
                    <div class="h3 fw-bold mb-0 text-primary"><?= number_format($stats['total']) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white border-start border-4 border-warning">
                <div class="card-body p-3">
                    <div class="text-muted small text-uppercase fw-bold">รอตรวจสอบ</div>
                    <div class="h3 fw-bold mb-0 text-warning"><?= number_format($stats['pending']) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white border-start border-4 border-success">
                <div class="card-body p-3">
                    <div class="text-muted small text-uppercase fw-bold">สำเร็จแล้ว</div>
                    <div class="h3 fw-bold mb-0 text-success"><?= number_format($stats['completed']) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white border-start border-4 border-purple">
                <div class="card-body p-3">
                    <div class="text-muted small text-uppercase fw-bold">ยอดเงินหมุนเวียนรวม</div>
                    <div class="h3 fw-bold mb-0 text-purple">฿<?= number_format($stats['income']) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-4">
        <div class="nav nav-pills bg-white p-2 rounded-4 shadow-sm d-inline-flex">
            <a class="nav-link rounded-pill px-4 <?= $filter_status == 'all' ? 'active bg-purple' : 'text-muted' ?>" href="?status=all&q=<?= $search ?>">ทั้งหมด</a>
            <a class="nav-link rounded-pill px-4 <?= $filter_status == 'pending' ? 'active bg-warning text-dark' : 'text-muted' ?>" href="?status=pending&q=<?= $search ?>">รอตรวจสอบ</a>
            <a class="nav-link rounded-pill px-4 <?= $filter_status == 'completed' ? 'active bg-success text-white' : 'text-muted' ?>" href="?status=completed&q=<?= $search ?>">สำเร็จ</a>
            <a class="nav-link rounded-pill px-4 <?= $filter_status == 'cancelled' ? 'active bg-danger text-white' : 'text-muted' ?>" href="?status=cancelled&q=<?= $search ?>">ยกเลิก</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Order No.</th>
                        <th>วันที่ / เวลา</th>
                        <th>ผู้ขาย (ร้านค้า)</th>
                        <th>ผู้ซื้อ (ลูกค้า)</th>
                        <th class="text-center">ยอดเงิน</th>
                        <th class="text-center">สถานะ</th>
                        <th class="text-end pe-4">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($orders) > 0): ?>
                        <?php foreach ($orders as $o): ?>
                        <tr>
                            <td class="ps-4">
                                <span class="fw-bold text-dark">#<?= $o['order_no'] ?></span>
                            </td>
                            <td>
                                <div class="small fw-bold text-dark"><?= date('d/m/Y', strtotime($o['created_at'])) ?></div>
                                <div class="small text-muted"><?= date('H:i', strtotime($o['created_at'])) ?> น.</div>
                            </td>
                            <td>
                                <div class="fw-bold text-purple"><i class="fas fa-store me-1"></i> <?= htmlspecialchars($o['shop_name']) ?></div>
                            </td>
                            <td>
                                <div class="small text-dark fw-bold"><?= htmlspecialchars($o['customer_name']) ?></div>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold text-dark">฿<?= number_format($o['total_amount']) ?></span>
                            </td>
                            <td class="text-center">
                                <?php 
                                    $st = $o['status'];
                                    $badge_class = match($st) {
                                        'pending' => 'bg-warning text-dark',
                                        'paid' => 'bg-info text-dark',
                                        'shipped' => 'bg-primary',
                                        'completed' => 'bg-success',
                                        'cancelled' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                    $st_th = match($st) {
                                        'pending' => 'รอตรวจสอบ',
                                        'paid' => 'ชำระเงินแล้ว',
                                        'shipped' => 'จัดส่งแล้ว',
                                        'completed' => 'สำเร็จ',
                                        'cancelled' => 'ยกเลิก',
                                        default => $st
                                    };
                                ?>
                                <span class="badge <?= $badge_class ?> rounded-pill px-3 py-2 fw-normal" style="min-width: 100px;"><?= $st_th ?></span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="order_view.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-outline-purple rounded-pill px-3">
                                    <i class="fas fa-search-dollar me-1"></i> รายละเอียด
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-search fa-3x mb-3 opacity-25"></i><br>
                                    ไม่พบรายการคำสั่งซื้อตามตัวกรองที่เลือก
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .bg-purple { background-color: var(--nia-purple) !important; color: white !important; }
    .btn-outline-purple { color: var(--nia-purple); border-color: var(--nia-purple); }
    .btn-outline-purple:hover { background-color: var(--nia-purple); color: white; }
    .nav-pills .nav-link.active { box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
</style>

<?php include '../includes/footer.php'; ?>