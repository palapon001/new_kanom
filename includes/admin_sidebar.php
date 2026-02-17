<?php
// ดึงค่าสีจาก Config
$sidebar_bg   = $config['theme']['colors']['secondary']   ?? '#2D1F57';
$sidebar_text = $config['theme']['colors']['navbar_text'] ?? '#ffffff';
$accent_color = $config['theme']['colors']['accent']      ?? '#FDB913';
?>

<script>
    const savedState = localStorage.getItem('sidebarState');
    if (savedState === 'collapsed') {
        document.documentElement.style.setProperty('--sidebar-width', '80px');
        document.body.classList.add('sidebar-collapsed');
    } else {
        document.documentElement.style.setProperty('--sidebar-width', '260px');
    }
</script>

<nav class="navbar navbar-dark d-lg-none sticky-top p-3 shadow-sm" style="background-color: <?= $sidebar_bg ?>;">
    <button class="btn btn-outline-light border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar">
        <i class="fas fa-bars fa-lg"></i>
    </button>
    <span class="fw-bold text-white ms-2"><?= $config['app']['name'] ?> (Admin)</span>
</nav>

<div class="offcanvas-lg offcanvas-start bg-dark text-white h-100 position-fixed start-0 top-0 overflow-hidden shadow d-flex flex-column transition-all" 
     tabindex="-1" id="adminSidebar" 
     style="width: var(--sidebar-width, 260px); z-index: 1040; background-color: <?= $sidebar_bg ?> !important;">
    
    <div class="d-flex align-items-center p-3 border-bottom border-white border-opacity-10" style="height: 70px;">
        <div class="d-flex align-items-center flex-grow-1 overflow-hidden">
            <div class="bg-white rounded-circle d-flex justify-content-center align-items-center flex-shrink-0 p-1" style="width: 40px; height: 40px;">
                <img src="<?= $path_prefix ?>assets/icon.svg" alt="Logo" class="img-fluid">
            </div>
            <div class="lh-1 ms-3 sidebar-text fade-in">
                <div class="fw-bold fs-6 text-white text-nowrap">Admin Panel</div>
                <div class="small text-white-50 text-nowrap">จัดการระบบ</div>
            </div>
        </div>
        
        <button class="btn btn-link text-white-50 p-0 ms-2 d-none d-lg-block" id="sidebarToggle">
            <i class="fas fa-chevron-left transition-transform" id="toggleIcon"></i>
        </button>
        
        <button type="button" class="btn-close btn-close-white ms-auto d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#adminSidebar"></button>
    </div>

    <div class="p-3 flex-grow-1 overflow-y-auto scrollbar-thin">
        <ul class="nav nav-pills flex-column gap-2">
            
            <li class="nav-item">
                <a href="<?= $path_prefix ?>admin/dashboard.php" class="nav-link text-white d-flex align-items-center <?= ($current_page == 'dashboard.php') ? 'active' : '' ?>" title="Dashboard">
                    <div class="icon-wrapper text-center" style="width: 24px;"><i class="fas fa-tachometer-alt text-warning"></i></div>
                    <span class="ms-3 sidebar-text text-nowrap">Dashboard</span>
                </a>
            </li>

            <li class="nav-header text-uppercase small text-white-50 fw-bold mt-3 mb-2 px-3 sidebar-text text-nowrap">ข้อมูลหลัก</li>
            <li class="nav-divider d-none my-2 border-top border-white border-opacity-10 w-100"></li>
            
            <li>
                <a href="<?= $path_prefix ?>admin/users_manage.php" class="nav-link text-white d-flex align-items-center <?= ($current_page == 'users_manage.php') ? 'active' : '' ?>" title="จัดการผู้ใช้">
                    <div class="icon-wrapper text-center" style="width: 24px;"><i class="fas fa-users text-info"></i></div>
                    <span class="ms-3 sidebar-text text-nowrap">จัดการผู้ใช้</span>
                </a>
            </li>
            <li>
                <a href="<?= $path_prefix ?>admin/products_manage.php" class="nav-link text-white d-flex align-items-center <?= ($current_page == 'products_manage.php') ? 'active' : '' ?>" title="จัดการสินค้า">
                    <div class="icon-wrapper text-center" style="width: 24px;"><i class="fas fa-box text-success"></i></div>
                    <span class="ms-3 sidebar-text text-nowrap">จัดการสินค้า</span>
                </a>
            </li>
            <li>
                <a href="<?= $path_prefix ?>admin/orders_manage.php" class="nav-link text-white d-flex align-items-center <?= ($current_page == 'orders_manage.php') ? 'active' : '' ?>" title="รายการคำสั่งซื้อ">
                    <div class="icon-wrapper text-center" style="width: 24px;"><i class="fas fa-clipboard-list text-primary"></i></div>
                    <span class="ms-3 sidebar-text text-nowrap">รายการคำสั่งซื้อ</span>
                </a>
            </li>

            <li class="nav-header text-uppercase small text-white-50 fw-bold mt-3 mb-2 px-3 sidebar-text text-nowrap">ตั้งค่า</li>
            <li class="nav-divider d-none my-2 border-top border-white border-opacity-10 w-100"></li>

            <li>
                <a href="<?= $path_prefix ?>admin/central_ingredients.php" class="nav-link text-white d-flex align-items-center <?= ($current_page == 'central_ingredients.php') ? 'active' : '' ?>" title="ราคากลาง">
                    <div class="icon-wrapper text-center" style="width: 24px;"><i class="fas fa-layer-group"></i></div>
                    <span class="ms-3 sidebar-text text-nowrap">วัตถุดิบราคากลาง</span>
                </a>
            </li>
            <li>
                <a href="<?= $path_prefix ?>admin/settings.php" class="nav-link text-white d-flex align-items-center <?= ($current_page == 'settings.php') ? 'active' : '' ?>" title="ตั้งค่าเว็บไซต์">
                    <div class="icon-wrapper text-center" style="width: 24px;"><i class="fas fa-cogs text-secondary"></i></div>
                    <span class="ms-3 sidebar-text text-nowrap">ตั้งค่าเว็บไซต์</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="mt-auto p-3 border-top border-white border-opacity-10">
        <div class="d-flex align-items-center mb-3 overflow-hidden">
            <img src="<?= !empty($_SESSION['profile_image']) ? $_SESSION['profile_image'] : 'https://placehold.co/50' ?>" class="rounded-circle border border-2 border-white flex-shrink-0" width="40" height="40">
            <div class="ms-2 overflow-hidden sidebar-text text-nowrap">
                <div class="fw-bold text-truncate text-white"><?= $_SESSION['username'] ?? 'Admin' ?></div>
                <div class="small text-success">● ออนไลน์</div>
            </div>
        </div>
        <a href="<?= $path_prefix ?>logout.php" class="btn btn-danger w-100 btn-sm rounded-pill d-flex align-items-center justify-content-center" title="ออกจากระบบ">
            <i class="fas fa-sign-out-alt"></i>
            <span class="ms-2 sidebar-text text-nowrap">ออกจากระบบ</span>
        </a>
    </div>
