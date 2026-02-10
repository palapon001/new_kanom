<?php
session_start();
require_once 'config.php';
require_once 'function.php';

$theme = $config['theme'];

// 1. ดึงข้อมูลสินค้าในตะกร้า
$grouped_cart = []; 
$has_items = false;

if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
    $product_ids = array_keys($_SESSION['cart']);
    
    if (!empty($product_ids)) {
        $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
        
        // Query ข้อมูลสินค้า + ชื่อร้านค้า + ID ร้านค้า
        $sql = "SELECT p.*, u.shop_name, u.id as shop_id 
                FROM products p 
                JOIN users u ON p.shop_id = u.id 
                WHERE p.id IN ($placeholders)
                ORDER BY u.shop_name ASC";
        
        $products = select($sql, $product_ids);

        // วนลูปจัดกลุ่มสินค้าตามร้านค้า
        foreach ($products as $p) {
            $id = $p['id'];
            $qty = $_SESSION['cart'][$id];
            $subtotal = $p['price'] * $qty;
            $shop_id = $p['shop_id'];

            $p['qty'] = $qty;
            $p['subtotal'] = $subtotal;

            if (!isset($grouped_cart[$shop_id])) {
                $grouped_cart[$shop_id] = [
                    'shop_name' => $p['shop_name'],
                    'shop_id' => $shop_id,
                    'items' => [],
                    'total_price' => 0,
                    'total_qty' => 0
                ];
            }

            $grouped_cart[$shop_id]['items'][] = $p;
            $grouped_cart[$shop_id]['total_price'] += $subtotal;
            $grouped_cart[$shop_id]['total_qty'] += $qty;
        }
        
        if (count($grouped_cart) > 0) {
            $has_items = true;
        }
    }
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container py-5">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h2 class="fw-bold text-purple mb-0">
                <i class="fas fa-shopping-cart me-2"></i> ตะกร้าสินค้า
            </h2>
            <?php if($has_items): ?>
                <p class="text-muted mb-0">แยกรายการตามร้านค้า (ชำระเงินทีละร้าน)</p>
            <?php else: ?>
                <p class="text-muted mb-0">ตะกร้าสินค้าของคุณว่างเปล่า</p>
            <?php endif; ?>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <?php if ($has_items): ?>
                <a href="process/cart_action.php?action=clear" class="btn btn-outline-danger btn-sm rounded-pill" onclick="return confirm('คุณต้องการล้างตะกร้าทั้งหมดใช่หรือไม่?');">
                    <i class="fas fa-trash-alt me-1"></i> ล้างทั้งหมด
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$has_items): ?>
        <div class="text-center py-5 bg-white shadow-sm rounded-4">
            <div class="mb-4">
                <div class="bg-light rounded-circle d-inline-flex p-4">
                    <i class="fas fa-shopping-basket fa-4x text-muted opacity-25"></i>
                </div>
            </div>
            <h4 class="fw-bold text-muted">ตะกร้าของคุณยังว่างอยู่</h4>
            <p class="text-muted mb-4">เลือกซื้อขนมอร่อยๆ จากเมืองเพชรกันเถอะ!</p>
            <a href="index.php" class="btn btn-nia btn-lg rounded-pill px-5 shadow-sm">
                <i class="fas fa-store me-2"></i> ไปเลือกซื้อสินค้า
            </a>
        </div>

    <?php else: ?>
        <form action="process/cart_action.php" method="POST" id="cartForm">
            <input type="hidden" name="action" value="update">
            
            <?php foreach ($grouped_cart as $shop_id => $shop_data): ?>
                
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="bg-light rounded-circle p-2 me-2">
                                <i class="fas fa-store text-purple"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-0"><?= htmlspecialchars($shop_data['shop_name']) ?></h5>
                        </div>
                        <span class="badge bg-light text-dark rounded-pill border">
                            <?= count($shop_data['items']) ?> รายการ
                        </span>
                    </div>

                    <div class="card-body p-0">
                        <?php foreach ($shop_data['items'] as $item): ?>
                            <?php 
                                $img = $item['image'];
                                if(empty($img) || !filter_var($img, FILTER_VALIDATE_URL)) {
                                    $img = 'uploads/kanom/' . $img;
                                }
                            ?>
                            <div class="p-3 border-bottom cart-item-row bg-white">
                                <div class="row align-items-center">
                                    <div class="col-md-6 d-flex align-items-center gap-3 mb-3 mb-md-0">
                                        <a href="process/cart_action.php?action=remove&id=<?= $item['id'] ?>" class="text-muted hover-danger" title="ลบรายการนี้" onclick="return confirm('ลบสินค้านี้?');">
                                            <i class="fas fa-times-circle"></i>
                                        </a>
                                        <div class="position-relative">
                                            <img src="<?= $img ?>" 
                                                 onerror="this.src='https://placehold.co/100x100?text=No+Image'"
                                                 class="rounded-3 border" 
                                                 width="80" height="80" style="object-fit: cover;">
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark"><?= htmlspecialchars($item['name']) ?></h6>
                                            <div class="text-magenta small fw-bold mt-1">
                                                ฿<?= number_format($item['price']) ?> / ชิ้น
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-6 col-md-3 text-center">
                                        <div class="input-group input-group-sm mx-auto" style="width: 100px;">
                                            <input type="number" 
                                                   name="qty[<?= $item['id'] ?>]" 
                                                   value="<?= $item['qty'] ?>" 
                                                   min="1" 
                                                   class="form-control text-center fw-bold bg-light border-0">
                                        </div>
                                    </div>

                                    <div class="col-6 col-md-3 text-end">
                                        <h6 class="fw-bold text-dark mb-0">฿<?= number_format($item['subtotal']) ?></h6>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="card-footer bg-light border-0 py-3">
                        <div class="row align-items-center">
                            
                            <div class="col-md-5 mb-3 mb-md-0 d-flex gap-2">
                                
                                <a href="shop_detail.php?id=<?= $shop_id ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                    <i class="fas fa-store me-1"></i> ซื้อเพิ่ม
                                </a>

                                <button type="submit" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                                    <i class="fas fa-sync-alt me-1"></i> คำนวณใหม่
                                </button>
                            </div>

                            <div class="col-md-7 text-end">
                                <div class="d-flex justify-content-md-end justify-content-between align-items-center gap-3">
                                    <div>
                                        <small class="text-muted d-block text-end">ยอดรวมร้านนี้</small>
                                        <h5 class="fw-bold text-magenta mb-0">฿<?= number_format($shop_data['total_price'], 2) ?></h5>
                                    </div>
                                    
                                    <a href="checkout.php?shop_id=<?= $shop_id ?>" class="btn btn-nia rounded-pill px-4 shadow-sm fw-bold">
                                        ชำระเงิน <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>

        </form>
    <?php endif; ?>
</div>

<style>
    .hover-danger:hover { color: #dc3545 !important; cursor: pointer; }
    .form-control:focus { box-shadow: none; border: 1px solid var(--nia-magenta); }
</style>

<?php include 'includes/footer.php'; ?>