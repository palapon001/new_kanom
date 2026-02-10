<?php
session_start();
require_once '../config.php';
require_once '../function.php';

// 1. ตรวจสอบสิทธิ์
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'shop') {
    header("Location: ../login.php");
    exit();
}

$shop_id = $_SESSION['user_id'];
$order_id = $_GET['id'] ?? 0;

// 2. [PROCESS] ส่วนอัปเดตสถานะ (ทำงานเมื่อกดปุ่ม)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    // อัปเดตสถานะออเดอร์ (ต้องเป็นออเดอร์ของร้านตัวเองเท่านั้น)
    $sql_update = "UPDATE orders SET status = ? WHERE id = ? AND shop_id = ?";
    if (update('orders', ['status' => $new_status], "id = ? AND shop_id = ?", [$order_id, $shop_id])) {
        echo "<script>alert('อัปเดตสถานะเรียบร้อยแล้ว'); window.location.href='order_detail.php?id=$order_id';</script>";
    }
}

// 3. ดึงข้อมูลออเดอร์ + ข้อมูลลูกค้า
$sql_order = "SELECT o.*, u.fullname, u.email, u.phone 
              FROM orders o 
              JOIN users u ON o.customer_id = u.id 
              WHERE o.id = ? AND o.shop_id = ?";
$order = selectOne($sql_order, [$order_id, $shop_id]);

if (!$order) {
    die("ไม่พบคำสั่งซื้อ หรือคุณไม่มีสิทธิ์เข้าถึง");
}

// 4. ดึงรายการสินค้าในออเดอร์
$items = select("SELECT * FROM order_items WHERE order_id = ?", [$order_id]);

$theme = $config['theme'];
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard.php" class="text-muted text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="order_list.php" class="text-muted text-decoration-none">รายการคำสั่งซื้อ</a></li>
            <li class="breadcrumb-item active" aria-current="page">ออเดอร์ #<?= $order['order_no'] ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h5 class="fw-bold mb-0 text-purple"><i class="fas fa-list me-2"></i>รายการสินค้า</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">สินค้า</th>
                                    <th class="text-center">ราคาต่อชิ้น</th>
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
                                    <td class="text-center">x <?= $item['quantity'] ?></td>
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

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h5 class="fw-bold mb-0 text-purple"><i class="fas fa-address-card me-2"></i>ข้อมูลจัดส่ง</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="small text-muted fw-bold">ชื่อลูกค้า</label>
                            <p class="mb-0"><?= htmlspecialchars($order['fullname']) ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small text-muted fw-bold">เบอร์โทรศัพท์</label>
                            <p class="mb-0"><?= htmlspecialchars($order['phone']) ?></p>
                        </div>
                        <div class="col-12">
                            <label class="small text-muted fw-bold">ที่อยู่จัดส่ง</label>
                            <div class="bg-light p-3 rounded border">
                                <?= nl2br(htmlspecialchars($order['shipping_address'])) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body">
                    <h5 class="fw-bold text-purple mb-3">สถานะออเดอร์</h5>
                    
                    <form method="POST">
                        <input type="hidden" name="update_status" value="1">
                        
                        <div class="mb-3">
                            <label class="form-label small text-muted">สถานะปัจจุบัน</label>
                            <select name="status" class="form-select fw-bold border-2 border-purple">
                                <option value="pending" <?= $order['status']=='pending'?'selected':'' ?>>⏳ รอตรวจสอบ</option>
                                <option value="paid" <?= $order['status']=='paid'?'selected':'' ?>>💰 ชำระเงินแล้ว (เตรียมส่ง)</option>
                                <option value="shipped" <?= $order['status']=='shipped'?'selected':'' ?>>🚚 จัดส่งแล้ว</option>
                                <option value="completed" <?= $order['status']=='completed'?'selected':'' ?>>✅ สำเร็จ</option>
                                <option value="cancelled" <?= $order['status']=='cancelled'?'selected':'' ?>>❌ ยกเลิก</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-nia w-100 fw-bold shadow-sm">
                            <i class="fas fa-save me-2"></i> บันทึกสถานะ
                        </button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h5 class="fw-bold mb-0 text-purple"><i class="fas fa-file-invoice-dollar me-2"></i>หลักฐานการโอน</h5>
                </div>
                <div class="card-body text-center">
                    <?php if ($order['slip_image']): ?>
                        <?php 
                            $slip_path = '../uploads/slips/' . $order['slip_image'];
                            // กรณีทดสอบ: ถ้าไฟล์ไม่มีจริง ให้ใช้รูป Mockup
                            if(!file_exists($slip_path)) $slip_path = 'https://source.unsplash.com/400x600/?receipt';
                        ?>
                        <a href="<?= $slip_path ?>" target="_blank">
                            <img src="<?= $slip_path ?>" class="img-fluid rounded border mb-3" style="max-height: 400px;">
                        </a>
                        <p class="small text-muted"><i class="fas fa-search-plus"></i> คลิกเพื่อดูรูปใหญ่</p>
                    <?php else: ?>
                        <div class="py-5 bg-light rounded text-muted">
                            <i class="fas fa-image fa-3x mb-2 opacity-25"></i>
                            <p class="mb-0">ไม่มีหลักฐานแนบมา</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>