<?php
session_start();
require_once 'config.php';
require_once 'function.php';

// 1. ดึงสินค้ามาใหม่ (Latest 8)
// ไม่ต้องมี Logic ค้นหาแล้ว เพราะย้ายไป search.php
$latest_products = select("SELECT * FROM products WHERE status = 'active' ORDER BY created_at DESC LIMIT 8");

// 2. ดึงร้านค้าแนะนำ (Random 4)
$shops = select("SELECT * FROM users WHERE role = 'shop' ORDER BY RAND() LIMIT 4");

// 3. 🟢 ดึงราคากลาง (Market Price Slider)
$sql_market = "SELECT 
                c.name, c.unit, 
                AVG(p.price) as avg_price,
                MIN(p.price) as min_price,
                MAX(p.price) as max_price
               FROM central_ingredients c
               JOIN products p ON c.id = p.central_id AND p.status = 'active'
               GROUP BY c.id 
               HAVING avg_price > 0
               ORDER BY RAND() LIMIT 6";
$market_prices = select($sql_market);

$theme = $config['theme'];
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="bg-purple text-white py-5 position-relative overflow-hidden"
    style="background: linear-gradient(135deg, <?= $theme['colors']['primary'] ?> 0%, <?= $theme['colors']['secondary'] ?> 100%);">

    <div style="position: absolute; top: 0; right: 0; width: 100%; height: 100%; background-image: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 20px 20px; opacity: 0.5;"></div>

    <div class="container position-relative z-1 text-center py-5">
        <h1 class="display-4 fw-bold mb-3 text-shadow">อาณาจักรขนมหวานเมืองเพชร</h1>
        <p class="lead mb-4 opacity-90">ศูนย์รวมขนมไทย วัตถุดิบคุณภาพ และภูมิปัญญาท้องถิ่น ส่งตรงถึงมือคุณ</p>

        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <form action="search.php" method="GET" class="input-group input-group-lg shadow-lg rounded-pill overflow-hidden bg-white p-1">
                    <span class="input-group-text bg-white border-0 ps-3 text-muted"><i class="fas fa-search"></i></span>
                    <input type="text" name="q" class="form-control border-0 shadow-none" placeholder="ค้นหาขนม, วัตถุดิบ, ร้านค้า...">
                    <button class="btn btn-nia rounded-pill px-4 fw-bold shadow-sm" type="submit">ค้นหา</button>
                </form>

                <div class="mt-3 small text-white opacity-75">
                    <span class="fw-bold me-2">ยอดฮิต:</span>
                    <a href="search.php?q=หม้อแกง" class="text-white text-decoration-none me-2 hover-underline">#หม้อแกง</a>
                    <a href="search.php?q=น้ำตาลโตนด" class="text-white text-decoration-none me-2 hover-underline">#น้ำตาลโตนด</a>
                    <a href="search.php?q=ทองหยอด" class="text-white text-decoration-none hover-underline">#ทองหยอด</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($market_prices)): ?>
    <div class="container mt-n5 position-relative z-2">
        <div class="d-flex justify-content-between align-items-end mb-2 px-2">
            <div>
                <h5 class="fw-bold text-white text-shadow mb-0">
                    <i class="fas fa-tag me-2 text-warning"></i>ราคากลางวันนี้
                </h5>
                <small class="text-white-50" style="font-size: 0.8rem;">อัปเดตล่าสุดจากสินค้าในระบบ</small>
            </div>
            <a href="market_price.php" class="text-white text-decoration-none small fw-bold hover-scale">
                ดูทั้งหมด <i class="fas fa-chevron-right ms-1"></i>
            </a>
        </div>

        <div class="scrolling-wrapper d-flex gap-3 pb-4 px-1" style="overflow-x: auto; scroll-behavior: smooth; -webkit-overflow-scrolling: touch;">
            <?php foreach ($market_prices as $mp): ?>
                <div class="card border-0 shadow-sm rounded-4 flex-shrink-0 hover-up" style="width: 220px; min-width: 220px; cursor: default;">
                    <div class="card-body p-3 text-center d-flex flex-column justify-content-center">
                        <h6 class="fw-bold text-dark text-truncate mb-1"><?= htmlspecialchars($mp['name']) ?></h6>
                        <p class="text-muted small mb-2 bg-light rounded-pill d-inline-block px-2 mx-auto">
                            หน่วย: <?= htmlspecialchars($mp['unit']) ?>
                        </p>
                        <div class="my-2">
                            <span class="d-block small text-muted mb-1">ราคาเฉลี่ย</span>
                            <h3 class="fw-bold text-purple mb-0">฿<?= number_format($mp['avg_price'], 2) ?></h3>
                        </div>
                        <div class="d-flex justify-content-between px-3 mt-auto pt-2 border-top" style="font-size: 0.75rem;">
                            <span class="text-success" title="ราคาต่ำสุด"><i class="fas fa-arrow-down"></i> <?= number_format($mp['min_price']) ?></span>
                            <span class="text-danger" title="ราคาสูงสุด"><i class="fas fa-arrow-up"></i> <?= number_format($mp['max_price']) ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <a href="market_price.php" class="card border-0 shadow-sm rounded-4 flex-shrink-0 text-decoration-none bg-white hover-up d-flex justify-content-center align-items-center"
                style="width: 120px; min-width: 120px;">
                <div class="text-center text-purple">
                    <div class="bg-purple bg-opacity-10 rounded-circle d-inline-flex justify-content-center align-items-center mb-2" style="width: 50px; height: 50px;">
                        <i class="fas fa-arrow-right fa-lg"></i>
                    </div>
                    <span class="d-block fw-bold small">ดูรายการ<br>ทั้งหมด</span>
                </div>
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="py-5"></div>
<?php endif; ?>


