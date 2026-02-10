<?php
session_start();
require_once '../config.php';
require_once '../function.php';

// 1. ความปลอดภัย: ต้องเป็น Admin เท่านั้น
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// 2. รับค่าค้นหา (ถ้ามี)
$search = $_GET['q'] ?? '';

// 3. Query ข้อมูล (Join ตาราง Products กับ Users เพื่อเอาชื่อร้าน)
$sql = "SELECT p.*, s.shop_name, s.fullname 
        FROM products p 
        JOIN users s ON p.shop_id = s.id 
        WHERE p.name LIKE ? OR s.shop_name LIKE ? 
        ORDER BY p.created_at DESC";

$products = select($sql, ["%$search%", "%$search%"]);

$theme = $config['theme'];
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold text-purple mb-0"><i class="fas fa-boxes me-2"></i>จัดการสินค้าทั้งหมด</h3>
            <p class="text-muted small mb-0">System Wide Product Management</p>
        </div>

        <form class="d-flex" method="GET" action="products_manage.php">
            <div class="input-group shadow-sm">
                <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                <input class="form-control border-start-0" type="search" name="q" placeholder="ชื่อสินค้า / ชื่อร้าน..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-purple" type="submit">ค้นหา</button>
            </div>
        </form>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">รูปสินค้า</th>
                        <th>ชื่อสินค้า</th>
                        <th>ร้านค้า (Shop)</th>
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
                                    // เช็คว่าเป็นลิงก์ภายนอก หรือไฟล์ในเครื่อง
                                    if (!filter_var($img, FILTER_VALIDATE_URL)) {
                                        $img = '../uploads/kanom/' . $img;
                                    }
                                    ?>
                                    <img src="<?= $img ?>"
                                        class="rounded shadow-sm"
                                        width="50" height="50"
                                        style="object-fit: cover;"
                                        onerror="this.onerror=null; this.src='https://placehold.co/50x50?text=No+Image';">
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($p['name']) ?></div>
                                    <small class="text-muted"><?= $p['category'] ?></small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-purple text-white rounded-circle d-flex justify-content-center align-items-center me-2" style="width: 30px; height: 30px; font-size: 12px;">
                                            <i class="fas fa-store"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-purple"><?= htmlspecialchars($p['shop_name']) ?></div>
                                            <small class="text-muted" style="font-size: 0.75rem;">เจ้าของ: <?= htmlspecialchars($p['fullname']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td class="fw-bold">฿<?= number_format($p['price']) ?></td>
                                <td>
                                    <?php if ($p['status'] == 'active'): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">วางขาย</span>
                                    <?php elseif ($p['status'] == 'out_of_stock'): ?>
                                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">หมด</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3">ซ่อน</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="../process/admin_process.php?action=delete_product&id=<?= $p['id'] ?>"
                                        class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('⚠️ คำเตือน!\n\nคุณกำลังจะลบสินค้า: <?= htmlspecialchars($p['name']) ?>\nการกระทำนี้ไม่สามารถกู้คืนได้ ยืนยันหรือไม่?');">
                                        <i class="fas fa-trash-alt me-1"></i> ลบสินค้า
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-box-open fa-3x mb-3 opacity-25"></i><br>
                                ไม่พบข้อมูลสินค้าในระบบ
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>