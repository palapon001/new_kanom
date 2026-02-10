<?php
// 1. ป้องกัน Error และกำหนด Path
if (!isset($path_prefix)) $path_prefix = '';

// 2. เช็คว่าเป็นหน้าไหน เพื่อทำ Active Menu
$current_page = basename($_SERVER['PHP_SELF']);

// 3. นับจำนวนสินค้าในตะกร้า (ถ้ามี)
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    $cart_count = array_sum($_SESSION['cart']);
}
?>

<nav class="navbar navbar-expand-lg navbar-dark navbar-nia py-3 sticky-top shadow-sm"
    style="background-color: <?= $config['theme']['colors']['secondary'] ?>;">

    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="<?= $path_prefix ?>index.php">
            <div class="bg-white text-purple rounded-circle d-flex justify-content-center align-items-center me-2 shadow-sm"
                style="width: 40px; height: 40px;">
                <i class="fas fa-crown fa-sm" style="color: <?= $config['theme']['colors']['primary'] ?>;"></i>
            </div>
            <div class="d-flex flex-column">
                <span class="fw-bold text-uppercase ls-1 fs-5">KanomMuangPhet</span>
                <span style="font-size: 0.65rem; opacity: 0.7; letter-spacing: 2px;">SMART PLATFORM</span>
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

                <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link px-3 <?= ($current_page == 'dashboard.php') ? 'active' : '' ?>" href="<?= $path_prefix ?>admin/dashboard.php">
                            <i class="fas fa-chart-line me-1"></i> ภาพรวม
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 <?= ($current_page == 'users_manage.php') ? 'active' : '' ?>" href="<?= $path_prefix ?>admin/users_manage.php">
                            <i class="fas fa-users-cog me-1"></i> สมาชิก
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 <?= ($current_page == 'products_manage.php') ? 'active' : '' ?>" href="<?= $path_prefix ?>admin/products_manage.php">
                            <i class="fas fa-boxes me-1"></i> สินค้า
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 <?= ($current_page == 'orders_manage.php') ? 'active' : '' ?>" href="<?= $path_prefix ?>admin/orders_manage.php">
                            <i class="fas fa-file-invoice-dollar me-1"></i> ออเดอร์
                        </a>
                    </li>

                    <li class="nav-item ms-2">
                        <a class="btn btn-outline-light btn-sm rounded-pill px-3" href="<?= $path_prefix ?>logout.php">
                            Logout
                        </a>
                    </li>

                <?php elseif (isset($_SESSION['user_id']) && $_SESSION['role'] == 'shop'): ?>
                    <li class="nav-item">
                        <a class="nav-link px-3 <?= ($current_page == 'dashboard.php') ? 'active' : '' ?>" href="<?= $path_prefix ?>shop/dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <div class="dropdown ms-2">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                                <span class="me-2 text-white fw-bold"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                                <div class="bg-white text-purple rounded-circle d-flex justify-content-center align-items-center" style="width: 32px; height: 32px;">
                                    <? if (!empty($_SESSION['profile_image'])) { ?>
                                        <img src="<?= $path_prefix ?>uploads/profiles/<?= htmlspecialchars($_SESSION['profile_image']) ?>"
                                            style="width: 32px; height: 32px; object-fit: cover;">
                                         <? } else { ?>
                                        <i class="fas fa-store"></i>
                                    <? } ?>
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg mt-2 rounded-3">
                                <li><a class="dropdown-item" href="<?= $path_prefix ?>profile.php"><i class="fas fa-cog me-2 text-muted"></i>ตั้งค่าร้านค้า</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item text-danger" href="<?= $path_prefix ?>logout.php"><i class="fas fa-sign-out-alt me-2"></i>ออกจากระบบ</a></li>
                            </ul>
                        </div>
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
                    <li class="nav-item">
                        <div class="dropdown ms-2">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                                <span class="me-2 text-white"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                                <div class="bg-white text-purple rounded-circle d-flex justify-content-center align-items-center" style="width: 32px; height: 32px;">
                                    <i class="fas fa-user"></i>
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg mt-2 rounded-3">
                                <li><a class="dropdown-item" href="<?= $path_prefix ?>my_orders.php"><i class="fas fa-history me-2 text-muted"></i>ประวัติการสั่งซื้อ</a></li>
                                <li><a class="dropdown-item" href="<?= $path_prefix ?>profile.php"><i class="fas fa-user-edit me-2 text-muted"></i>แก้ไขข้อมูลส่วนตัว</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item text-danger" href="<?= $path_prefix ?>logout.php"><i class="fas fa-sign-out-alt me-2"></i>ออกจากระบบ</a></li>
                            </ul>
                        </div>
                    </li>

                <?php else: ?>
                    <li class="nav-item d-none d-lg-block text-white-50 mx-2">|</li>
                    <li class="nav-item mt-3 mt-lg-0">
                        <a href="<?= $path_prefix ?>login.php" class="btn btn-outline-light btn-sm px-4 rounded-pill me-2">
                            เข้าสู่ระบบ
                        </a>
                        <a href="<?= $path_prefix ?>register.php" class="btn btn-nia btn-sm shadow-sm px-4 rounded-pill">
                            สมัครสมาชิก
                        </a>
                    </li>
                <?php endif; ?>

            </ul>
        </div>
    </div>
</nav>

<div style="height: 4px; background: linear-gradient(90deg, <?= $config['theme']['colors']['primary'] ?> 0%, <?= $config['theme']['colors']['accent'] ?> 100%);"></div>