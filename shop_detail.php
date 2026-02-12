<?php
session_start();
// 1. เรียกใช้ Config และ Function
require_once 'config.php';
require_once 'function.php';

// 🔒 ส่วนที่เพิ่ม: ตรวจสอบการ Login
if (!isset($_SESSION['user_id'])) {
    // ถ้ายังไม่ Login ให้แจ้งเตือนและดีดไปหน้า Login
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบก่อนเลือกซื้อสินค้า';
    header("Location: login.php");
    exit();
}

// 2. รับค่า ID ร้านค้าจาก URL
$shop_id = $_GET['id'] ?? 0;

// 3. ดึงข้อมูลร้านค้า
$sql_shop = "SELECT * FROM users WHERE id = ? AND role = 'shop'";
$shop = selectOne($sql_shop, [$shop_id]);

// ถ้าไม่เจอร้านค้า ให้เด้งกลับหน้าแรก
if (!$shop) {
    echo "<script>alert('ไม่พบข้อมูลร้านค้า'); window.location.href='index.php';</script>";
    exit();
}

// 4. ดึงสินค้าของร้านนี้
$sql_products = "SELECT * FROM products WHERE shop_id = ? AND status = 'active'";
$products = select($sql_products, [$shop_id]);

$theme = $config['theme'];
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="bg-light py-5 position-relative overflow-hidden">
    <div style="position: absolute; top: -50px; right: -50px; width: 300px; height: 300px; background: <?= $theme['colors']['primary'] ?>; opacity: 0.05; border-radius: 50%; filter: blur(60px);"></div>
    
    <div class="container position-relative z-1">
        <div class="row align-items-center">
            <div class="col-md-3 text-center text-md-start mb-3 mb-md-0">
                <?php 
                    $profile_img = $shop['profile_image'] ?? '';
                    if(empty($profile_img)) {
                        $profile_img = 'https://placehold.co/150x150?text=Shop+Logo';
                    } elseif(!filter_var($profile_img, FILTER_VALIDATE_URL)){
                         $profile_img = 'uploads/profiles/' . $profile_img;
                    }
                ?>
                <img src="<?= $profile_img ?>" 
                     onerror="this.onerror=null; this.src='https://placehold.co/150x150?text=No+Image';"
                     class="rounded-circle shadow border border-4 border-white" 
                     width="150" height="150" style="object-fit: cover;">
            </div>
            <div class="col-md-6 text-center text-md-start">
                <h1 class="fw-bold text-purple mb-2"><?= htmlspecialchars($shop['shop_name']) ?></h1>
                <p class="text-muted mb-2"><i class="fas fa-map-marker-alt me-2 text-danger"></i><?= htmlspecialchars($shop['address'] ?? 'ไม่ระบุที่อยู่') ?></p>
                <div class="d-flex gap-2 justify-content-center justify-content-md-start">
                    <span class="badge bg-success bg-opacity-10 text-success border border-success px-3">
                        <i class="fas fa-check-circle me-1"></i> Verified Shop
                    </span>
                    <span class="badge bg-warning bg-opacity-10 text-dark border border-warning px-3">
                        <i class="fas fa-star me-1 text-warning"></i> 4.9 Rating
                    </span>
                </div>
            </div>
            <div class="col-md-3 text-center text-md-end mt-4 mt-md-0">
                <?php if(isset($shop['line_id'])): ?>
                    <a href="#" class="btn btn-success btn-lg shadow-sm w-100 mb-2 rounded-pill"><i class="fab fa-line me-2"></i> ติดต่อร้านค้า</a>
                <?php else: ?>
                    <a href="tel:<?= $shop['phone'] ?>" class="btn btn-outline-primary btn-lg shadow-sm w-100 mb-2 rounded-pill"><i class="fas fa-phone me-2"></i> โทรติดต่อ</a>
                <?php endif; ?>
                <p class="small text-muted mb-0">เปิดทำการ: 08:00 - 17:00 น.</p>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-end mb-4 border-bottom pb-2">
        <h3 class="fw-bold text-purple mb-0"><i class="fas fa-utensils me-2"></i>เมนูแนะนำ</h3>
        <span class="text-muted small">ทั้งหมด <?= count($products) ?> รายการ</span>
    </div>

    <div class="row g-4">
        <?php if (count($products) > 0): ?>
            <?php foreach ($products as $p): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm product-card" style="border-radius: 16px; overflow: hidden; transition: transform 0.3s;">
                        <div class="position-relative">
                            <?php 
                                $img_url = $p['image'];
                                if(empty($img_url)) {
                                    $img_url = 'https://placehold.co/400x300?text=No+Image'; // รูป Default ถ้าไม่มีชื่อไฟล์
                                } elseif(!filter_var($img_url, FILTER_VALIDATE_URL)){
                                     $img_url = 'uploads/kanom/' . $img_url; 
                                }
                            ?>
                            <img src="<?= $img_url ?>" 
                                 onerror="this.onerror=null; this.src='https://placehold.co/400x300?text=No+Image';"
                                 class="card-img-top" 
                                 alt="<?= htmlspecialchars($p['name']) ?>" 
                                 style="height: 200px; object-fit: cover;">
                                 
                            <span class="position-absolute top-0 end-0 m-2 badge bg-white text-dark shadow-sm rounded-pill">
                                <?= ucfirst($p['category']) ?>
                            </span>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fw-bold text-dark mb-1"><?= htmlspecialchars($p['name']) ?></h5>
                            <p class="card-text text-muted small flex-grow-1 two-lines"><?= htmlspecialchars($p['description']) ?></p>
                            
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <h5 class="fw-bold text-magenta mb-0">฿<?= number_format($p['price']) ?></h5>
                                
                                <form action="process/cart_action.php" method="POST">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                    <input type="hidden" name="qty" value="1"> 
                                    <button type="submit" class="btn btn-sm btn-outline-nia rounded-circle" title="เพิ่มลงตะกร้า">
                                        <i class="fas fa-cart-plus"></i>
                                    </button>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-box-open fa-3x text-muted mb-3 opacity-50"></i>
                <p class="text-muted">ร้านค้านี้ยังไม่มีรายการสินค้า</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    .two-lines {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .btn-outline-nia {
        color: var(--nia-magenta);
        border-color: var(--nia-magenta);
    }
    .btn-outline-nia:hover {
        background-color: var(--nia-magenta);
        color: white;
    }
</style>

<?php include 'includes/footer.php'; ?>