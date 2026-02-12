<?php
session_start();
require_once '../config.php';
require_once '../function.php';

// 1. ตรวจสอบสิทธิ์
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'shop') {
    header("Location: ../login.php");
    exit();
}

// 🟢 ดึงข้อมูลวัตถุดิบมาตรฐาน (สำหรับคำนวณราคากลาง)
$central_items = select("SELECT * FROM central_ingredients ORDER BY name ASC");

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
                <div class="card-header bg-purple text-white py-3" 
                     style="background: linear-gradient(135deg, <?= $theme['colors']['secondary'] ?> 0%, <?= $theme['colors']['primary'] ?> 100%);">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-plus-circle me-2"></i>เพิ่มสินค้าใหม่</h5>
                </div>
                <div class="card-body p-4">

                    <form action="../process/product_process.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add_product">

                        <h6 class="fw-bold text-purple mb-3 border-bottom pb-2">1. ข้อมูลพื้นฐาน</h6>
                        <div class="row g-4 mb-4">
                            <div class="col-md-4 text-center">
                                <label class="form-label fw-bold d-block">รูปสินค้า</label>
                                <label for="product_image" class="d-inline-block position-relative cursor-pointer hover-scale">
                                    <img id="imgPreview" src="https://placehold.co/300x300?text=Click+to+Upload" 
                                         class="rounded-4 shadow-sm border" width="100%" style="max-width:200px; aspect-ratio:1/1; object-fit:cover;">
                                    <div class="position-absolute bottom-0 end-0 bg-white rounded-circle p-2 shadow-sm border">
                                        <i class="fas fa-camera text-purple"></i>
                                    </div>
                                </label>
                                <input type="file" name="product_image" id="product_image" class="d-none" accept="image/*" onchange="previewImage(this)">
                            </div>
                            
                            <div class="col-md-8">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-bold">ชื่อสินค้า <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control bg-light border-0" required placeholder="เช่น ขนมหม้อแกง, ไข่ไก่เบอร์ 2">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold">รายละเอียด</label>
                                        <textarea name="description" class="form-control bg-light border-0" rows="2" placeholder="คำอธิบายสินค้า..."></textarea>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">หมวดหมู่ <span class="text-danger">*</span></label>
                                        <select name="category" class="form-select bg-light border-0" required>
                                            <option value="dessert">ขนมหวาน</option>
                                            <option value="material">วัตถุดิบ</option>
                                            <option value="souvenir">ของฝาก</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">ชนิดวัตถุดิบ (สำหรับราคากลาง)</label>
                                        <select name="central_id" class="form-select bg-light border-0">
                                            <option value="">-- ไม่ใช่วัตถุดิบมาตรฐาน --</option>
                                            <?php foreach($central_items as $ci): ?>
                                                <option value="<?= $ci['id'] ?>"><?= $ci['name'] ?> (หน่วย: <?= $ci['unit'] ?>)</option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text small" style="font-size: 0.75rem;">
                                            * เลือกเฉพาะเมื่อสินค้านี้เป็นวัตถุดิบ เช่น ไข่, แป้ง
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">ราคาขาย (ต่อชิ้น/หน่วย) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number" name="price" class="form-control bg-light border-0" required placeholder="0.00" step="0.01">
                                            <span class="input-group-text bg-light border-0">บาท</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">จำนวนสต็อก <span class="text-danger">*</span></label>
                                        <input type="number" name="stock_qty" class="form-control bg-light border-0" required value="10">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">สถานะ</label>
                                        <select name="status" class="form-select bg-light border-0">
                                            <option value="active">วางขาย</option>
                                            <option value="hidden">ซ่อน</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                            <h6 class="fw-bold text-purple mb-0">
                                2. ส่วนผสม (Recipes) 
                                <small class="text-muted fw-normal" style="font-size: 0.8rem;">(เฉพาะสินค้าที่เป็นขนม/อาหาร)</small>
                            </h6>
                            <button type="button" class="btn btn-sm btn-outline-purple rounded-pill" onclick="addIngredient()">
                                <i class="fas fa-plus"></i> เพิ่มส่วนผสม
                            </button>
                        </div>
                        <div id="ingredient_container" class="mb-4">
                            </div>

                        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                            <h6 class="fw-bold text-purple mb-0">3. ตัวเลือกแพ็คเกจ (Packages)</h6>
                            <button type="button" class="btn btn-sm btn-outline-purple rounded-pill" onclick="addPackage()">
                                <i class="fas fa-plus"></i> เพิ่มแพ็คเกจ
                            </button>
                        </div>
                        <div id="package_container" class="mb-4">
                            <div class="text-center text-muted small fst-italic py-2 bg-light rounded" id="no_package_msg">
                                ยังไม่มีแพ็คเกจเสริม (ขายตามราคาปลีกด้านบน)
                            </div>
                        </div>

                        <div class="d-grid mt-5">
                            <button type="submit" class="btn btn-nia btn-lg fw-bold shadow-sm rounded-pill">
                                <i class="fas fa-save me-2"></i> บันทึกข้อมูลสินค้า
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) { document.getElementById('imgPreview').src = e.target.result; }
        reader.readAsDataURL(input.files[0]);
    }
}

