<?php
session_start();
require_once '../config.php';
require_once '../function.php';

// 1. ความปลอดภัย: Admin เท่านั้น
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'คุณไม่มีสิทธิ์เข้าถึงส่วนนี้';
    header("Location: ../login.php");
    exit();
}

$order_id = $_GET['id'] ?? 0;

// 2. Logic: บังคับเปลี่ยนสถานะ (Admin Force Update)
if (isset($_POST['admin_update_status'])) {
    $new_status = $_POST['status'];
    $tracking = $_POST['tracking_no'] ?? '';

    // อัปเดตสถานะลง DB
    $update_result = update('orders', [
        'status' => $new_status,
        'tracking_no' => $tracking
    ], "id = ?", [$order_id]);

    if ($update_result) {
        $_SESSION['success'] = '✅ Admin: อัปเดตสถานะเรียบร้อยแล้ว';
        header("Location: order_view.php?id=$order_id");
        exit();
    } else {
        $_SESSION['error'] = 'เกิดข้อผิดพลาดในการอัปเดต';
    }
}

// 3. ดึงข้อมูลออเดอร์ + ข้อมูลลูกค้า + ข้อมูลร้านค้า
$sql = "SELECT o.*, 
               c.fullname AS customer_name, c.phone AS customer_phone, c.address AS customer_address,
               s.shop_name, s.phone as shop_phone, s.address as shop_address
        FROM orders o
        JOIN users c ON o.customer_id = c.id
        JOIN users s ON o.shop_id = s.id
        WHERE o.id = ?";

$order = selectOne($sql, [$order_id]);

if (!$order) {
    $_SESSION['error'] = 'ไม่พบข้อมูลคำสั่งซื้อ';
    header("Location: orders_manage.php");
    exit();
}

// 4. ดึงรายการสินค้าในออเดอร์
$items = select("SELECT * FROM order_items WHERE order_id = ?", [$order_id]);

