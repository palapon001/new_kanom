<?php
session_start();
require_once 'config.php';
require_once 'function.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบก่อนเลือกซื้อสินค้า';
    header("Location: login.php");
    exit();
}

// 1. ตั้งค่า Category Map
$category_map = [
    'dessert'  => 'ขนมหวาน',
    'material' => 'วัตถุดิบ',
    'souvenir' => 'ของฝาก'
];

// --- ⚙️ ส่วนตั้งค่า Pagination ---
$limit = 8; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// 2. รับค่า ID ร้านค้า และ หมวดหมู่ (เริ่มต้นที่ dessert)
$shop_id = $_GET['id'] ?? 0;
$current_cat = $_GET['cat'] ?? 'dessert'; 

// ตรวจสอบว่าหมวดหมู่ที่ส่งมาอยู่ใน Map หรือไม่ (ป้องกันการกรอกมั่ว)
if (!array_key_exists($current_cat, $category_map)) {
    $current_cat = 'dessert';
}

// 3. ดึงข้อมูลร้านค้า
$shop = selectOne("SELECT * FROM users WHERE id = ? AND role = 'shop'", [$shop_id]);
if (!$shop) {
    $_SESSION['error'] = 'ไม่พบข้อมูลร้านค้าที่คุณต้องการ';
    header("Location: index.php");
    exit();
}

// 4. นับจำนวนสินค้าในหมวดหมู่ที่เลือก
$sql_count = "SELECT COUNT(*) as total FROM products WHERE shop_id = ? AND status = 'active' AND category = ?";
$total_products = selectOne($sql_count, [$shop_id, $current_cat])['total'];
$total_pages = ceil($total_products / $limit);

// 5. ดึงสินค้าแบบแบ่งหน้า
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
        <div class="row align-items-center text-center text-md-start">
            <div class="col-md-auto mb-3 mb-md-0">
                <img src="<?= (!empty($shop['profile_image'])) ? (filter_var($shop['profile_image'], FILTER_VALIDATE_URL) ? $shop['profile_image'] : 'uploads/profiles/'.$shop['profile_image']) : 'https://placehold.co/130x130?text=Shop' ?>" 
                     class="rounded-circle shadow-sm border border-4 border-white" width="130" height="130" style="object-fit: cover;">
            </div>
            <div class="col-md">
                <h2 class="fw-bold text-dark mb-1"><?= htmlspecialchars($shop['shop_name']) ?></h2>
                <p class="text-muted mb-0"><i class="fas fa-map-marker-alt me-2 text-danger"></i><?= htmlspecialchars($shop['address'] ?? 'เพชรบุรี') ?></p>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    
    <div class="row mb-5">
        <div class="col-12 d-flex justify-content-center">
            <div class="nav nav-pills bg-white p-2 rounded-pill shadow-sm border">
                <?php foreach($category_map as $key => $name): ?>
                    <a href="?id=<?= $shop_id ?>&cat=<?= $key ?>" 
                       class="nav-link rounded-pill px-4 <?= $current_cat == $key ? 'active bg-purple text-white shadow' : 'text-muted' ?>">
                       <?= $name ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark mb-0">รายการสินค้า: <span class="text-purple"><?= $category_map[$current_cat] ?></span></h4>
        <span class="text-muted small">พบ <?= $total_products ?> รายการ</span>
    </div>

    <div class="row g-4 mb-5">
        <?php if (count($products) > 0): ?>
            <?php foreach ($products as $p): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm product-card transition-up" style="border-radius: 20px; overflow: hidden;">
                        <img src="<?= (!empty($p['image'])) ? (filter_var($p['image'], FILTER_VALIDATE_URL) ? $p['image'] : 'uploads/kanom/'.$p['image']) : 'https://placehold.co/400x300?text=No+Image' ?>" 
                             class="card-img-top" style="height: 180px; object-fit: cover;">
                        <div class="card-body p-4 d-flex flex-column">
                            <h6 class="fw-bold text-dark text-truncate mb-1"><?= htmlspecialchars($p['name']) ?></h6>
                            <p class="text-muted small mb-3 two-lines flex-grow-1"><?= htmlspecialchars($p['description']) ?></p>
                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <span class="text-purple fw-bold h5 mb-0">฿<?= number_format($p['price']) ?></span>
                                <form action="process/cart_action.php" method="POST">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                    <input type="hidden" name="qty" value="1"> 
                                    <button type="submit" class="btn btn-purple btn-sm rounded-pill px-3">
                                        <i class="fas fa-plus me-1"></i> เพิ่ม
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="py-5 bg-light rounded-4 border border-dashed">
                    <i class="fas fa-box-open fa-3x text-muted mb-3 opacity-25"></i>
                    <p class="text-muted mb-0">ขณะนี้ยังไม่มีสินค้าในหมวดหมู่ <?= $category_map[$current_cat] ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($total_pages > 1): ?>
    <nav>
        <ul class="pagination justify-content-center">
            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                <a class="page-link border-0 shadow-sm rounded-circle me-2" href="?id=<?= $shop_id ?>&cat=<?= $current_cat ?>&page=<?= $page - 1 ?>">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item mx-1">
                    <a class="page-link border-0 shadow-sm rounded-circle <?= ($page == $i) ? 'active bg-purple text-white' : 'text-muted' ?>" 
                       href="?id=<?= $shop_id ?>&cat=<?= $current_cat ?>&page=<?= $i ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                <a class="page-link border-0 shadow-sm rounded-circle ms-2" href="?id=<?= $shop_id ?>&cat=<?= $current_cat ?>&page=<?= $page + 1 ?>">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>

</div>

<style>
    :root { --nia-purple: #6f42c1; }
    .bg-purple { background-color: var(--nia-purple) !important; }
    .text-purple { color: var(--nia-purple) !important; }
    .btn-purple { background-color: var(--nia-purple); color: white; border-radius: 50px; transition: 0.3s; }
    .btn-purple:hover { background-color: #5a32a3; color: white; transform: scale(1.05); }
    .page-link { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; transition: 0.3s; }
    .transition-up:hover { transform: translateY(-8px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
    .two-lines { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .border-dashed { border-style: dashed !important; border-width: 2px !important; }
</style>

<?php include 'includes/footer.php'; ?>