</div>

<style>
    :root {
        --sidebar-width: 260px;
    }

    /* Transition */
    .transition-all { transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); }
    .transition-transform { transition: transform 0.3s ease; }

    /* Scrollbar */
    .scrollbar-thin::-webkit-scrollbar { width: 4px; }
    .scrollbar-thin::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 4px; }

    /* Nav Link Style */
    .nav-link {
        color: rgba(255,255,255,0.8) !important;
        transition: all 0.2s;
        border-radius: 8px;
    }
    .nav-link:hover {
        background-color: rgba(255, 255, 255, 0.1);
        color: #fff !important;
        transform: translateX(3px);
    }
    /* Active State: ทำให้เด่นชัดขึ้น */
    .nav-link.active {
        background-color: rgba(255, 255, 255, 0.15) !important;
        color: <?= $accent_color ?> !important; /* ใช้สี Accent จาก Config */
        font-weight: bold;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    /* Desktop Logic */
    @media (min-width: 992px) {
        body {
            padding-left: var(--sidebar-width);
            transition: padding-left 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        
        .offcanvas-lg {
            transform: none !important;
            visibility: visible !important;
        }

        /* Collapsed State */
        body.sidebar-collapsed .sidebar-text {
            opacity: 0;
            width: 0;
            display: none !important;
        }
        
        body.sidebar-collapsed .nav-divider {
            display: block !important;
        }

        body.sidebar-collapsed .nav-link {
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
        }
        
        body.sidebar-collapsed .nav-link:hover {
            transform: none; /* ห้ามขยับตอนย่อ */
        }

        body.sidebar-collapsed #toggleIcon {
            transform: rotate(180deg);
        }
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const toggleBtn = document.getElementById('sidebarToggle');
        const body = document.body;
        const html = document.documentElement;

        // ฟังก์ชันอัปเดตสถานะ
        function updateSidebar() {
            if (body.classList.contains('sidebar-collapsed')) {
                html.style.setProperty('--sidebar-width', '80px');
                localStorage.setItem('sidebarState', 'collapsed');
            } else {
                html.style.setProperty('--sidebar-width', '260px');
                localStorage.setItem('sidebarState', 'expanded');
            }
        }

        toggleBtn.addEventListener('click', function() {
            body.classList.toggle('sidebar-collapsed');
            updateSidebar();
        });
    });
</script>