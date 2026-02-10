<?php
session_start();
require_once '../config.php';
require_once '../function.php';

// 1. ตรวจสอบสิทธิ์ (Shop Only)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'shop') {
    header("Location: ../login.php");
    exit();
}

$shop_id = $_SESSION['user_id'];
$product_id = $_GET['id'] ?? 0;

// 2. ดึงข้อมูลสินค้าหลัก
$product = selectOne("SELECT * FROM products WHERE id = ? AND shop_id = ?", [$product_id, $shop_id]);

// ถ้าไม่เจอสินค้า หรือไม่ใช่เจ้าของ
if (!$product) {
    echo "<script>alert('ไม่พบสินค้า หรือคุณไม่มีสิทธิ์'); window.location='menu_manage.php';</script>";
    exit();
}

// 3. ดึงข้อมูลส่วนผสม (Ingredients)
$ingredients = select("SELECT * FROM product_ingredients WHERE product_id = ?", [$product_id]);

// 4. ดึงข้อมูลแพ็คเกจ (Packages)
$packages = select("SELECT * FROM product_packages WHERE product_id = ?", [$product_id]);

$theme = $config['theme'];
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <div class="mb-4">
                <a href="menu_manage.php" class="text-decoration-none text-muted small fw-bold">
                    <i class="fas fa-arrow-left me-1"></i> ย้อนกลับ
                </a>
            </div>

            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-warning text-dark py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-edit me-2"></i>แก้ไขข้อมูลสินค้า</h5>
                </div>
                <div class="card-body p-4">

                    <form action="../process/product_process.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="edit_product">
                        <input type="hidden" name="id" value="<?= $product['id'] ?>">

                        <h6 class="fw-bold text-warning mb-3 border-bottom pb-2">1. ข้อมูลพื้นฐาน</h6>
                        <div class="row g-4 mb-4">
                            <div class="col-md-4 text-center">
                                <label class="form-label fw-bold d-block">รูปสินค้า</label>
                                <label for="product_image" class="d-inline-block position-relative cursor-pointer">
                                    <?php 
                                        $img = $product['image'];
                                        if(!filter_var($img, FILTER_VALIDATE_URL)) $img = '../uploads/kanom/'.$img;
                                    ?>
                                    <img id="imgPreview" src="<?= $img ?>" 
                                         class="rounded-4 shadow-sm border border-warning" width="100%" 
                                         style="max-width:200px; aspect-ratio:1/1; object-fit:cover;"
                                         onerror="this.src='https://placehold.co/300x300?text=No+Image'">
                                    
                                    <div class="position-absolute bottom-0 end-0 bg-white rounded-circle p-2 shadow-sm border">
                                        <i class="fas fa-camera text-warning"></i>
                                    </div>
                                </label>
                                <input type="file" name="product_image" id="product_image" class="d-none" accept="image/*" onchange="previewImage(this)">
                                <div class="small text-muted mt-2">คลิกรูปเพื่อเปลี่ยน (ถ้าไม่เปลี่ยนให้เว้นว่าง)</div>
                            </div>
                            
                            <div class="col-md-8">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-bold">ชื่อสินค้า</label>
                                        <input type="text" name="name" class="form-control bg-light border-0" required value="<?= htmlspecialchars($product['name']) ?>">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold">รายละเอียด</label>
                                        <textarea name="description" class="form-control bg-light border-0" rows="3"><?= htmlspecialchars($product['description']) ?></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">หมวดหมู่</label>
                                        <select name="category" class="form-select bg-light border-0" required>
                                            <option value="dessert" <?= $product['category']=='dessert'?'selected':'' ?>>ขนมหวาน</option>
                                            <option value="material" <?= $product['category']=='material'?'selected':'' ?>>วัตถุดิบ</option>
                                            <option value="souvenir" <?= $product['category']=='souvenir'?'selected':'' ?>>ของฝาก</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">ราคาขายปลีก</label>
                                        <div class="input-group">
                                            <input type="number" name="price" class="form-control bg-light border-0" required value="<?= $product['price'] ?>">
                                            <span class="input-group-text bg-light border-0">บาท</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">จำนวนสต็อก</label>
                                        <input type="number" name="stock_qty" class="form-control bg-light border-0" required value="<?= $product['stock_qty'] ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">สถานะ</label>
                                        <select name="status" class="form-select bg-light border-0">
                                            <option value="active" <?= $product['status']=='active'?'selected':'' ?>>วางขาย</option>
                                            <option value="hidden" <?= $product['status']=='hidden'?'selected':'' ?>>ซ่อน</option>
                                            <option value="out_of_stock" <?= $product['status']=='out_of_stock'?'selected':'' ?>>ของหมด</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        
                        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                            <h6 class="fw-bold text-warning mb-0 text-dark">2. ส่วนผสม (Ingredients)</h6>
                            <button type="button" class="btn btn-sm btn-outline-warning text-dark rounded-pill" onclick="addIngredient()">
                                <i class="fas fa-plus"></i> เพิ่มส่วนผสม
                            </button>
                        </div>
                        <div id="ingredient_container" class="mb-4">
                            <?php if(count($ingredients) > 0): ?>
                                <?php foreach($ingredients as $ing): ?>
                                <div class="row g-2 mb-2 ingredient-row">
                                    <div class="col-md-5">
                                        <input type="text" name="ing_name[]" class="form-control bg-light border-0" value="<?= htmlspecialchars($ing['ingredient_name']) ?>" placeholder="ชื่อส่วนผสม">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" name="ing_amount[]" class="form-control bg-light border-0" value="<?= $ing['amount'] ?>" placeholder="ปริมาณ" step="0.01">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text" name="ing_unit[]" class="form-control bg-light border-0" value="<?= htmlspecialchars($ing['unit']) ?>" placeholder="หน่วย">
                                    </div>
                                    <div class="col-md-1 text-center">
                                        <button type="button" class="btn btn-link text-danger p-0" onclick="removeRow(this)"><i class="fas fa-trash-alt"></i></button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-muted small fst-italic py-2" id="no_ing_msg">ยังไม่มีข้อมูลส่วนผสม</div>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                            <h6 class="fw-bold text-warning mb-0 text-dark">3. ตัวเลือกแพ็คเกจ (Packages)</h6>
                            <button type="button" class="btn btn-sm btn-outline-warning text-dark rounded-pill" onclick="addPackage()">
                                <i class="fas fa-plus"></i> เพิ่มแพ็คเกจ
                            </button>
                        </div>
                        <div id="package_container" class="mb-4">
                            <?php if(count($packages) > 0): ?>
                                <?php foreach($packages as $pkg): ?>
                                <div class="row g-2 mb-2 package-row">
                                    <div class="col-md-5">
                                        <input type="text" name="pack_name[]" class="form-control bg-light border-0" value="<?= htmlspecialchars($pkg['package_name']) ?>" placeholder="ชื่อแพ็คเกจ">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" name="pack_amount[]" class="form-control bg-light border-0" value="<?= $pkg['qty_per_pack'] ?>" placeholder="จำนวนชิ้น">
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <input type="number" name="pack_price[]" class="form-control bg-light border-0" value="<?= $pkg['price'] ?>" placeholder="ราคา">
                                            <span class="input-group-text bg-light border-0 small">฿</span>
                                        </div>
                                    </div>
                                    <div class="col-md-1 text-center">
                                        <button type="button" class="btn btn-link text-danger p-0" onclick="removeRow(this)"><i class="fas fa-trash-alt"></i></button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-muted small fst-italic py-2" id="no_package_msg">ยังไม่มีแพ็คเกจเสริม</div>
                            <?php endif; ?>
                        </div>

                        <div class="d-grid mt-5">
                            <button type="submit" class="btn btn-warning btn-lg fw-bold shadow-sm text-dark">
                                <i class="fas fa-save me-2"></i> บันทึกการแก้ไข
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// ฟังก์ชัน Preview รูป
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) { document.getElementById('imgPreview').src = e.target.result; }
        reader.readAsDataURL(input.files[0]);
    }
}

