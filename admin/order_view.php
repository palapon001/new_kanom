<?php
session_start();
require_once '../config.php';
require_once '../function.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$order_id = $_GET['id'] ?? 0;

// Logic อัปเดตสถานะ (Admin Force)
if (isset($_POST['admin_update_status'])) {
    update('orders', [
        'status' => $_POST['status'],
        'tracking_no' => $_POST['tracking_no']
    ], "id = ?", [$order_id]);
    $_SESSION['success'] = 'อัปเดตข้อมูลเรียบร้อย';
    header("Location: order_view.php?id=$order_id");
    exit();
}

$sql = "SELECT o.*, 
               c.fullname AS customer_name, c.phone AS customer_phone, c.address AS customer_address,
               s.shop_name, s.phone as shop_phone, s.address as shop_address
        FROM orders o
        JOIN users c ON o.customer_id = c.id
        JOIN users s ON o.shop_id = s.id
        WHERE o.id = ?";
$order = selectOne($sql, [$order_id]);
$items = select("SELECT * FROM order_items WHERE order_id = ?", [$order_id]);

include '../includes/header.php';
include '../includes/navbar.php';
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-3 d-print-none">
        <a href="orders_manage.php" class="btn btn-light rounded-pill"><i class="fas fa-arrow-left"></i> กลับ</a>
        
        <div class="dropdown">
            <button class="btn btn-purple dropdown-toggle shadow-sm fw-bold rounded-pill" data-bs-toggle="dropdown">
                <i class="fas fa-file-export me-2"></i>Export / Print
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                <li><button class="dropdown-item" onclick="window.print()"><i class="fas fa-print me-2 text-dark"></i>พิมพ์ใบปะหน้า (Print)</button></li>
                <li><hr class="dropdown-divider"></li>
                <li><button class="dropdown-item" onclick="exportOrder('png')"><i class="far fa-image me-2 text-primary"></i>บันทึกเป็น PNG</button></li>
                <li><button class="dropdown-item" onclick="exportOrder('jpeg')"><i class="far fa-image me-2 text-warning"></i>บันทึกเป็น JPG</button></li>
                <li><button class="dropdown-item" onclick="exportPDF()"><i class="far fa-file-pdf me-2 text-danger"></i>บันทึกเป็น PDF</button></li>
            </ul>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8" id="exportArea">
            
            <div class="d-none d-print-block text-center mb-4">
                <h4 class="fw-bold">ใบสรุปคำสั่งซื้อ #<?= $order['order_no'] ?></h4>
                <p class="small text-muted">ออกโดยระบบบริหารจัดการร้านค้ากลาง</p>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden info-card">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="fw-bold text-purple mb-0"><i class="fas fa-map-marker-alt me-2"></i>ที่อยู่สำหรับจัดส่ง</h5>
                </div>
                <div class="card-body p-4 border-top">
                    <div class="row g-4">
                        <div class="col-6 border-end">
                            <h6 class="text-muted small fw-bold text-uppercase mb-2">ผู้ส่ง (Sender)</h6>
                            <p class="fw-bold mb-1"><?= htmlspecialchars($order['shop_name']) ?></p>
                            <p class="small mb-1"><?= htmlspecialchars($order['shop_phone']) ?></p>
                            <p class="small text-muted"><?= htmlspecialchars($order['shop_address']) ?></p>
                        </div>
                        <div class="col-6">
                            <h6 class="text-purple small fw-bold text-uppercase mb-2">ผู้รับ (Receiver)</h6>
                            <p class="fw-bold mb-1" style="font-size: 1.1rem;"><?= htmlspecialchars($order['customer_name']) ?></p>
                            <p class="fw-bold text-purple mb-2"><?= htmlspecialchars($order['customer_phone']) ?></p>
                            <div class="small lh-base">
                                <?= nl2br(htmlspecialchars($order['shipping_address'] ?? $order['customer_address'])) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 fw-bold text-purple"><i class="fas fa-shopping-basket me-2"></i>รายการสินค้า</h5>
                </div>
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
                                <td colspan="3" class="text-end fw-bold pt-3">ยอดรวมสุทธิ</td>
                                <td class="text-end fw-bold pe-4 pt-3 text-purple h5">฿<?= number_format($order['total_amount']) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden slip-card">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 fw-bold text-purple"><i class="fas fa-receipt me-2"></i>หลักฐานการชำระเงิน</h5>
                </div>
                <div class="card-body bg-light text-center p-4">
                    <?php if(!empty($order['slip_image'])): ?>
                        <img src="../uploads/slips/<?= $order['slip_image'] ?>" class="img-fluid rounded shadow border" style="max-height: 450px;">
                    <?php else: ?>
                        <p class="text-muted py-5">ไม่มีหลักฐานการโอนเงิน</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4 d-print-none">
            <div class="card border-0 shadow-sm rounded-4 bg-purple text-white mb-4" 
                 style="background: linear-gradient(135deg, #2D1F57 0%, #5D4396 100%);">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4"><i class="fas fa-user-shield me-2"></i>Admin Control</h5>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold opacity-75">สถานะออเดอร์</label>
                            <select name="status" class="form-select border-0 shadow-none">
                                <option value="pending" <?= $order['status']=='pending'?'selected':'' ?>>🟠 รอตรวจสอบ</option>
                                <option value="paid" <?= $order['status']=='paid'?'selected':'' ?>>🔵 ชำระแล้ว</option>
                                <option value="shipped" <?= $order['status']=='shipped'?'selected':'' ?>>🟣 ส่งของแล้ว</option>
                                <option value="completed" <?= $order['status']=='completed'?'selected':'' ?>>🟢 สำเร็จ</option>
                                <option value="cancelled" <?= $order['status']=='cancelled'?'selected':'' ?>>🔴 ยกเลิก</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold opacity-75">เลขพัสดุ</label>
                            <input type="text" name="tracking_no" class="form-control border-0" value="<?= htmlspecialchars($order['tracking_no'] ?? '') ?>" placeholder="กรอกเลขพัสดุ">
                        </div>
                        <button type="submit" name="admin_update_status" class="btn btn-light w-100 fw-bold text-purple rounded-pill shadow-sm">
                            <i class="fas fa-save me-2"></i> บันทึกข้อมูล
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // 📸 ฟังก์ชัน Export เป็นรูปภาพ (PNG/JPG)
    function exportOrder(type) {
        const area = document.getElementById('exportArea');
        html2canvas(area, { scale: 2, useCORS: true }).then(canvas => {
            const link = document.createElement('a');
            link.download = 'order-<?= $order['order_no'] ?>.' + type;
            link.href = canvas.toDataURL('image/' + type, 0.9);
            link.click();
        });
    }

    // 📄 ฟังก์ชัน Export เป็น PDF
    function exportPDF() {
        const area = document.getElementById('exportArea');
        const opt = {
            margin:       [0.5, 0.5],
            filename:     'order-<?= $order['order_no'] ?>.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2, useCORS: true },
            jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
        };
        html2pdf().set(opt).from(area).save();
    }
</script>

<style>
/* 🖨️ CSS สำหรับการพิมพ์ */
@media print {
    .d-print-none, nav, footer, .navbar, .btn, .dropdown { display: none !important; }
    body { background: #fff !important; }
    .container { max-width: 100% !important; width: 100% !important; padding: 0 !important; }
    .col-lg-8 { width: 100% !important; flex: 0 0 100% !important; max-width: 100% !important; }
    .card { border: 1px solid #ddd !important; box-shadow: none !important; margin-bottom: 20px !important; }
    .card-header { border-bottom: 1px solid #ddd !important; }
}
/* สไตล์ทั่วไป */
.info-card { border-left: 5px solid var(--nia-purple) !important; }
.slip-card { border-top: 5px solid #28a745 !important; }
</style>

<?php include '../includes/footer.php'; ?>