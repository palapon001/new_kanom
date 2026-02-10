<?php
session_start();
require_once 'config.php';
require_once 'function.php';

// 1. ตรวจสอบว่า Login หรือยัง
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบก่อนชำระเงิน';
    header("Location: login.php");
    exit();
}

// 2. รับค่า Shop ID ที่ส่งมาจากหน้า Cart
$shop_id = $_GET['shop_id'] ?? 0;
if ($shop_id == 0) {
    header("Location: cart.php");
    exit();
}

$current_user_id = $_SESSION['user_id'];

// 3. ดึงข้อมูล "ผู้ซื้อ" (เพื่อเอาที่อยู่จัดส่ง)
$sql_user = "SELECT fullname, phone, address FROM users WHERE id = ?";
$buyer = selectOne($sql_user, [$current_user_id]);

// 4. ดึงข้อมูล "ร้านค้า" (เพื่อเอาเลขบัญชี/QR Code)
$sql_shop = "SELECT * FROM users WHERE id = ? AND role = 'shop'";
$shop = selectOne($sql_shop, [$shop_id]);

if (!$shop) {
    echo "<script>alert('ไม่พบข้อมูลร้านค้า'); window.location='cart.php';</script>";
    exit();
}

// 5. ดึงสินค้าในตะกร้า (เฉพาะของร้านนี้)
$checkout_items = [];
$total_price = 0;
$total_qty = 0;

if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
    $product_ids = array_keys($_SESSION['cart']);
    
    if (!empty($product_ids)) {
        $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
        
        $sql = "SELECT * FROM products WHERE id IN ($placeholders) AND shop_id = ?";
        $params = array_merge($product_ids, [$shop_id]);
        
        $products = select($sql, $params);

        foreach ($products as $p) {
            $id = $p['id'];
            $qty = $_SESSION['cart'][$id];
            $subtotal = $p['price'] * $qty;

            $total_price += $subtotal;
            $total_qty += $qty;

            $p['qty'] = $qty;
            $p['subtotal'] = $subtotal;
            $checkout_items[] = $p;
        }
    }
}

if (empty($checkout_items)) {
    echo "<script>alert('ไม่มีสินค้าของร้านนี้ในตะกร้า'); window.location='cart.php';</script>";
    exit();
}

$theme = $config['theme'];
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container py-5">
    
    <a href="cart.php" class="btn btn-outline-secondary rounded-pill mb-4 shadow-sm">
        <i class="fas fa-arrow-left me-2"></i> ย้อนกลับไปตะกร้า
    </a>

    <div class="mb-4">
        <h2 class="fw-bold text-purple"><i class="fas fa-money-bill-wave me-2"></i> แจ้งชำระเงิน</h2>
        <p class="text-muted">ร้านค้า: <span class="fw-bold text-dark"><?= htmlspecialchars($shop['shop_name']) ?></span></p>
    </div>

    <form action="process/order_save.php" method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
        <input type="hidden" name="shop_id" value="<?= $shop_id ?>">
        <input type="hidden" name="total_price" value="<?= $total_price ?>">

        <div class="row g-4">
            
            <div class="col-lg-7">
                
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="fw-bold mb-0"><i class="fas fa-map-marker-alt me-2 text-danger"></i> ที่อยู่จัดส่ง</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small text-muted">ชื่อ-นามสกุล</label>
                                <input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($buyer['fullname']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted">เบอร์โทรศัพท์</label>
                                <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($buyer['phone']) ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted">ที่อยู่จัดส่ง</label>
                                <textarea name="address" class="form-control" rows="3" required><?= htmlspecialchars($buyer['address']) ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="fw-bold mb-0"><i class="fas fa-shopping-basket me-2 text-warning"></i> รายการสินค้า</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php foreach ($checkout_items as $item): ?>
                            <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="badge bg-light text-dark rounded-pill border"><?= $item['qty'] ?>x</span>
                                    <div>
                                        <h6 class="mb-0 text-dark"><?= htmlspecialchars($item['name']) ?></h6>
                                        <small class="text-muted">฿<?= number_format($item['price']) ?> / ชิ้น</small>
                                    </div>
                                </div>
                                <span class="fw-bold">฿<?= number_format($item['subtotal']) ?></span>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="p-3 bg-light d-flex justify-content-between align-items-center rounded-bottom-4">
                            <span class="fw-bold fs-5">ยอดรวมทั้งสิ้น</span>
                            <span class="fw-bold fs-4 text-magenta">฿<?= number_format($total_price, 2) ?></span>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-lg-5">
                
                <div class="card border-0 shadow-sm rounded-4 bg-white">
                    <div class="card-header bg-purple text-white py-3 rounded-top-4" 
                         style="background: linear-gradient(135deg, <?= $theme['colors']['secondary'] ?> 0%, <?= $theme['colors']['primary'] ?> 100%);">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-university me-2"></i> ช่องทางชำระเงิน</h5>
                    </div>
                    <div class="card-body text-center p-4">
                        
                        <?php 
                            $has_bank = !empty($shop['bank_account']) && !empty($shop['bank_name']);
                            $has_qr = !empty($shop['qrcode_image']);
                        ?>

                        <?php if ($has_bank): ?>
                            <div class="bg-light p-3 rounded-3 mb-3 text-start">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted small">ธนาคาร</span>
                                    <span class="fw-bold text-dark"><?= htmlspecialchars($shop['bank_name']) ?></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted small">ชื่อบัญชี</span>
                                    <span class="fw-bold text-dark"><?= htmlspecialchars($shop['bank_account_name']) ?></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">เลขที่บัญชี</span>
                                    <div class="d-flex align-items-center">
                                        <span class="fw-bold text-primary fs-5 me-2" id="accNum"><?= htmlspecialchars($shop['bank_account']) ?></span>
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle" onclick="copyToClipboard('accNum')" title="คัดลอก">
                                            <i class="far fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($has_qr): ?>
                            <div class="mb-3">
                                <p class="small text-muted mb-2">หรือสแกน QR Code</p>
                                <img src="uploads/shop/<?= $shop['qrcode_image'] ?>" class="img-fluid rounded border shadow-sm" style="max-width: 200px;">
                            </div>
                        <?php endif; ?>

                        <?php if (!$has_bank && !$has_qr): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-circle"></i> ร้านค้านี้ยังไม่ได้ระบุช่องทางชำระเงิน <br>กรุณาติดต่อร้านค้าโดยตรง
                            </div>
                        <?php endif; ?>

                        <hr>

                        <div class="mb-3 text-start">
                            <label class="form-label fw-bold small">แนบหลักฐานการโอนเงิน (สลิป) *</label>
                            <input type="file" name="payment_slip" class="form-control" accept="image/*" required>
                            <div class="form-text text-muted">รองรับไฟล์ .jpg, .png, .jpeg</div>
                        </div>

                        <button type="submit" class="btn btn-nia btn-lg w-100 rounded-pill shadow-sm fw-bold mt-2">
                            <i class="fas fa-check-circle me-2"></i> ยืนยันการสั่งซื้อ
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

<script>
function copyToClipboard(elementId) {
    var copyText = document.getElementById(elementId).innerText;
    navigator.clipboard.writeText(copyText).then(function() {
        Swal.fire({
            icon: 'success',
            title: 'คัดลอกเลขบัญชีแล้ว',
            showConfirmButton: false,
            timer: 1000
        });
    });
}

function validateForm() {
    return true;
}
</script>

<?php include 'includes/footer.php'; ?>