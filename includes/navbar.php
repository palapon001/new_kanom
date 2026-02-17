<?php
if (!isset($path_prefix)) $path_prefix = '';
$current_page = basename($_SERVER['PHP_SELF']);

// 🛑 1. ถ้าเป็น ADMIN ให้ไปเรียก Sidebar แทน
if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
    include 'admin_sidebar.php';
    return;
}

// =================================================================
// 🛍️ Navbar for User / Shop / Guest
// =================================================================

$cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;

// Config Colors
$navbar_bg   = $config['theme']['colors']['secondary']   ?? '#2D1F57';
$navbar_text = $config['theme']['colors']['navbar_text'] ?? '#ffffff';
$accent_color = $config['theme']['colors']['accent']     ?? '#FDB913';
?>

<nav class="navbar navbar-expand-lg py-3 sticky-top transition-all" 
     style="background-color: <?= $navbar_bg ?>; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
    
    <div class="container">
        
        <a class="navbar-brand d-flex align-items-center gap-3" href="<?= $path_prefix ?>index.php" style="color: <?= $navbar_text ?> !important;">
            <div class="logo-wrapper bg-white shadow-sm d-flex justify-content-center align-items-center">
                <img src="<?= $path_prefix ?>assets/icon.svg" alt="Logo" class="img-fluid">
            </div>
            <div class="d-flex flex-column lh-1 brand-text">
                <span class="fw-bold text-uppercase fs-5 ls-1"><?= $config['app']['name'] ?></span>
                <span class="opacity-75 small fw-light" style="font-size: 0.75rem;"><?= $config['app']['desc'] ?></span>
            </div>
        </a>

        <button class="navbar-toggler border-0 p-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar">
            <div class="hamburger-icon">
                <span style="background-color: <?= $navbar_text ?>;"></span>
                <span style="background-color: <?= $navbar_text ?>;"></span>
                <span style="background-color: <?= $navbar_text ?>;"></span>
            </div>
            <?php if ($cart_count > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle d-lg-none"></span>
            <?php endif; ?>
        </button>

        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar" 
             style="background-color: <?= $navbar_bg ?>;">
            
            <div class="offcanvas-header border-bottom border-white border-opacity-10">
                <h5 class="offcanvas-title fw-bold" style="color: <?= $navbar_text ?>;">เมนูหลัก</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
            </div>

            <div class="offcanvas-body">
                
                <form class="d-flex me-auto ms-lg-4 my-3 my-lg-0 w-100 search-form" style="max-width: 350px;" action="<?= $path_prefix ?>search.php">
                    <div class="input-group position-relative">
                        <input class="form-control rounded-pill border-0 ps-4 py-2" type="search" name="q" placeholder="ค้นหาเมนูอร่อย..." 
                               style="background: rgba(255,255,255,0.15); color: <?= $navbar_text ?>; padding-right: 40px;">
                        <button class="btn border-0 position-absolute end-0 top-50 translate-middle-y me-2 rounded-circle d-flex align-items-center justify-content-center" 
                                type="submit" style="width: 32px; height: 32px; background: <?= $accent_color ?>; z-index: 5;">
                            <i class="fas fa-search text-dark fa-sm"></i>
                        </button>
                    </div>
                </form>

                <ul class="navbar-nav justify-content-end flex-grow-1 align-items-center gap-lg-1">
                    
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom px-3 <?= ($current_page == 'index.php') ? 'active' : '' ?>" 
                           href="<?= $path_prefix ?>index.php" style="color: <?= $navbar_text ?> !important;">หน้าแรก</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom px-3 <?= ($current_page == 'market_price.php') ? 'active' : '' ?>" 
                           href="<?= $path_prefix ?>market_price.php" style="color: <?= $navbar_text ?> !important;">ราคากลาง</a>
                    </li>

                    <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'shop'): ?>
                        <li class="nav-item dropdown ms-lg-2">
                            <a class="nav-link dropdown-toggle btn-role shop px-3 py-2 rounded-pill fw-bold" href="#" data-bs-toggle="dropdown">
                                <i class="fas fa-store me-2"></i> ร้านค้า
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 mt-2 p-2">
                                <li><a class="dropdown-item rounded-3 mb-1" href="<?= $path_prefix ?>shop/dashboard.php"><i class="fas fa-chart-pie me-2 text-primary"></i> Dashboard</a></li>
                                <li><a class="dropdown-item rounded-3 mb-1" href="<?= $path_prefix ?>shop/menu_manage.php"><i class="fas fa-utensils me-2 text-warning"></i> จัดการเมนู</a></li>
                                <li><a class="dropdown-item rounded-3 mb-1" href="<?= $path_prefix ?>shop/order_list.php"><i class="fas fa-list-alt me-2 text-success"></i> คำสั่งซื้อ</a></li>
                                <li><hr class="dropdown-divider mx-2"></li>
                                <li><a class="dropdown-item rounded-3 text-danger" href="<?= $path_prefix ?>logout.php"><i class="fas fa-sign-out-alt me-2"></i> ออกจากระบบ</a></li>
                            </ul>
                        </li>

                    <?php elseif (isset($_SESSION['user_id']) && $_SESSION['role'] == 'user'): ?>
                        <li class="nav-item me-2">
                            <a class="nav-link position-relative cart-btn" href="<?= $path_prefix ?>cart.php" style="color: <?= $navbar_text ?> !important;">
                                <i class="fas fa-shopping-basket fa-lg"></i>
                                <?php if ($cart_count > 0): ?>
                                    <span class="cart-badge badge rounded-pill bg-danger border border-2 border-white">
                                        <?= $cart_count ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link profile-toggle p-0" href="#" data-bs-toggle="dropdown">
                                <img src="<?= !empty($_SESSION['profile_image']) ? $_SESSION['profile_image'] : 'https://placehold.co/50' ?>" 
                                     class="rounded-circle border border-2 border-white shadow-sm" width="40" height="40" style="object-fit: cover;">
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 mt-3 p-2">
                                <li class="px-3 py-2 text-muted small fw-bold">สวัสดี, <?= $_SESSION['username'] ?? 'User' ?></li>
                                <li><hr class="dropdown-divider mx-2"></li>
                                <li><a class="dropdown-item rounded-3 mb-1" href="<?= $path_prefix ?>profile.php"><i class="fas fa-user me-2 text-primary"></i> โปรไฟล์</a></li>
                                <li><a class="dropdown-item rounded-3 mb-1" href="<?= $path_prefix ?>my_orders.php"><i class="fas fa-history me-2 text-success"></i> ประวัติการสั่งซื้อ</a></li>
                                <li><a class="dropdown-item rounded-3 text-danger" href="<?= $path_prefix ?>logout.php"><i class="fas fa-sign-out-alt me-2"></i> ออกจากระบบ</a></li>
                            </ul>
                        </li>

                    <?php else: ?>
                        <li class="nav-item d-flex align-items-center gap-2 mt-3 mt-lg-0 ms-lg-3">
                            <a href="<?= $path_prefix ?>login.php" class="btn btn-outline-light rounded-pill px-4 fw-bold login-btn"
                               style="color: <?= $navbar_text ?>; border-color: <?= $navbar_text ?>;">
                               เข้าสู่ระบบ
                            </a>
                            <a href="<?= $path_prefix ?>register.php" class="btn rounded-pill px-4 fw-bold register-btn" 
                               style="background-color: <?= $accent_color ?>; color: #000;">
                               สมัครสมาชิก
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</nav>

