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

// 2. ดึงข้อมูลสินค้าของร้านนี้
$products = select("SELECT * FROM products WHERE shop_id = ? ORDER BY created_at DESC", [$shop_id]);

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
        <a href="product_add.php" class="btn btn-nia shadow-sm fw-bold">
            <i class="fas fa-plus me-2"></i> เพิ่มสินค้าใหม่
        </a>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">รูปภาพ</th>
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
                                        <img src="<?= $img ?>" class="rounded shadow-sm" width="60" height="60" style="object-fit: cover;">
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($p['name']) ?></div>
                                        <small class="text-muted text-truncate d-inline-block" style="max-width: 150px;">
                                            <?= htmlspecialchars($p['description']) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php 
                                            $cats = ['dessert'=>'ขนมหวาน', 'raw_material'=>'วัตถุดิบ', 'snack'=>'ของว่าง', 'drink'=>'เครื่องดื่ม'];
                                            echo $cats[$p['category']] ?? $p['category'];
                                        ?>
                                    </td>
                                    <td class="fw-bold text-magenta">฿<?= number_format($p['price']) ?></td>
                                    <td>
                                        <?php if($p['status'] == 'active'): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success">พร้อมขาย</span>
                                        <?php elseif($p['status'] == 'out_of_stock'): ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger">สินค้าหมด</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary">ไม่แสดง</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="product_edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-warning me-1" title="แก้ไข">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="../process/product_process.php?action=delete&id=<?= $p['id'] ?>" 
                                           class="btn btn-sm btn-outline-danger" 
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
                                    ยังไม่มีสินค้าในร้าน <br>
                                    <a href="product_add.php" class="btn btn-sm btn-nia mt-2">เพิ่มสินค้าชิ้นแรก</a>
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