<div class="container py-4">
    <div class="text-center mb-4">
        <h6 class="text-purple fw-bold text-uppercase ls-2" style="font-size: 0.8rem;">Categories</h6>
        <h3 class="fw-bold">เลือกช้อปตามหมวดหมู่</h3>
    </div>

    <div class="row g-3 justify-content-center">
        <?php
        $cats = [
            ['name' => 'ขนมหวาน', 'val' => 'dessert', 'icon' => 'fa-candy-cane', 'color' => '#FF6B6B'],
            ['name' => 'วัตถุดิบ', 'val' => 'material', 'icon' => 'fa-seedling', 'color' => '#4ECDC4'],
            ['name' => 'ของฝาก', 'val' => 'souvenir', 'icon' => 'fa-gift', 'color' => '#FFE66D'],
            ['name' => 'เครื่องดื่ม', 'val' => 'drink', 'icon' => 'fa-coffee', 'color' => '#1A535C']
        ];
        foreach ($cats as $c):
        ?>
            <div class="col-6 col-md-3 col-lg-2">
                <a href="search.php?category=<?= $c['val'] ?>" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 text-center py-3 hover-up" style="border-radius: 16px; cursor: pointer; transition: 0.2s;">
                        <div class="mb-2 text-white rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm"
                            style="width: 50px; height: 50px; background-color: <?= $c['color'] ?>;">
                            <i class="fas <?= $c['icon'] ?>"></i>
                        </div>
                        <h6 class="fw-bold mb-0 text-dark small"><?= $c['name'] ?></h6>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="bg-light py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <h3 class="fw-bold mb-1">สินค้ามาใหม่ 🔥</h3>
                <p class="text-muted small mb-0">อัปเดตความอร่อยล่าสุดส่งตรงจากเตา</p>
            </div>
            <a href="search.php" class="btn btn-outline-purple rounded-pill px-3 fw-bold">
                ดูทั้งหมด <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            <?php foreach ($latest_products as $p): ?>
                <div class="col-6 col-md-3">
                    <div class="card h-100 border-0 shadow-sm product-card" style="border-radius: 16px; overflow: hidden; transition: 0.3s;">
                        <a href="shop_detail.php?id=<?= $p['shop_id'] ?>" class="text-decoration-none text-dark">
                            <div class="position-relative">
                                <?php
                                $img = $p['image'];
                                if (!filter_var($img, FILTER_VALIDATE_URL)) {
                                    $img = 'uploads/kanom/' . $img;
                                }
                                ?>
                                <img src="<?= $img ?>"
                                    class="card-img-top"
                                    alt="<?= htmlspecialchars($p['name']) ?>"
                                    style="height: 200px; object-fit: cover;"
                                    onerror="this.onerror=null; this.src='https://placehold.co/400x300?text=No+Image';">

                                <?php if ($p['category'] == 'dessert'): ?>
                                    <span class="position-absolute top-0 start-0 m-2 badge bg-warning text-dark rounded-pill shadow-sm">สินค้าใหม่</span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body p-3">
                                <h6 class="card-title fw-bold mb-1 text-truncate"><?= htmlspecialchars($p['name']) ?></h6>
                                <p class="text-muted small mb-2 text-truncate"><?= htmlspecialchars($p['description']) ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-magenta">฿<?= number_format($p['price']) ?></span>
                                    <span class="btn btn-sm btn-light text-purple rounded-circle shadow-sm" style="width: 32px; height: 32px; padding: 0; line-height: 32px;"><i class="fas fa-shopping-basket"></i></span>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="text-center mb-5">
        <h3 class="fw-bold">ร้านค้าแนะนำ 🏪</h3>
    </div>
    <div class="row g-4 justify-content-center">
        <?php foreach ($shops as $s): ?>
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm text-center py-4 h-100 hover-up" style="border-radius: 16px;">
                    <div class="card-body">
                        <?php
                        $profile = $s['profile_image'] ?? '';
                        if (!filter_var($profile, FILTER_VALIDATE_URL) && !empty($profile)) $profile = 'uploads/profiles/' . $profile;
                        ?>
                        <img src="<?= $profile ?>"
                            class="rounded-circle shadow-sm mb-3 border border-3 border-white"
                            width="80" height="80"
                            style="object-fit: cover;"
                            onerror="this.onerror=null; this.src='https://placehold.co/100x100?text=Shop';">

                        <h5 class="fw-bold mb-1 text-truncate"><?= htmlspecialchars($s['shop_name']) ?></h5>
                        <p class="text-muted small mb-3"><i class="fas fa-map-marker-alt me-1 text-danger"></i> เพชรบุรี</p>
                        <a href="shop_detail.php?id=<?= $s['id'] ?>" class="btn btn-outline-nia rounded-pill btn-sm px-4">เยี่ยมชมร้าน</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
    /* CSS สำหรับ Horizontal Scrolling (Slider) */
    .scrolling-wrapper {
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    .scrolling-wrapper::-webkit-scrollbar { display: none; }
    .mt-n5 { margin-top: -3rem !important; }
    .hover-up:hover { transform: translateY(-5px); transition: 0.3s; }
    .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important; }
    .text-shadow { text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); }
    .hover-underline:hover { text-decoration: underline !important; }
    .btn-outline-purple { color: var(--nia-purple); border-color: var(--nia-purple); }
    .btn-outline-purple:hover { background-color: var(--nia-purple); color: white; }
    .hover-scale:hover { transform: scale(1.05); display: inline-block; transition: 0.2s; }
</style>

<?php include 'includes/footer.php'; ?>