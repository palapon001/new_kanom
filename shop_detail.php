<?php
session_start();
require_once 'config.php';
require_once 'function.php';

// 1. ตรวจสอบ Login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบก่อนเลือกซื้อสินค้า';
    header("Location: login.php");
    exit();
} 

// --- ⚙️ Pagination Setup ---
$limit = 8; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// 3. รับค่าและตรวจสอบ
$shop_id = $_GET['id'] ?? 0;
$current_cat = $_GET['cat'] ?? 'dessert'; 

if (!array_key_exists($current_cat, $category_map)) {
    $current_cat = 'dessert';
}

// 4. ดึงข้อมูลร้านค้า
$shop = selectOne("SELECT * FROM users WHERE id = ? AND role = 'shop'", [$shop_id]);

if (!$shop) {
    $_SESSION['error'] = 'ไม่พบข้อมูลร้านค้าที่คุณต้องการ';
    header("Location: index.php");
    exit();
}

// 5. Query ข้อมูลสินค้า
$sql_count = "SELECT COUNT(*) as total FROM products WHERE shop_id = ? AND status = 'active' AND category = ?";
$total_products = selectOne($sql_count, [$shop_id, $current_cat])['total'];
$total_pages = ceil($total_products / $limit);

$sql_products = "SELECT * FROM products 
                 WHERE shop_id = ? AND status = 'active' AND category = ? 
                 ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$products = select($sql_products, [$shop_id, $current_cat]);

$theme = $config['theme'];
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="bg-white border-bottom py-5">
    <div class="container">
        <div class="row align-items-center g-4">
            
            <div class="col-lg-7 text-center text-lg-start">
                <div class="d-flex flex-column flex-lg-row align-items-center gap-4">
                    <img src="<?= (!empty($shop['profile_image'])) ? (filter_var($shop['profile_image'], FILTER_VALIDATE_URL) ? $shop['profile_image'] : 'uploads/profiles/'.$shop['profile_image']) : 'https://placehold.co/150x150?text=Shop' ?>" 
                         class="rounded-circle shadow-sm border" width="120" height="120" style="object-fit: cover;">
                    
                    <div>
                        <h2 class="fw-bold mb-2"><?= htmlspecialchars($shop['shop_name']) ?></h2>
                        <p class="text-muted mb-3">
                            <i class="fas fa-map-marker-alt text-danger me-2"></i><?= htmlspecialchars($shop['address'] ?? 'ไม่ระบุที่อยู่') ?>
                        </p>
                        <div class="d-flex gap-2 justify-content-center justify-content-lg-start">
                            <a href="tel:<?= $shop['phone'] ?>" class="btn btn-outline-dark rounded-pill px-4">
                                <i class="fas fa-phone me-2"></i> โทรติดต่อ
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <?php if (!empty($shop['latitude']) && !empty($shop['longitude'])): ?>
                    <div class="ratio ratio-21x9 rounded-3 overflow-hidden shadow-sm border">
                        <iframe 
                            src="https://maps.google.com/maps?q=<?= $shop['latitude'] ?>,<?= $shop['longitude'] ?>&z=15&output=embed" 
                            style="border:0;" loading="lazy">
                        </iframe>
                    </div>
                <?php else: ?>
                    <div class="bg-light rounded-3 d-flex align-items-center justify-content-center text-muted" style="height: 150px;">
                        <small><i class="fas fa-map-marker-alt me-1"></i> ไม่ระบุพิกัดร้านค้า</small>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<div class="container py-5">
    
    <div class="row mb-5">
        <div class="col-12 d-flex justify-content-center">
            <div class="nav nav-pills bg-white shadow-sm rounded-pill p-2 border">
                <?php foreach($category_map as $key => $name): ?>
                    <a href="?id=<?= $shop_id ?>&cat=<?= $key ?>" 
                       class="nav-link rounded-pill px-4 <?= $current_cat == $key ? 'active bg-primary' : 'text-secondary' ?>">
                       <?= $name['name'] ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-end mb-4 border-bottom pb-2">
        <h4 class="fw-bold mb-0">
            <i class="fas fa-tag me-2 text-primary"></i><?= $category_map[$current_cat]['name'] ?>
        </h4>
        <span class="text-muted small">ทั้งหมด <?= $total_products ?> รายการ</span>
    </div>

    <div class="row g-4 mb-5">
        <?php if (count($products) > 0): ?>
            <?php foreach ($products as $p): ?>
                <div class="col-sm-6 col-lg-3">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="position-relative">
                            <img src="<?= (!empty($p['image'])) ? (filter_var($p['image'], FILTER_VALIDATE_URL) ? $p['image'] : 'uploads/kanom/'.$p['image']) : 'https://placehold.co/400x300?text=No+Image' ?>" 
                                 class="card-img-top" style="height: 200px; object-fit: cover;" alt="<?= htmlspecialchars($p['name']) ?>">
                            
                            <?php if($p['status'] == 'inactive'): ?>
                                <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-flex align-items-center justify-content-center text-white fw-bold">
                                    สินค้าหมด
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title fw-bold text-truncate"><?= htmlspecialchars($p['name']) ?></h6>
                            <p class="card-text small text-muted text-truncate mb-3"><?= htmlspecialchars($p['description']) ?></p>
                            
                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                <span class="h5 mb-0 fw-bold text-primary">฿<?= number_format($p['price']) ?></span>
                                
                                <form action="process/cart_action.php" method="POST">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                    <input type="hidden" name="qty" value="1"> 
                                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3" <?= $p['status'] == 'inactive' ? 'disabled' : '' ?>>
                                        <i class="fas fa-cart-plus me-1"></i> เพิ่ม
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="text-muted opacity-50 mb-2">
                    <i class="fas fa-box-open fa-3x"></i>
                </div>
                <p class="text-muted">ยังไม่มีสินค้าในหมวดหมู่นี้</p>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($total_pages > 1): ?>
    <nav>
        <ul class="pagination justify-content-center">
            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                <a class="page-link rounded-circle mx-1 border-0 shadow-sm" href="?id=<?= $shop_id ?>&cat=<?= $current_cat ?>&page=<?= $page - 1 ?>">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                    <a class="page-link rounded-circle mx-1 border-0 shadow-sm" href="?id=<?= $shop_id ?>&cat=<?= $current_cat ?>&page=<?= $i ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                <a class="page-link rounded-circle mx-1 border-0 shadow-sm" href="?id=<?= $shop_id ?>&cat=<?= $current_cat ?>&page=<?= $page + 1 ?>">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>