<style>
    /* 1. Logo Styling */
    .logo-wrapper {
        width: 45px; height: 45px; border-radius: 12px;
        transform: rotate(-5deg); transition: transform 0.3s ease;
    }
    .navbar-brand:hover .logo-wrapper { transform: rotate(0deg) scale(1.1); }
    .ls-1 { letter-spacing: 1px; }

    /* 2. Search Bar Placeholder Color */
    .search-form input::placeholder { color: <?= $navbar_text ?>; opacity: 0.7; }
    .search-form input:focus { 
        background: rgba(255,255,255,0.25) !important; 
        box-shadow: none; 
        color: <?= $navbar_text ?>; 
    }

    /* 3. Link Animation (Underline Effect) */
    .nav-link-custom {
        position: relative;
        font-weight: 500;
        opacity: 0.9;
        transition: all 0.3s;
    }
    .nav-link-custom:hover, .nav-link-custom.active {
        opacity: 1;
        transform: translateY(-2px);
    }
    .nav-link-custom::after {
        content: ''; position: absolute; width: 0; height: 3px;
        bottom: 0; left: 50%; background-color: <?= $accent_color ?>;
        transition: all 0.3s ease; border-radius: 10px;
        transform: translateX(-50%);
    }
    .nav-link-custom:hover::after, .nav-link-custom.active::after { width: 60%; }

    /* 4. Role Button Style */
    .btn-role.shop { background: rgba(255,255,255,0.15); color: #fff !important; backdrop-filter: blur(5px); }
    .btn-role:hover { background: rgba(255,255,255,0.25); transform: translateY(-2px); }

    /* 5. Cart Icon Animation */
    .cart-btn:hover i { animation: swing 0.8s ease; }
    .cart-badge { position: absolute; top: 0; right: 0; transform: translate(30%, -20%); animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }

    /* 6. Dropdown Menu (Clean & Modern) */
    .dropdown-menu { 
        background-color: #ffffff !important; 
        animation: slideDown 0.3s ease forwards;
        transform-origin: top center;
    }
    .dropdown-item { color: #333 !important; transition: all 0.2s; border-left: 3px solid transparent; }
    .dropdown-item:hover { 
        background-color: #f8f9fa; 
        color: <?= $config['theme']['colors']['primary'] ?? '#E6007E' ?> !important;
        border-left: 3px solid <?= $accent_color ?>;
        padding-left: 1.5rem;
    }

    /* 7. Button Hover Effects */
    .login-btn:hover { background: rgba(255,255,255,0.1); }
    .register-btn:hover { transform: scale(1.05); box-shadow: 0 4px 15px rgba(0,0,0,0.2); }

    /* 8. Hamburger Icon (Custom) */
    .hamburger-icon { width: 30px; height: 20px; position: relative; display: flex; flex-direction: column; justify-content: space-between; }
    .hamburger-icon span { display: block; height: 3px; width: 100%; border-radius: 3px; transition: all 0.3s; }
    
    /* Animations */
    @keyframes swing { 20% { transform: rotate(15deg); } 40% { transform: rotate(-10deg); } 60% { transform: rotate(5deg); } 80% { transform: rotate(-5deg); } 100% { transform: rotate(0deg); } }
    @keyframes popIn { from { transform: translate(30%, -20%) scale(0); } to { transform: translate(30%, -20%) scale(1); } }
    @keyframes slideDown { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>