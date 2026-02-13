<?php
session_start();
require_once '../config.php';
require_once '../function.php';

// 1. เช็คสิทธิ์ร้านค้า
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'shop') {
    header("Location: ../login.php");
    exit();
}

$shop_id = $_SESSION['user_id'];

// --- 🛠️ ส่วน Filter & Search Logic ---
$search_q = $_GET['q'] ?? '';
$filter_cat = $_GET['cat'] ?? 'all';
$filter_status = $_GET['status'] ?? 'all';
$sort_price = $_GET['sort'] ?? '';

// 2. สร้าง SQL แบบ Dynamic
$sql = "SELECT * FROM products WHERE shop_id = ?";
$params = [$shop_id]; // เริ่มต้นด้วย shop_id

// กรองคำค้นหา
if (!empty($search_q)) {
    $sql .= " AND name LIKE ?";
    $params[] = "%$search_q%";
}

// กรองหมวดหมู่
if ($filter_cat !== 'all') {
    $sql .= " AND category = ?";
    $params[] = $filter_cat;
}

// กรองสถานะ
if ($filter_status !== 'all') {
    $sql .= " AND status = ?";
    $params[] = $filter_status;
}

// เรียงลำดับ (ไม่ต้อง bind param เพราะเป็นค่าคงที่ที่เรากำหนดเองในโค้ด)
if ($sort_price == 'asc') {
    $sql .= " ORDER BY price ASC";
} elseif ($sort_price == 'desc') {
    $sql .= " ORDER BY price DESC";
} else {
    $sql .= " ORDER BY created_at DESC";
}

// 3. เรียกใช้ฟังก์ชัน select() ง่ายๆ บรรทัดเดียวจบ! ✅
$products = select($sql, $params);

$theme = $config['theme'];
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-purple mb-0"><i class="fas fa-boxes me-2"></i>จัดการสินค้า</h3>
            <p class="text-muted small mb-0">รายการสินค้าทั้งหมดในร้านของคุณ</p>
        </div>
        <a href="product_add.php" class="btn btn-nia shadow-sm fw-bold rounded-pill">
            <i class="fas fa-plus me-2"></i> เพิ่มสินค้าใหม่
        </a>
    </div>

    <div class="card border-0 shadow-sm mb-4 rounded-4 bg-white">
        <div class="card-body p-3">
            <form action="" method="GET" class="row g-2 align-items-center">
                
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="q" class="form-control bg-light border-0" placeholder="ค้นหาชื่อสินค้า..." value="<?= htmlspecialchars($search_q) ?>">
                    </div>
                </div>

                <div class="col-md-2">
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
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="sort" class="form-select bg-light border-0" onchange="this.form.submit()">
                        <option value="">เรียงตามล่าสุด</option>
                        <option value="asc" <?= $sort_price=='asc'?'selected':'' ?>>ราคา ต่ำ > สูง</option>
                        <option value="desc" <?= $sort_price=='desc'?'selected':'' ?>>ราคา สูง > ต่ำ</option>
                    </select>
                </div>

                <div class="col-md-2 d-grid">
                    <a href="menu_manage.php" class="btn btn-outline-secondary border-0"><i class="fas fa-undo me-1"></i> ล้างค่า</a>
                </div>

            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3">รูปภาพ</th>
                            <th>ชื่อสินค้า</th>
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
                                        <?php 
                                            $img = $p['image'];
                                            if(!filter_var($img, FILTER_VALIDATE_URL)) $img = '../uploads/kanom/'.$img;
                                        ?>
                                        <img src="<?= $img ?>" class="rounded shadow-sm" width="60" height="60" style="object-fit: cover;" onerror="this.src='https://placehold.co/60x60?text=No+Image'">
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($p['name']) ?></div>
                                        <small class="text-muted text-truncate d-inline-block" style="max-width: 200px;">
                                            <?= htmlspecialchars($p['description']) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php 
                                            $badge_color = 'bg-secondary';
                                            if($p['category']=='dessert') $badge_color = 'bg-warning text-dark';
                                            elseif($p['category']=='material') $badge_color = 'bg-info text-dark';
                                            elseif($p['category']=='souvenir') $badge_color = 'bg-primary';
                                        ?>
                                        <span class="badge <?= $badge_color ?> bg-opacity-25 border border-0 fw-normal px-3 py-2 rounded-pill">
                                            <?= $category_map[$p['category']]['name'] ?? $p['category']; ?>
                                        </span>
                                    </td>
                                    <td class="fw-bold text-purple">฿<?= number_format($p['price'], 2) ?></td>
                                    <td>
                                        <?php if($p['status'] == 'active'): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success px-2 py-1"><i class="fas fa-check-circle me-1"></i>วางขาย</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1"><i class="fas fa-eye-slash me-1"></i>ซ่อน</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="product_edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-light text-warning shadow-sm me-1" title="แก้ไข">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="../process/product_process.php?action=delete&id=<?= $p['id'] ?>" 
                                           class="btn btn-sm btn-light text-danger shadow-sm" 
                                           title="ลบ"
                                           onclick="return confirm('คุณต้องการลบสินค้านี้จริงหรือไม่?');">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-box-open fa-3x mb-3 opacity-25"></i><br>
                                    ไม่พบสินค้าตามเงื่อนไขที่เลือก <br>
                                    <a href="menu_manage.php" class="btn btn-sm btn-outline-secondary mt-2">ล้างตัวกรอง</a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>