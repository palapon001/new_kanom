<?php
session_start();
require_once 'config.php';
require_once 'function.php';

// 1. ตรวจสอบสิทธิ์ (ต้อง Login)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = $_GET['id'] ?? 0;

// 2. ดึงข้อมูลออเดอร์ + ร้านค้า
// ต้องเช็คว่า customer_id ตรงกับ user_id ที่ login อยู่ไหม (เพื่อความปลอดภัย)
$sql = "SELECT o.*, s.shop_name, s.phone as shop_phone, s.address as shop_address
        FROM orders o
        JOIN users s ON o.shop_id = s.id
        WHERE o.id = ? AND o.customer_id = ?";

$order = selectOne($sql, [$order_id, $user_id]);

if (!$order) {
    echo "<script>alert('ไม่พบข้อมูลคำสั่งซื้อ'); window.location='my_orders.php';</script>";
    exit();
}

// 3. ดึงรายการสินค้า
$items = select("SELECT * FROM order_items WHERE order_id = ?", [$order_id]);

$theme = $config['theme'];
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container py-5">
    
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="my_orders.php" class="text-muted text-decoration-none">ประวัติการสั่งซื้อ</a></li>
            <li class="breadcrumb-item active">ออเดอร์ #<?= $order['order_no'] ?></li>
        </ol>
    </nav>

    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
        <div class="card-body p-4 d-md-flex justify-content-between align-items-center bg-white">
            <div>
                <h4 class="fw-bold text-purple mb-1">คำสั่งซื้อ #<?= $order['order_no'] ?></h4>
                <p class="text-muted mb-0 small">สั่งซื้อเมื่อ: <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
            </div>
            <div class="mt-3 mt-md-0 text-end">
                <?php 
                    $status_map = [
                        'pending' => ['label' => '⏳ รอตรวจสอบ', 'class' => 'warning text-dark'],
                        'paid' => ['label' => '💰 ชำระแล้ว', 'class' => 'info text-dark'],
                        'shipped' => ['label' => '🚚 จัดส่งแล้ว', 'class' => 'primary'],
                        'completed' => ['label' => '✅ สำเร็จ', 'class' => 'success'],
                        'cancelled' => ['label' => '❌ ยกเลิก', 'class' => 'danger']
                    ];
                    $st = $status_map[$order['status']] ?? ['label' => $order['status'], 'class' => 'secondary'];
                ?>
                <span class="badge bg-<?= $st['class'] ?> fs-6 px-3 py-2 rounded-pill shadow-sm">
                    <?= $st['label'] ?>
                </span>
                
                <?php if(!empty($order['tracking_no'])): ?>
                    <div class="mt-2 text-primary fw-bold small">
                        <i class="fas fa-box me-1"></i> Tracking: <?= $order['tracking_no'] ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row g-4">
        
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h5 class="fw-bold mb-0 text-purple"><i class="fas fa-shopping-basket me-2"></i>รายการสินค้า</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">สินค้า</th>
                                    <th class="text-center">ราคา</th>
                                    <th class="text-center">จำนวน</th>
                                    <th class="text-end pe-4">รวม</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                <tr>
                                    <td class="ps-4">
                                        <span class="fw-bold"><?= htmlspecialchars($item['product_name']) ?></span>
                                    </td>
                                    <td class="text-center">฿<?= number_format($item['price']) ?></td>
                                    <td class="text-center">x<?= $item['quantity'] ?></td>
                                    <td class="text-end pe-4 fw-bold">฿<?= number_format($item['price'] * $item['quantity']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <td colspan="3" class="text-end fw-bold pt-3">ยอดรวมสุทธิ</td>
                                    <td class="text-end pe-4 fw-bold text-magenta fs-5 pt-3">฿<?= number_format($order['total_amount'], 2) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body">
                            <h6 class="fw-bold text-muted text-uppercase mb-3">ผู้ขาย (ร้านค้า)</h6>
                            <h5 class="fw-bold text-purple mb-2"><?= htmlspecialchars($order['shop_name']) ?></h5>
                            <p class="mb-1"><i class="fas fa-phone me-2 text-muted"></i> <?= htmlspecialchars($order['shop_phone']) ?></p>
                            <p class="small text-muted mb-0"><i class="fas fa-map-marker-alt me-2"></i> <?= htmlspecialchars($order['shop_address']) ?></p>
                            
                            <hr class="border-dashed my-3">
                            <a href="tel:<?= $order['shop_phone'] ?>" class="btn btn-outline-primary btn-sm rounded-pill w-100">
                                <i class="fas fa-phone-alt me-1"></i> โทรติดต่อร้านค้า
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body">
                            <h6 class="fw-bold text-muted text-uppercase mb-3">ข้อมูลการจัดส่งของคุณ</h6>
                            <div class="bg-light p-3 rounded border">
                                <?= nl2br(htmlspecialchars($order['shipping_address'])) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h5 class="fw-bold mb-0 text-purple"><i class="fas fa-file-invoice-dollar me-2"></i>หลักฐานการโอน</h5>
                </div>
                <div class="card-body text-center bg-light">
                    <?php if (!empty($order['slip_image'])): ?>
                        <?php 
                            $slip_path = 'uploads/slips/' . $order['slip_image'];
                            // กรณีทดสอบ: ถ้าไม่มีไฟล์จริง ให้ใช้ Placeholder
                            if (!file_exists($slip_path)) {
                                $slip_url = 'https://placehold.co/400x600?text=Slip+Image';
                            } else {
                                $slip_url = $slip_path;
                            }
                        ?>
                        <a href="<?= $slip_url ?>" target="_blank">
                            <img src="<?= $slip_url ?>" class="img-fluid rounded shadow-sm border mb-3" style="max-height: 400px;">
                        </a>
                        <p class="small text-muted mb-0"><i class="fas fa-search-plus"></i> คลิกเพื่อดูรูปใหญ่</p>
                    <?php else: ?>
                        <div class="py-5 text-muted">
                            <i class="fas fa-image fa-3x mb-3 opacity-25"></i>
                            <p class="mb-0">ไม่พบหลักฐานการโอนเงิน</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    .border-dashed { border-style: dashed !important; }
</style>

<?php include 'includes/footer.php'; ?>