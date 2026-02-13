<?php
session_start();
require_once 'config.php';
require_once 'function.php';

// --- 1. รับค่าจาก URL (GET) ---
$search_q = $_GET['q'] ?? '';
$category_filter = $_GET['category'] ?? 'all';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 9; // จำนวนสินค้าต่อหน้า
$offset = ($page - 1) * $limit;

// --- 2. สร้าง Query สำหรับ Filter ---
$sql_base = "FROM products WHERE status = 'active'";
$params = [];
$types = "";

// กรองคำค้นหา
if (!empty($search_q)) {
    $sql_base .= " AND (name LIKE ? OR description LIKE ?)";
    $params[] = "%$search_q%";
    $params[] = "%$search_q%";
    $types .= "ss";
}

// กรองหมวดหมู่
if ($category_filter !== 'all') {
    $sql_base .= " AND category = ?";
    $params[] = $category_filter;
    $types .= "s";
}

// กรองราคา
if (!empty($min_price)) {
    $sql_base .= " AND price >= ?";
    $params[] = $min_price;
    $types .= "d";
}
if (!empty($max_price)) {
    $sql_base .= " AND price <= ?";
    $params[] = $max_price;
    $types .= "d";
}

// --- 3. คำนวณ Pagination (นับจำนวนสินค้าทั้งหมดก่อน) ---
// เราต้องใช้ Prepared Statement แบบ manual เพราะ function select() ปกติรองรับแค่ query ธรรมดา
// แต่ที่นี่เราต้องนับ rows และ fetch data ด้วยเงื่อนไขเดียวกัน
global $conn;
$count_sql = "SELECT COUNT(*) as total " . $sql_base;
$stmt = $conn->prepare($count_sql);
if(!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total_rows = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);


// --- 4. Query ดึงข้อมูลสินค้าจริง (พร้อม Sorting & Limit) ---
$order_by = "ORDER BY created_at DESC"; // Default: ใหม่สุด
if ($sort == 'price_asc') $order_by = "ORDER BY price ASC";
if ($sort == 'price_desc') $order_by = "ORDER BY price DESC";
if ($sort == 'name_asc') $order_by = "ORDER BY name ASC";

$final_sql = "SELECT * " . $sql_base . " " . $order_by . " LIMIT ?, ?";
// เพิ่ม params สำหรับ Limit
$params[] = $offset;
$params[] = $limit;
$types .= "ii";

