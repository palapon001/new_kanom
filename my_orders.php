<?php
session_start();
require_once 'config.php';
require_once 'function.php';

// 1. ต้องล็อกอินก่อน
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. ดึงข้อมูลคำสั่งซื้อของ User คนนี้
// Join ตาราง users เพื่อเอาชื่อร้านค้า (shop_name) มาโชว์
$sql = "SELECT o.*, s.shop_name, s.phone as shop_phone
        FROM orders o
        JOIN users s ON o.shop_id = s.id
        WHERE o.customer_id = ? 
        ORDER BY o.created_at DESC";
$orders = select($sql, [$user_id]);

$theme = $config['theme'];
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container py-5">
    <h3 class="fw-bold text-purple mb-4"><i class="fas fa-history me-2"></i>ประวัติการสั่งซื้อของฉัน</h3>

    <?php if (empty($orders)): ?>
        <div class="text-center py-5 bg-light rounded-4">
            <i class="fas fa-file-invoice fa-3x text-muted mb-3 opacity-25"></i>
            <p class="text-muted">คุณยังไม่มีรายการคำสั่งซื้อ</p>
            <a href="index.php" class="btn btn-nia">ไปช้อปปิ้งกันเถอะ</a>
        </div>
    <?php else: ?>
        
        <div class="row">
            <div class="col-12">
                <?php foreach ($orders as $order): ?>
                    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                        
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-bold text-primary me-2">#<?= $order['order_no'] ?></span>
                                <span class="text-muted small"><i class="fas fa-store me-1"></i> <?= htmlspecialchars($order['shop_name']) ?></span>
                            </div>
                            <div class="text-muted small">
                                <?= date('d M Y H:i', strtotime($order['created_at'])) ?>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row align-items-center">
                                
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <?php 
                                        // Query สินค้าในออเดอร์นี้มาโชว์
                                        $items = select("SELECT product_name, quantity FROM order_items WHERE order_id = ?", [$order['id']]);
                                    ?>
                                    <ul class="list-unstyled mb-0 small">
                                        <?php foreach ($items as $item): ?>
                                            <li class="mb-1 text-muted">
                                                • <?= htmlspecialchars($item['product_name']) ?> <span class="text-dark">x<?= $item['quantity'] ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>

                                <div class="col-md-3 text-md-center mb-3 mb-md-0">
                                    <p class="mb-0 text-muted small">ยอดคำสั่งซื้อ</p>
                                    <h5 class="fw-bold text-magenta">฿<?= number_format($order['total_amount'], 2) ?></h5>
                                </div>

                                <div class="col-md-3 text-md-end">
                                    <?php 
                                        // Badge สถานะ
                                        $status_map = [
                                            'pending' => ['label' => 'รอตรวจสอบ', 'class' => 'warning text-dark'],
                                            'paid' => ['label' => 'ชำระแล้ว', 'class' => 'info text-dark'],
                                            'shipped' => ['label' => 'จัดส่งแล้ว', 'class' => 'primary'],
                                            'completed' => ['label' => 'สำเร็จ', 'class' => 'success'],
                                            'cancelled' => ['label' => 'ยกเลิก', 'class' => 'danger']
                                        ];
                                        $st = $status_map[$order['status']] ?? ['label' => $order['status'], 'class' => 'secondary'];
                                    ?>
                                    <span class="badge bg-<?= $st['class'] ?> bg-opacity-10 text-dark border border-<?= explode(' ', $st['class'])[0] ?> px-3 py-2 rounded-pill">
                                        <?= $st['label'] ?>
                                    </span>

                                    <div class="mt-2">
                                        <a href="tel:<?= $order['shop_phone'] ?>" class="btn btn-sm btn-link text-decoration-none text-muted">ติดต่อร้าน</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>