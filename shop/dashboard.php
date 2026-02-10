<?php
session_start();

// 1. โหลด Config และ Function
require_once '../config.php';
require_once '../function.php';

// 2. ตรวจสอบสิทธิ์ (Security Check)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'shop') {
    header("Location: ../login.php");
    exit();
}

// ดึงข้อมูล User จาก Session
$shop_id = $_SESSION['user_id'];
$shop_name = $_SESSION['user_name'] ?? 'ร้านค้าของฉัน';
$theme = $config['theme'];

// ดึงข้อมูลร้านค้าเพิ่มเติม (เพื่อเอารูปโปรไฟล์ล่าสุด)
$shop_info = selectOne("SELECT profile_image FROM users WHERE id = ?", [$shop_id]);
$_SESSION['profile_image'] = $shop_info['profile_image'] ?? '';

// 3. ดึงข้อมูลสถิติจาก Database (Real-time Query)
// 3.1 ยอดขายรวม (เฉพาะที่สถานะ completed)
$sql_sales = "SELECT SUM(total_amount) as total FROM orders WHERE shop_id = ? AND status = 'completed'";
$total_sales = selectOne($sql_sales, [$shop_id])['total'] ?? 0;

// 3.2 ออเดอร์ที่รอตรวจสอบ (pending)
$sql_pending = "SELECT COUNT(*) as count FROM orders WHERE shop_id = ? AND status = 'pending'";
$count_pending = selectOne($sql_pending, [$shop_id])['count'] ?? 0;

// 3.3 จำนวนสินค้าในร้าน
$sql_products = "SELECT COUNT(*) as count FROM products WHERE shop_id = ?";
$count_products = selectOne($sql_products, [$shop_id])['count'] ?? 0;

// 3.4 ดึงรายการคำสั่งซื้อล่าสุด 5 รายการ
$sql_orders = "SELECT * FROM orders WHERE shop_id = ? ORDER BY created_at DESC LIMIT 5";
$recent_orders = select($sql_orders, [$shop_id]);


