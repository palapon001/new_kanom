<?php
session_start();
require_once 'config.php';
require_once 'function.php';

// 1. ดึงสินค้าแนะนำ (ล่าสุด 8 ชิ้น)
$latest_products = select("SELECT * FROM products WHERE status = 'active' ORDER BY created_at DESC LIMIT 8");

// 2. ดึงร้านค้าแนะนำ (Limit 4 ร้าน)
$shops = select("SELECT * FROM users WHERE role = 'shop' LIMIT 4");

$theme = $config['theme'];
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="bg-purple text-white py-5 position-relative overflow-hidden" 
     style="background: linear-gradient(135deg, <?= $theme['colors']['primary'] ?> 0%, <?= $theme['colors']['secondary'] ?> 100%);">
    
    <div style="position: absolute; top: 0; right: 0; width: 100%; height: 100%; background-image: url('https://www.transparenttextures.com/patterns/cubes.png'); opacity: 0.1;"></div>

    <div class="container position-relative z-1 text-center py-5">
        <h1 class="display-4 fw-bold mb-3">อาณาจักรขนมหวานเมืองเพชร</h1>
        <p class="lead mb-4 opacity-75">ศูนย์รวมขนมไทย วัตถุดิบคุณภาพ และภูมิปัญญาท้องถิ่น ส่งตรงถึงมือคุณ</p>
        
        <div class="row justify-content-center">
            <div class="col-md-6">
                <form action="index.php" method="GET" class="input-group input-group-lg shadow rounded-pill overflow-hidden bg-white p-1">
                    <input type="text" name="q" class="form-control border-0 ps-4" placeholder="ค้นหาขนม, ร้านค้า...">
                    <button class="btn btn-nia rounded-pill px-4 fw-bold" type="submit">ค้นหา</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="text-center mb-4">
        <h6 class="text-purple fw-bold text-uppercase ls-2" style="font-size: 0.8rem;">Categories</h6>
        <h3 class="fw-bold">เลือกช้อปตามหมวดหมู่</h3>
    </div>
    
    <div class="row g-3 justify-content-center">
        <?php 
            $cats = [
                ['name'=>'ขนมหวาน', 'icon'=>'fa-candy-cane', 'color'=>'#FF6B6B'],
                ['name'=>'วัตถุดิบ', 'icon'=>'fa-seedling', 'color'=>'#4ECDC4'],
                ['name'=>'ของฝาก', 'icon'=>'fa-gift', 'color'=>'#FFE66D'],
                ['name'=>'เครื่องดื่ม', 'icon'=>'fa-coffee', 'color'=>'#1A535C']
            ];
            foreach($cats as $c):
        ?>
        <div class="col-6 col-md-3 col-lg-2"> <div class="card border-0 shadow-sm h-100 text-center py-3 hover-up" style="border-radius: 16px; cursor: pointer; transition: 0.2s;">
                <div class="mb-2 text-white rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm" 
                     style="width: 50px; height: 50px; background-color: <?= $c['color'] ?>;">
                    <i class="fas <?= $c['icon'] ?>"></i>
                </div>
                <h6 class="fw-bold mb-0 text-dark small"><?= $c['name'] ?></h6>
            </div>
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
            <a href="#" class="text-decoration-none fw-bold text-purple">ดูทั้งหมด <i class="fas fa-arrow-right"></i></a>
        </div>

        <div class="row g-4">
            <?php foreach ($latest_products as $p): ?>
            <div class="col-6 col-md-3">
                <div class="card h-100 border-0 shadow-sm product-card" style="border-radius: 16px; overflow: hidden; transition: 0.3s;">
                    <a href="shop_detail.php?id=<?= $p['shop_id'] ?>" class="text-decoration-none text-dark">
                        <div class="position-relative">
                            <?php 
                                $img = $p['image'];
                                if(!filter_var($img, FILTER_VALIDATE_URL)) $img = 'uploads/kanom/'.$img;
                            ?>
                            <img src="<?= $img ?>" 
                                 class="card-img-top" 
                                 alt="<?= htmlspecialchars($p['name']) ?>" 
                                 style="height: 200px; object-fit: cover;"
                                 onerror="this.onerror=null; this.src='https://placehold.co/400x300?text=No+Image';">
                            
                            <?php if($p['category']=='dessert'): ?>
                                <span class="position-absolute top-0 start-0 m-2 badge bg-warning text-dark rounded-pill shadow-sm">แนะนำ</span>
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
                        if(!filter_var($profile, FILTER_VALIDATE_URL) && !empty($profile)) $profile = 'uploads/profiles/'.$profile;
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
    .hover-up:hover { transform: translateY(-5px); transition: 0.3s; }
    .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1)!important; }
</style>

<?php include 'includes/footer.php'; ?>