$stmt = $conn->prepare($final_sql);
if(!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$products = $result->fetch_all(MYSQLI_ASSOC);


$theme = $config['theme'];
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="bg-light py-4 border-bottom">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted">หน้าแรก</a></li>
                <li class="breadcrumb-item active text-purple fw-bold" aria-current="page">ค้นหาสินค้า</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-5">
    <div class="row g-4">
        
        <div class="col-lg-3">
            <form action="search.php" method="GET" class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 100px; z-index: 900;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3"><i class="fas fa-filter me-2 text-purple"></i>กรองข้อมูล</h5>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">คำค้นหา</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="q" class="form-control border-start-0" placeholder="ชื่อสินค้า..." value="<?= htmlspecialchars($search_q) ?>">
                        </div>
                    </div>

                    <hr class="border-dashed my-3">

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">หมวดหมู่</label> 
                        <div class="d-grid gap-2">
                            <?php foreach($category_map as $key => $label): ?>
                            <label class="custom-radio-btn">
                                <input type="radio" name="category" value="<?= $key ?>" <?= $category_filter == $key ? 'checked' : '' ?> onchange="this.form.submit()">
                                <span class="content py-2 px-3 rounded-3 d-block border w-100 text-start">
                                    <?= $label['name'] ?>
                                </span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <hr class="border-dashed my-3">

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">ช่วงราคา</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="number" name="min_price" class="form-control form-control-sm" placeholder="ต่ำสุด" value="<?= htmlspecialchars($min_price) ?>">
                            <span>-</span>
                            <input type="number" name="max_price" class="form-control form-control-sm" placeholder="สูงสุด" value="<?= htmlspecialchars($max_price) ?>">
                        </div>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-purple shadow-sm fw-bold">ค้นหา</button>
                        <a href="search.php" class="btn btn-outline-secondary btn-sm">ล้างค่า</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-lg-9">
            
            <div class="d-md-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded-4 shadow-sm border">
                <div class="mb-2 mb-md-0">
                    <span class="text-muted">พบสินค้าทั้งหมด <strong class="text-purple"><?= $total_rows ?></strong> รายการ</span>
                    <?php if(!empty($search_q)): ?>
                        <small class="text-muted ms-2">(คำค้นหา: "<?= htmlspecialchars($search_q) ?>")</small>
                    <?php endif; ?>
                </div>
                <div class="d-flex align-items-center">
                    <label class="me-2 small text-muted text-nowrap">เรียงตาม:</label>
                    <select name="sort" class="form-select form-select-sm border-0 bg-light fw-bold" style="width: 150px;" onchange="updateSort(this.value)">
                        <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>มาใหม่ล่าสุด</option>
                        <option value="price_asc" <?= $sort == 'price_asc' ? 'selected' : '' ?>>ราคา ต่ำ -> สูง</option>
                        <option value="price_desc" <?= $sort == 'price_desc' ? 'selected' : '' ?>>ราคา สูง -> ต่ำ</option>
                        <option value="name_asc" <?= $sort == 'name_asc' ? 'selected' : '' ?>>ชื่อ ก-ฮ</option>
                    </select>
                </div>
            </div>

            <div class="row g-3">
                <?php if (count($products) > 0): ?>
                    <?php foreach ($products as $p): ?>
                    <div class="col-6 col-md-4">
                        <div class="card h-100 border-0 shadow-sm product-card" style="border-radius: 16px; overflow: hidden; transition: 0.3s;">
                            <a href="shop_detail.php?id=<?= $p['shop_id'] ?>" class="text-decoration-none text-dark">
                                <div class="position-relative">
                                    <?php 
                                        $img = $p['image'];
                                        if(!filter_var($img, FILTER_VALIDATE_URL)) $img = 'uploads/kanom/' . $img;
                                    ?>
                                    <img src="<?= $img ?>" class="card-img-top" alt="<?= htmlspecialchars($p['name']) ?>" 
                                         style="height: 200px; object-fit: cover;"
                                         onerror="this.onerror=null; this.src='https://placehold.co/400x300?text=No+Image';">
                                    
                                    <?php if($p['category']=='dessert'): ?>
                                        <span class="position-absolute top-0 end-0 m-2 badge bg-warning text-dark rounded-pill shadow-sm small">ขนมหวาน</span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body p-3">
                                    <h6 class="card-title fw-bold mb-1 text-truncate"><?= htmlspecialchars($p['name']) ?></h6>
                                    <p class="text-muted small mb-2 text-truncate"><?= htmlspecialchars($p['description']) ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-magenta fs-5">฿<?= number_format($p['price']) ?></span>
                                        <button class="btn btn-sm btn-light text-purple rounded-circle shadow-sm" style="width: 32px; height: 32px; padding: 0;">
                                            <i class="fas fa-arrow-right"></i>
                                        </button>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5 bg-white rounded-4 shadow-sm">
                        <i class="fas fa-search fa-4x text-muted opacity-25 mb-3"></i>
                        <h5 class="fw-bold text-muted">ไม่พบสินค้าที่คุณค้นหา</h5>
                        <p class="text-muted mb-4">ลองเปลี่ยนคำค้นหา หรือปรับตัวกรองใหม่</p>
                        <a href="search.php" class="btn btn-purple rounded-pill px-4">ดูสินค้าทั้งหมด</a>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($total_pages > 1): ?>
            <div class="mt-5 d-flex justify-content-center">
                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-purple shadow-sm rounded-pill overflow-hidden">
                        
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>

                    </ul>
                </nav>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
// ฟังก์ชันสำหรับ Dropdown Sort ให้เปลี่ยน URL โดยคงค่า Filter อื่นๆ ไว้
function updateSort(val) {
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('sort', val);
    urlParams.set('page', 1); // รีเซ็ตไปหน้า 1 เมื่อเปลี่ยนการเรียง
    window.location.search = urlParams;
}
</script>

<style>
    /* Radio Button สวยๆ สำหรับหมวดหมู่ */
    .custom-radio-btn input { display: none; }
    .custom-radio-btn .content { 
        cursor: pointer; 
        transition: 0.2s; 
        color: #6c757d;
        border-color: #dee2e6 !important;
    }
    .custom-radio-btn input:checked + .content {
        background-color: var(--nia-purple);
        color: white;
        border-color: var(--nia-purple) !important;
        box-shadow: 0 4px 6px rgba(111, 66, 193, 0.2);
    }
    .custom-radio-btn .content:hover {
        background-color: #f8f9fa;
    }
    .custom-radio-btn input:checked + .content:hover {
        background-color: var(--nia-purple);
    }

    .border-dashed { border-style: dashed !important; border-color: #dee2e6; }
    
    .btn-purple { background-color: var(--nia-purple); color: white; }
    .btn-purple:hover { background-color: #5a32a3; color: white; }

    /* Pagination สีม่วง */
    .pagination-purple .page-link { color: var(--nia-purple); border: none; margin: 0 2px; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; }
    .pagination-purple .page-item.active .page-link { background-color: var(--nia-purple); color: white; }
    .pagination-purple .page-link:hover { background-color: #f0ebfa; }
    
    .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1)!important; }
</style>

<?php include 'includes/footer.php'; ?>