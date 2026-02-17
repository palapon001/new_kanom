<?php
session_start();
require_once 'config.php';
require_once 'function.php';

// 1. ดึงสินค้ามาใหม่ (Latest 8)
$latest_products = select("SELECT * FROM products WHERE status = 'active' ORDER BY created_at DESC LIMIT 8");

// 2. ดึงร้านค้าแนะนำ (Random 4)
$shops = select("SELECT * FROM users WHERE role = 'shop' ORDER BY RAND() LIMIT 4");

$theme = $config['theme'];
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div id="sectionSearch" class="position-relative overflow-hidden py-4 py-lg-5"
    style="background: linear-gradient(135deg, var(--nia-magenta) 0%, var(--nia-purple) 100%);">

    <div class="position-absolute top-0 start-0 w-100 h-100 opacity-10" 
         style="background-image: url('https://www.transparenttextures.com/patterns/cubes.png'); pointer-events: none;"></div>

    <div class="container position-relative z-1 text-center py-3 py-lg-5">
        
        <span class="badge bg-white text-purple rounded-pill px-3 py-2 mb-3 shadow-sm animate__animated animate__fadeInDown">
            <i class="fas fa-store me-1"></i> ตลาดออนไลน์เมืองเพชร
        </span>
        
        <h1 class="display-5 display-md-3 fw-bold text-white mb-3 text-shadow animate__animated animate__fadeInUp">
            อาณาจักรขนมหวาน
        </h1>
        
        <p class="lead text-white-50 mb-4 mb-lg-5 fs-6 fs-md-4 animate__animated animate__fadeInUp px-3">
            รวมสุดยอดของดีเมืองเพชรบุรี ส่งตรงจากแหล่งผลิตถึงมือคุณ
        </p>

        <div class="row justify-content-center animate__animated animate__fadeInUp">
            <div class="col-12 col-md-8 col-lg-6">
                <form action="search.php" method="GET" class="position-relative px-2 px-md-0">
                    <input type="text" name="q" 
                           class="form-control form-control-lg rounded-pill shadow-lg border-0 ps-4 py-3 fs-6 fs-md-5"
                           style="padding-right: 110px;" 
                           placeholder="ค้นหาเมนูโปรด... (เช่น หม้อแกง)">
                    
                    <button type="submit" 
                            class="btn btn-purple rounded-pill position-absolute top-50 end-0 translate-middle-y me-3 me-md-2 px-3 px-md-4 py-2 fw-bold shadow-sm"
                            style="z-index: 5;">
                        <i class="fas fa-search"></i> 
                        <span class="d-none d-md-inline ms-1">ค้นหา</span>
                    </button>
                </form>

                <div class="mt-3 d-flex justify-content-center align-items-center gap-2 flex-wrap px-2">
                    <span class="text-white-50 small text-nowrap">คำค้นยอดฮิต:</span>
                    <a href="search.php?q=หม้อแกง" class="badge bg-white bg-opacity-10 text-white text-decoration-none fw-normal border border-white border-opacity-25 hover-up transition-all py-2 px-3 rounded-pill">#หม้อแกง</a>
                    <a href="search.php?q=น้ำตาลโตนด" class="badge bg-white bg-opacity-10 text-white text-decoration-none fw-normal border border-white border-opacity-25 hover-up transition-all py-2 px-3 rounded-pill">#น้ำตาลโตนด</a>
                    <a href="search.php?q=บ้าบิ่น" class="badge bg-white bg-opacity-10 text-white text-decoration-none fw-normal border border-white border-opacity-25 hover-up transition-all py-2 px-3 rounded-pill">#บ้าบิ่น</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="text-center mb-5">
        <h6 class="text-purple fw-bold text-uppercase ls-1">Browse by Category</h6>
        <h2 class="fw-bold">เลือกช้อปตามหมวดหมู่</h2>
    </div>

    <div class="row g-4 justify-content-center">
        <?php foreach ($category_map as $key => $info): ?>
            <div class="col-6 col-md-3">
                <a href="search.php?category=<?= $key ?>" class="text-decoration-none">
                    <div class="card h-100 border-0 shadow-sm text-center py-4 rounded-4 hover-up transition-all">
                        <div class="mb-3 d-inline-block p-3 rounded-circle <?= $info['color'] ?> bg-opacity-10 text-dark">
                            <i class="fas <?= $info['icon'] ?> fa-2x <?= str_replace('bg-', 'text-', $info['color']) ?>"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-0"><?= $info['name'] ?></h6>
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
            <a href="search.php" class="btn btn-outline-purple rounded-pill px-4 fw-bold">
                ดูทั้งหมด
            </a>
        </div>

        <div class="row g-4">
            <?php foreach ($latest_products as $p): ?>
                <div class="col-6 col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden hover-up transition-all">
                        <div class="position-relative">
                            <img src="<?= (!empty($p['image'])) ? (filter_var($p['image'], FILTER_VALIDATE_URL) ? $p['image'] : 'uploads/kanom/' . $p['image']) : 'https://placehold.co/400x300?text=No+Image' ?>"
                                class="card-img-top" style="height: 200px; object-fit: cover;"
                                alt="<?= htmlspecialchars($p['name']) ?>">

                            <?php if ($p['category'] == 'dessert'): ?>
                                <span class="badge bg-warning text-dark position-absolute top-0 start-0 m-2 rounded-pill shadow-sm">New</span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title fw-bold text-truncate mb-1"><?= htmlspecialchars($p['name']) ?></h6>
                            <p class="small text-muted mb-3 text-truncate"><?= htmlspecialchars($p['description']) ?></p>

                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                <span class="h5 fw-bold text-purple mb-0">฿<?= number_format($p['price']) ?></span>
                                <a href="shop_detail.php?id=<?= $p['shop_id'] ?>" class="btn btn-sm btn-light rounded-circle shadow-sm text-purple" style="width: 32px; height: 32px; padding: 0; line-height: 32px;">
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
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
    <div class="row g-4">
        <?php foreach ($shops as $s): ?>
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm rounded-4 text-center py-4 hover-up transition-all">
                    <div class="card-body">
                        <img src="<?= (!empty($s['profile_image'])) ? (filter_var($s['profile_image'], FILTER_VALIDATE_URL) ? $s['profile_image'] : 'uploads/profiles/' . $s['profile_image']) : 'https://placehold.co/100x100?text=Shop' ?>"
                            class="rounded-circle shadow-sm mb-3 border" width="80" height="80" style="object-fit: cover;">

                        <h6 class="fw-bold text-truncate mb-1"><?= htmlspecialchars($s['shop_name']) ?></h6>
                        <p class="small text-muted mb-3"><i class="fas fa-map-marker-alt me-1 text-danger"></i> เพชรบุรี</p>

                        <a href="shop_detail.php?id=<?= $s['id'] ?>" class="btn btn-outline-dark btn-sm rounded-pill px-4">เยี่ยมชม</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

<?php include 'includes/footer.php'; ?>