// ฟังก์ชันเพิ่มส่วนผสม (เหมือนหน้า Add แต่ซ่อน msg ถ้ามี)
function addIngredient() {
    const msg = document.getElementById('no_ing_msg');
    if(msg) msg.style.display = 'none';

    const container = document.getElementById('ingredient_container');
    const html = `
        <div class="row g-2 mb-2 ingredient-row fade-in">
            <div class="col-md-5">
                <input type="text" name="ing_name[]" class="form-control bg-light border-0" placeholder="ชื่อส่วนผสม" required>
            </div>
            <div class="col-md-3">
                <input type="number" name="ing_amount[]" class="form-control bg-light border-0" placeholder="ปริมาณ" step="0.01" required>
            </div>
            <div class="col-md-3">
                <input type="text" name="ing_unit[]" class="form-control bg-light border-0" placeholder="หน่วย" required>
            </div>
            <div class="col-md-1 text-center">
                <button type="button" class="btn btn-link text-danger p-0" onclick="removeRow(this)"><i class="fas fa-trash-alt"></i></button>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
}

// ฟังก์ชันเพิ่มแพ็คเกจ
function addPackage() {
    const msg = document.getElementById('no_package_msg');
    if(msg) msg.style.display = 'none';

    const container = document.getElementById('package_container');
    const html = `
        <div class="row g-2 mb-2 package-row fade-in">
            <div class="col-md-5">
                <input type="text" name="pack_name[]" class="form-control bg-light border-0" placeholder="ชื่อแพ็คเกจ" required>
            </div>
            <div class="col-md-3">
                <input type="number" name="pack_amount[]" class="form-control bg-light border-0" placeholder="จำนวนชิ้น" required>
            </div>
            <div class="col-md-3">
                <div class="input-group">
                    <input type="number" name="pack_price[]" class="form-control bg-light border-0" placeholder="ราคา" required>
                    <span class="input-group-text bg-light border-0 small">฿</span>
                </div>
            </div>
            <div class="col-md-1 text-center">
                <button type="button" class="btn btn-link text-danger p-0" onclick="removeRow(this)"><i class="fas fa-trash-alt"></i></button>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
}

// ฟังก์ชันลบแถว
function removeRow(btn) {
    if(confirm('ต้องการลบรายการนี้?')) {
        btn.closest('.row').remove();
    }
}
</script>

<style>
    .fade-in { animation: fadeIn 0.3s; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
</style>

<?php include '../includes/footer.php'; ?>