// 🟢 ฟังก์ชันเพิ่มแถวส่วนผสม (Ingredients)
function addIngredient() {
    const container = document.getElementById('ingredient_container');
    const html = `
        <div class="row g-2 mb-2 ingredient-row fade-in align-items-center">
            <div class="col-md-5">
                <input type="text" name="ing_name[]" class="form-control bg-light border-0" placeholder="ชื่อส่วนผสม (เช่น แป้ง)" required>
            </div>
            <div class="col-md-3">
                <input type="number" name="ing_amount[]" class="form-control bg-light border-0" placeholder="ปริมาณ" step="0.01" required>
            </div>
            <div class="col-md-3">
                <input type="text" name="ing_unit[]" class="form-control bg-light border-0" placeholder="หน่วย (กรัม)" required>
            </div>
            <div class="col-md-1 text-center">
                <button type="button" class="btn btn-sm btn-light text-danger rounded-circle shadow-sm" onclick="removeRow(this)">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
}

// 🟣 ฟังก์ชันเพิ่มแถวแพ็คเกจ (Packages)
function addPackage() {
    const container = document.getElementById('package_container');
    const noMsg = document.getElementById('no_package_msg');
    if(noMsg) noMsg.style.display = 'none';

    const html = `
        <div class="row g-2 mb-2 package-row fade-in align-items-center">
            <div class="col-md-5">
                <input type="text" name="pack_name[]" class="form-control bg-light border-0" placeholder="ชื่อแพ็คเกจ (เช่น กล่องเล็ก)" required>
            </div>
            <div class="col-md-3">
                <input type="number" name="pack_amount[]" class="form-control bg-light border-0" placeholder="จำนวนชิ้น" required>
            </div>
            <div class="col-md-3">
                <div class="input-group">
                    <input type="number" name="pack_price[]" class="form-control bg-light border-0" placeholder="ราคาขาย" required>
                    <span class="input-group-text bg-light border-0 small">฿</span>
                </div>
            </div>
            <div class="col-md-1 text-center">
                <button type="button" class="btn btn-sm btn-light text-danger rounded-circle shadow-sm" onclick="removeRow(this)">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
}

// 🔴 ฟังก์ชันลบแถว
function removeRow(btn) {
    btn.closest('.row').remove();
    // เช็คถ้าลบแพ็คเกจหมด ให้โชว์ข้อความเดิม
    const packContainer = document.getElementById('package_container');
    if(packContainer.querySelectorAll('.package-row').length === 0) {
        const noMsg = document.getElementById('no_package_msg');
        if(noMsg) noMsg.style.display = 'block';
    }
}
</script>

<style>
    .hover-scale { transition: transform 0.2s; }
    .hover-scale:hover { transform: scale(1.05); }
    .fade-in { animation: fadeIn 0.3s; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
</style>

<?php include '../includes/footer.php'; ?>