$theme = $config['theme'];
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="orders_manage.php" class="text-muted text-decoration-none">รายการออเดอร์ทั้งหมด</a></li>
            <li class="breadcrumb-item active">ออเดอร์ #<?= $order['order_no'] ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        
        <div class="col-lg-8">
            
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-purple mb-4"><i class="fas fa-handshake me-2"></i>ข้อมูลคู่ค้า</h5>
                    <div class="row">
                        <div class="col-md-6 border-end">
                            <h6 class="text-muted small fw-bold text-uppercase">ผู้ขาย (SHOP)</h6>
                            <p class="fw-bold mb-1"><?= htmlspecialchars($order['shop_name']) ?></p>
                            <p class="small text-muted mb-1"><i class="fas fa-phone me-1"></i> <?= htmlspecialchars($order['shop_phone']) ?></p>
                            <p class="small text-muted"><i class="fas fa-map-marker-alt me-1"></i> <?= htmlspecialchars($order['shop_address']) ?></p>
                        </div>
                        <div class="col-md-6 ps-md-4">
                            <h6 class="text-muted small fw-bold text-uppercase">ผู้ซื้อ (CUSTOMER)</h6>
                            <p class="fw-bold mb-1"><?= htmlspecialchars($order['customer_name']) ?></p>
                            <p class="small text-muted mb-1"><i class="fas fa-phone me-1"></i> <?= htmlspecialchars($order['customer_phone']) ?></p>
                            <div class="bg-light p-2 rounded small text-muted border">
                                <i class="fas fa-map-marker-alt me-1 text-danger"></i> 
                                <?= nl2br(htmlspecialchars($order['shipping_address'] ?? $order['customer_address'])) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h5 class="mb-0 fw-bold text-purple"><i class="fas fa-shopping-basket me-2"></i>รายการสินค้า</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0 align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">สินค้า</th>
                                    <th class="text-center">ราคา</th>
                                    <th class="text-center">จำนวน</th>
                                    <th class="text-end pe-4">รวม</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($items as $item): ?>
                                <tr>
                                    <td class="ps-4 fw-bold"><?= htmlspecialchars($item['product_name']) ?></td>
                                    <td class="text-center">฿<?= number_format($item['price']) ?></td>
                                    <td class="text-center">x<?= $item['quantity'] ?></td>
                                    <td class="text-end pe-4 fw-bold">฿<?= number_format($item['price'] * $item['quantity']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <td colspan="3" class="text-end fw-bold pt-3">ยอดสุทธิ</td>
                                    <td class="text-end fw-bold pe-4 pt-3 text-purple h5">฿<?= number_format($order['total_amount']) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h5 class="mb-0 fw-bold text-purple"><i class="fas fa-file-invoice-dollar me-2"></i>หลักฐานการโอนเงิน</h5>
                </div>
                <div class="card-body text-center bg-light">
                    <?php if(!empty($order['slip_image'])): ?>
                        <?php 
                            $slip = $order['slip_image'];
                            $slip_path_check = '../uploads/slips/' . $slip;
                            
                            if (file_exists($slip_path_check)) {
                                $display_slip = $slip_path_check;
                            } else {
                                $display_slip = 'https://placehold.co/400x600?text=Slip+Not+Found';
                            }
                        ?>
                        <a href="<?= $display_slip ?>" target="_blank">
                            <img src="<?= $display_slip ?>" class="img-fluid rounded shadow-sm border" style="max-height: 400px; object-fit: contain;">
                        </a>
                        <p class="text-muted mt-2 small"><i class="fas fa-search-plus"></i> คลิกที่รูปเพื่อดูภาพขยาย</p>
                    <?php else: ?>
                        <div class="py-5 text-muted">
                            <i class="fas fa-image fa-3x mb-3 opacity-25"></i>
                            <p class="mb-0">ไม่มีหลักฐานการโอนเงิน (อาจเป็นการเก็บเงินปลายทาง)</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 bg-purple text-white mb-4" 
                 style="background: linear-gradient(135deg, #2D1F57 0%, #5D4396 100%);">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3"><i class="fas fa-user-shield me-2"></i>Admin Control</h5>
                    <p class="small opacity-75 mb-4">
                        คุณมีสิทธิ์ระดับสูงสุดในการจัดการออเดอร์นี้ กรุณาใช้ความระมัดระวังในการเปลี่ยนสถานะ
                    </p>

                    <form method="POST">
                        <label class="form-label small fw-bold text-uppercase opacity-75">สถานะปัจจุบัน</label>
                        <select name="status" class="form-select form-select-lg mb-3 shadow-none border-0 text-purple fw-bold">
                            <option value="pending" <?= $order['status']=='pending'?'selected':'' ?>>🟠 รอตรวจสอบ (Pending)</option>
                            <option value="paid" <?= $order['status']=='paid'?'selected':'' ?>>🔵 ชำระแล้ว (Paid)</option>
                            <option value="shipped" <?= $order['status']=='shipped'?'selected':'' ?>>🟣 ส่งของแล้ว (Shipped)</option>
                            <option value="completed" <?= $order['status']=='completed'?'selected':'' ?>>🟢 สำเร็จ (Completed)</option>
                            <option value="cancelled" <?= $order['status']=='cancelled'?'selected':'' ?>>🔴 ยกเลิก (Cancelled)</option>
                        </select>

                        <label class="form-label small fw-bold text-uppercase opacity-75">เลขพัสดุ (Tracking No.)</label>
                        <input type="text" name="tracking_no" class="form-control mb-4 border-0 text-purple fw-bold" 
                               value="<?= htmlspecialchars($order['tracking_no'] ?? '') ?>" placeholder="ยังไม่มีเลขพัสดุ">

                        <button type="submit" name="admin_update_status" class="btn btn-light w-100 fw-bold shadow-sm text-purple">
                            <i class="fas fa-save me-2"></i> บันทึกการเปลี่ยนแปลง
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="d-grid gap-2">
                <a href="tel:<?= $order['shop_phone'] ?>" class="btn btn-outline-secondary bg-white shadow-sm">
                    <i class="fas fa-store me-2"></i> โทรหาร้านค้า
                </a>
                <a href="tel:<?= $order['customer_phone'] ?>" class="btn btn-outline-secondary bg-white shadow-sm">
                    <i class="fas fa-user me-2"></i> โทรหาลูกค้า
                </a>
            </div>

        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>