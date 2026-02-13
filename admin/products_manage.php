<?php
session_start();
require_once '../config.php';
require_once '../function.php';

// 1. ความปลอดภัย: ต้องเป็น Admin เท่านั้น
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// 2. รับค่า Filter จาก URL
$search_q = $_GET['q'] ?? '';
$filter_cat = $_GET['cat'] ?? 'all';
$filter_status = $_GET['status'] ?? 'all';

// 3. สร้าง SQL Query แบบ Dynamic
$sql = "SELECT p.*, s.shop_name, s.fullname 
        FROM products p 
        JOIN users s ON p.shop_id = s.id 
        WHERE (p.name LIKE ? OR s.shop_name LIKE ?)";

$params = ["%$search_q%", "%$search_q%"];

// กรองหมวดหมู่
if ($filter_cat !== 'all') {
    $sql .= " AND p.category = ?";
    $params[] = $filter_cat;
}

// กรองสถานะ
if ($filter_status !== 'all') {
    $sql .= " AND p.status = ?";
    $params[] = $filter_status;
}

$sql .= " ORDER BY p.created_at DESC";

$products = select($sql, $params);

// Map หมวดหมู่สำหรับแสดงผล
$category_map = [
    'dessert' => 'ขนมหวาน',
    'material' => 'วัตถุดิบ',
    'souvenir' => 'ของฝาก'
];

$theme = $config['theme'];
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-purple mb-0"><i class="fas fa-boxes me-2"></i>จัดการสินค้าทั้งหมด</h3>
            <p class="text-muted small mb-0">รายการสินค้าจากทุกร้านค้าในระบบ</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4 rounded-4 bg-white">
        <div class="card-body p-3">
            <form action="" method="GET" class="row g-2 align-items-center">
                
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="q" class="form-control bg-light border-0" 
                               placeholder="ชื่อสินค้า / ชื่อร้านค้า..." value="<?= htmlspecialchars($search_q) ?>">
                    </div>
                </div>

                <div class="col-md-3">
                    <select name="cat" class="form-select bg-light border-0" onchange="this.form.submit()">
                        <option value="all">ทุกหมวดหมู่</option>
                        <option value="dessert" <?= $filter_cat=='dessert'?'selected':'' ?>>ขนมหวาน</option>
                        <option value="material" <?= $filter_cat=='material'?'selected':'' ?>>วัตถุดิบ</option>
                        <option value="souvenir" <?= $filter_cat=='souvenir'?'selected':'' ?>>ของฝาก</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="status" class="form-select bg-light border-0" onchange="this.form.submit()">
                        <option value="all">ทุกสถานะ</option>
                        <option value="active" <?= $filter_status=='active'?'selected':'' ?>>วางขาย</option>
                        <option value="hidden" <?= $filter_status=='hidden'?'selected':'' ?>>ซ่อน</option>
                        <option value="out_of_stock" <?= $filter_status=='out_of_stock'?'selected':'' ?>>สินค้าหมด</option>
                    </select>
                </div>

                <div class="col-md-2 d-grid">
                    <a href="products_manage.php" class="btn btn-outline-secondary border-0"><i class="fas fa-undo me-1"></i> ล้างค่า</a>
                </div>

            </form>
        </div>
    </div>

    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
        <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4 d-flex align-items-center">
            <i class="fas fa-check-circle me-2 fs-4"></i>
            <div>ลบสินค้าเรียบร้อยแล้ว</div>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0 table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3">สินค้า</th>
                            <th>ร้านค้า (Shop)</th>
                            <th>หมวดหมู่</th>
                            <th>ราคา</th>
                            <th>สถานะ</th>
                            <th class="text-end pe-4">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($products) > 0): ?>
                            <?php foreach ($products as $p): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <?php
                                                $img = $p['image'];
                                                if (!filter_var($img, FILTER_VALIDATE_URL)) {
                                                    $img = '../uploads/kanom/' . $img;
                                                }
                                            ?>
                                            <img src="<?= $img ?>" class="rounded shadow-sm me-3 border" width="50" height="50" style="object-fit: cover;" onerror="this.src='https://placehold.co/50x50?text=No+Image'">
                                            <div>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($p['name']) ?></div>
                                                <small class="text-muted">Stock: <?= $p['stock_qty'] ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-purple"><i class="fas fa-store me-1"></i> <?= htmlspecialchars($p['shop_name']) ?></div>
                                        <small class="text-muted" style="font-size: 0.75rem;">โดย: <?= htmlspecialchars($p['fullname']) ?></small>
                                    </td>
                                    <td>
                                        <?php 
                                            $badge_color = 'bg-secondary';
                                            if($p['category']=='dessert') $badge_color = 'bg-warning text-dark';
                                            elseif($p['category']=='material') $badge_color = 'bg-info text-dark';
                                            elseif($p['category']=='souvenir') $badge_color = 'bg-primary';
                                        ?>
                                        <span class="badge <?= $badge_color ?> bg-opacity-25 border border-0 fw-normal px-2 py-1 rounded-pill">
                                            <?= $category_map[$p['category']] ?? $p['category']; ?>
                                        </span>
                                    </td>
                                    <td class="fw-bold text-dark">฿<?= number_format($p['price']) ?></td>
                                    <td>
                                        <?php if ($p['status'] == 'active'): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2">วางขาย</span>
                                        <?php elseif ($p['status'] == 'out_of_stock'): ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2">หมด</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2">ซ่อน</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="../process/admin_process.php?action=delete_product&id=<?= $p['id'] ?>"
                                           class="btn btn-sm btn-light text-danger shadow-sm hover-scale"
                                           onclick="return confirm('⚠️ คำเตือน!\n\nคุณกำลังจะลบสินค้า: <?= htmlspecialchars($p['name']) ?>\nการกระทำนี้ไม่สามารถกู้คืนได้ ยืนยันหรือไม่?');"
                                           title="ลบสินค้าถาวร">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-box-open fa-3x mb-3 opacity-25"></i><br>
                                    ไม่พบข้อมูลสินค้าตามเงื่อนไขที่เลือก
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-scale:hover { transform: scale(1.1); transition: 0.2s; }
</style>

<?php include '../includes/footer.php'; ?>