// 4. โหลด Layout
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="fw-bold text-purple mb-0">สวัสดี, <?= htmlspecialchars($shop_name) ?> <i class="fas fa-certificate text-warning ms-2" title="Verified Shop"></i></h2>
            <p class="text-muted small">ยินดีต้อนรับสู่ระบบจัดการร้านค้าอัจฉริยะ Smart Gastronomy</p>
        </div>
        <div>
            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill shadow-sm">
                <i class="far fa-calendar-alt me-2 text-purple"></i> <?= date('d M Y') ?>
            </span>
        </div>
    </div>

    <div class="row g-4 mb-5">

        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 16px; background: linear-gradient(135deg, <?= $theme['colors']['primary'] ?> 0%, #c4006b 100%); color: white;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="mb-1 opacity-75 small">ยอดขายรวม</p>
                            <h3 class="fw-bold mb-0">฿<?= number_format($total_sales, 0) ?></h3>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                            <i class="fas fa-wallet fa-lg"></i>
                        </div>
                    </div>
                    <div class="mt-3 small opacity-75">
                        <i class="fas fa-chart-line me-1"></i> รายได้ทั้งหมดที่ทำสำเร็จ
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 bg-white" style="border-radius: 16px; border-left: 5px solid <?= $theme['colors']['accent'] ?> !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="mb-1 text-muted small fw-bold">ออเดอร์รอส่ง</p>
                            <h3 class="fw-bold text-dark mb-0"><?= number_format($count_pending) ?></h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                            <i class="fas fa-box-open fa-lg"></i>
                        </div>
                    </div>
                    <a href="order_list.php?status=pending" class="btn btn-sm btn-light text-warning mt-3 w-100 fw-bold">
                        จัดการออเดอร์ <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 bg-white" style="border-radius: 16px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="mb-1 text-muted small fw-bold">สินค้าทั้งหมด</p>
                            <h3 class="fw-bold text-purple mb-0"><?= number_format($count_products) ?></h3>
                        </div>
                        <div class="bg-purple bg-opacity-10 text-purple rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                            <i class="fas fa-cubes fa-lg"></i>
                        </div>
                    </div>
                    <a href="menu_manage.php" class="btn btn-sm btn-light text-primary mt-3 w-100 fw-bold">
                        จัดการสินค้า <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 bg-white" style="border-radius: 16px;">
                <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                    <div class="mb-2 text-success">
                        <i class="fas fa-calculator fa-2x"></i>
                    </div>
                    <h6 class="fw-bold text-dark">คำนวณต้นทุน</h6>
                    <p class="text-muted small mb-2" style="font-size: 0.8rem;">Smart Recipe Calculator</p>
                    <a href="calculator.php" class="btn btn-sm btn-outline-success rounded-pill px-3">ใช้งาน</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 16px;">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-purple mb-0"><i class="fas fa-list-alt me-2"></i>รายการสั่งซื้อล่าสุด</h5>
                    <a href="order_list.php" class="text-muted small text-decoration-none">ดูทั้งหมด</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">เลขที่คำสั่งซื้อ</th>
                                    <th>วันที่</th>
                                    <th>ยอดรวม</th>
                                    <th>สถานะ</th>
                                    <th>จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($recent_orders) > 0): ?>
                                    <?php foreach ($recent_orders as $order): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold text-primary">#<?= $order['order_no'] ?></td>
                                            <td class="small text-muted"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                                            <td class="fw-bold">฿<?= number_format($order['total_amount'], 2) ?></td>
                                            <td>
                                                <?php
                                                $status_color = 'secondary';
                                                $status_text = $order['status'];

                                                if ($order['status'] == 'pending') {
                                                    $status_color = 'warning text-dark';
                                                    $status_text = 'รอตรวจสอบ';
                                                } elseif ($order['status'] == 'paid') {
                                                    $status_color = 'info text-dark';
                                                    $status_text = 'ชำระแล้ว';
                                                } elseif ($order['status'] == 'shipped') {
                                                    $status_color = 'primary';
                                                    $status_text = 'ส่งแล้ว';
                                                } elseif ($order['status'] == 'completed') {
                                                    $status_color = 'success';
                                                    $status_text = 'สำเร็จ';
                                                } elseif ($order['status'] == 'cancelled') {
                                                    $status_color = 'danger';
                                                    $status_text = 'ยกเลิก';
                                                }
                                                ?>
                                                <span class="badge bg-<?= $status_color ?> bg-opacity-25 rounded-pill">
                                                    <?= $status_text ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="order_detail.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-secondary rounded-circle shadow-sm" title="ดูรายละเอียด">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i><br>
                                            ยังไม่มีรายการสั่งซื้อเข้ามา
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                <div class="card-body text-center py-4">
                    <?php
                    // Logic จัดการรูปภาพ + Error Handling
                    $profile_img = $shop_info['profile_image'] ?? '';
                    if (!filter_var($profile_img, FILTER_VALIDATE_URL)) {
                        // ถ้าไม่ใช่ URL ภายนอก ให้เติม path
                        $profile_img = (!empty($profile_img)) ? '../uploads/profiles/' . $profile_img : '';
                    }
                    ?>
                    <img src="<?= $profile_img ?>"
                        class="rounded-circle shadow-sm mb-3 border border-3 border-white"
                        width="100" height="100"
                        style="object-fit: cover;"
                        onerror="this.onerror=null; this.src='https://placehold.co/150x150?text=Shop';">

                    <h5 class="fw-bold text-purple"><?= htmlspecialchars($shop_name) ?></h5>
                    <p class="text-muted small">ID: <?= str_pad($shop_id, 4, '0', STR_PAD_LEFT) ?></p>

                    <div class="d-grid gap-2">
                        <a href="../profile.php" class="btn btn-outline-nia btn-sm">แก้ไขข้อมูลร้าน</a>
                        <a href="../shop_detail.php?id=<?= $shop_id ?>" target="_blank" class="btn btn-sm btn-light text-purple">ดูหน้าร้าน (Public View)</a>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm text-white" style="border-radius: 16px; background-color: <?= $theme['colors']['secondary'] ?>;">
                <div class="card-body">
                    <h6 class="fw-bold mb-3"><i class="fas fa-bullhorn me-2"></i>ประกาศจากโครงการ</h6>
                    <ul class="list-unstyled small mb-0 opacity-75">
                        <li class="mb-2 border-bottom border-light pb-2" style="--bs-border-opacity: .2;">
                            <i class="fas fa-star text-warning me-2"></i> เปิดลงทะเบียนอบรม Digital Marketing รุ่นที่ 2
                        </li>
                        <li>
                            <i class="fas fa-info-circle text-info me-2"></i> ปิดปรับปรุงระบบวันที่ 28 ก.พ. เวลา 02:00-04:00 น.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

</div>

<?php include '../includes/footer.php'; ?>