<?php
// 1. ป้องกัน Error และกำหนด Path
if (!isset($path_prefix)) $path_prefix = '';

// 2. เช็คว่าเป็นหน้าไหน
$current_page = basename($_SERVER['PHP_SELF']);

// 3. นับจำนวนสินค้าในตะกร้า
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    $cart_count = array_sum($_SESSION['cart']);
}

// 4. Query ราคากลาง (ทำครั้งเดียวตรงนี้เลย)
$sql_market = "SELECT 
                c.name, c.unit, 
                AVG(p.price) as avg_price
               FROM central_ingredients c
               JOIN products p ON c.id = p.central_id AND p.status = 'active'
               GROUP BY c.id 
               HAVING avg_price > 0
               ORDER BY RAND() LIMIT 10";
$market_prices = select($sql_market);
$count_items = count($market_prices);
?>

<nav class="navbar navbar-expand-lg navbar-dark navbar-nia py-3 sticky-top shadow-sm"
    style="background-color: <?= $config['theme']['colors']['secondary'] ?>;">

    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="<?= $path_prefix ?>index.php">
            <div class="bg-white rounded-circle d-flex justify-content-center align-items-center me-2 shadow-sm" 
                 style="width: 45px; height: 45px; padding: 5px;">
                <img src="<?= $path_prefix ?>assets/icon.svg" alt="Logo" 
                     width="100%" height="100%" style="object-fit: contain;">
            </div>
            <div class="d-flex flex-column">
                <span class="fw-bold text-uppercase ls-1 fs-5"><?= $config['app']['name'] ?></span>
                <span style="font-size: 0.65rem; opacity: 0.7; letter-spacing: 2px;"><?= $config['app']['desc'] ?></span>
            </div>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navContent">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <a class="nav-link px-3 <?= ($current_page == 'index.php') ? 'active' : '' ?>" href="<?= $path_prefix ?>index.php">หน้าแรก</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3 <?= ($current_page == 'market_price.php') ? 'active' : '' ?>" href="<?= $path_prefix ?>market_price.php">
                        <i class="fas fa-tags me-1"></i> ราคากลาง
                    </a>
                </li>

                <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link px-3" href="<?= $path_prefix ?>admin/dashboard.php">Admin Panel</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-outline-light btn-sm rounded-pill px-3" href="<?= $path_prefix ?>logout.php">Logout</a>
                    </li>
                <?php elseif (isset($_SESSION['user_id']) && $_SESSION['role'] == 'shop'): ?>
                    <li class="nav-item">
                        <a class="nav-link px-3" href="<?= $path_prefix ?>shop/dashboard.php">ร้านค้าของฉัน</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-outline-light btn-sm rounded-pill px-3" href="<?= $path_prefix ?>logout.php">Logout</a>
                    </li>
                <?php elseif (isset($_SESSION['user_id']) && $_SESSION['role'] == 'user'): ?>
                    <li class="nav-item me-2">
                        <a class="nav-link position-relative" href="<?= $path_prefix ?>cart.php">
                            <i class="fas fa-shopping-cart fa-lg"></i>
                            <?php if ($cart_count > 0): ?>
                                <span class="position-absolute top-10 start-90 translate-middle badge rounded-pill bg-danger border border-white" style="font-size: 0.6rem;">
                                    <?= $cart_count ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-outline-light btn-sm rounded-pill px-3" href="<?= $path_prefix ?>logout.php">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item d-none d-lg-block text-white-50 mx-2">|</li>
                    <li class="nav-item mt-3 mt-lg-0">
                        <a href="<?= $path_prefix ?>login.php" class="btn btn-outline-light btn-sm px-4 rounded-pill me-2">เข้าสู่ระบบ</a>
                        <a href="<?= $path_prefix ?>register.php" class="btn btn-outline-light btn-sm px-4 rounded-pill me-2">สมัครสมาชิก</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<?php if (!empty($market_prices)): ?>
    <div class="bg-dark py-2 overflow-hidden border-bottom" style="height: 40px;">
        <div class="container h-100">
            <div class="d-flex align-items-center h-100">

                <div class="flex-shrink-0 me-3 border-end border-secondary pe-3 z-1 bg-dark">
                    <span class="text-warning fw-bold small"><i class="fas fa-chart-line me-2"></i>ราคากลาง</span>
                </div>

                <div class="ticker-wrap w-100 overflow-hidden position-relative">
                    <div class="<?= ($count_items > 5) ? 'ticker' : 'd-flex align-items-center h-100' ?>">
                        <?php
                        // Logic: Loop 2 รอบถ้าสินค้า > 5, Loop 1 รอบถ้าน้อยกว่า
                        $loops = ($count_items > 5) ? 2 : 1;
                        
                        for ($i = 0; $i < $loops; $i++):
                            foreach ($market_prices as $mp):
                        ?>
                                <div class="ticker-item d-inline-flex align-items-center mx-4">
                                    <span class="text-white-50 small me-2"><?= htmlspecialchars($mp['name']) ?></span>
                                    <span class="fw-bold text-success small">฿<?= number_format($mp['avg_price'], 2) ?></span>
                                    <span class="badge bg-secondary text-white ms-1" style="font-size: 0.6rem;"><?= $mp['unit'] ?></span>
                                </div>
                        <?php
                            endforeach;
                        endfor;
                        ?>
                    </div>
                </div>

                <a href="<?= $path_prefix ?>market_price.php" class="flex-shrink-0 ms-3 text-white-50 text-decoration-none small hover-white z-1 bg-dark ps-2">
                    ดูทั้งหมด <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>

<style>
    .ticker-wrap {
        width: 100%;
        overflow: hidden;
        white-space: nowrap;
    }

    /* Class สำหรับทำให้ข้อความวิ่ง */
    .ticker {
        display: inline-block;
        animation: ticker 40s linear infinite; /* ปรับเวลาตามความชอบ */
    }

    .ticker-item {
        display: inline-block;
    }

    .ticker-wrap:hover .ticker {
        animation-play-state: paused;
    }

    @keyframes ticker {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }

    .hover-white:hover { color: white !important; }
    
    /* Responsive Adjustments */
    @media (max-width: 991px) {
        .navbar-brand img { height: 30px; }
        .fs-5 { font-size: 1rem !important; }
    }
</style>