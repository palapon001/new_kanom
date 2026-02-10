<?php
session_start();
require_once '../config.php';
require_once '../function.php';

// Check Shop Role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'shop') {
    header("Location: ../login.php");
    exit();
}

$shop_id = $_SESSION['user_id'];
$theme = $config['theme'];

// =========================================================
// 🟢 1. ใช้ selectOne() เพื่อดึงข้อมูลร้านค้า (ตามคำขอ)
// =========================================================
// ดึงชื่อร้านค้า หรือ ชื่อผู้ใช้ เพื่อนำไปแสดงผล
$shop_info = selectOne("SELECT username FROM users WHERE id = ?", [$shop_id]);
$shop_name = $shop_info['username'] ?? 'ร้านค้าของคุณ';


// =========================================================
// 🟢 2. ใช้ select() เพื่อดึงข้อมูลสินค้าและส่วนผสม (หลายรายการ)
// =========================================================
$recipes_data = [];

// Query ดึงสินค้า + ส่วนผสม
$sql = "SELECT p.id as product_id, p.name as product_name, 
               pi.ingredient_name, pi.amount, pi.unit
        FROM products p
        LEFT JOIN product_ingredients pi ON p.id = pi.product_id
        WHERE p.shop_id = ? AND p.status = 'active'
        ORDER BY p.name ASC";

// ✅ ใช้ฟังก์ชัน select() ของเราเอง (สะอาดและอ่านง่ายกว่า)
$rows = select($sql, [$shop_id]);

// จัดรูปแบบข้อมูล (Loop เหมือนเดิม)
foreach ($rows as $row) {
    $pid = $row['product_id'];
    
    if (!isset($recipes_data[$pid])) {
        $recipes_data[$pid] = [
            'name' => $row['product_name'],
            'ingredients' => []
        ];
    }

    if (!empty($row['ingredient_name'])) {
        $recipes_data[$pid]['ingredients'][] = [
            'name' => $row['ingredient_name'],
            'amount' => (float)$row['amount'],
            'unit' => $row['unit'],
            'unit_cost' => 1.5 // (สมมติราคาต้นทุน)
        ];
    }
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <div class="card border-0 shadow-lg mb-4" style="border-radius: <?= $theme['ui']['radius'] ?>; overflow: hidden;">
                <div class="card-header text-white py-3 d-flex justify-content-between align-items-center" 
                     style="background: linear-gradient(135deg, <?= $theme['colors']['secondary'] ?> 0%, <?= $theme['colors']['primary'] ?> 100%);">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-calculator fa-lg me-2 text-warning"></i>
                        <div>
                            <h5 class="mb-0 fw-bold">Smart Recipe Calculator</h5>
                            <small class="text-white-50" style="font-size: 0.8rem;">สำหรับ: <?= htmlspecialchars($shop_name) ?></small>
                        </div>
                    </div>
                    <span class="badge bg-white text-purple rounded-pill px-3">ระบบคำนวณสูตรและต้นทุน</span>
                </div>
                
                <div class="card-body p-4">
                    <div class="row g-3 align-items-end mb-4">
                        <div class="col-md-5">
                            <label class="form-label fw-bold text-muted small text-uppercase">เลือกเมนูขนม (Select Menu)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="fas fa-utensils text-purple"></i></span>
                                <select id="menuSelect" class="form-select form-select-lg bg-light border-0 fw-bold text-dark">
                                    <option value="" disabled selected>-- เลือกสินค้า --</option>
                                    <?php if(empty($recipes_data)): ?>
                                        <option value="" disabled>ยังไม่มีสินค้า (ต้องเพิ่มสินค้าก่อน)</option>
                                    <?php else: ?>
                                        <?php foreach ($recipes_data as $id => $data): ?>
                                            <option value="<?= $id ?>"><?= htmlspecialchars($data['name']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-muted small text-uppercase">จำนวนที่ต้องการผลิต (ชุด/ชิ้น)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="fas fa-sort-numeric-up text-purple"></i></span>
                                <input type="number" id="quantity" class="form-control form-control-lg bg-light border-0 fw-bold text-center" value="1" min="1">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button onclick="calculate()" class="btn btn-nia btn-lg w-100 shadow-sm fw-bold">
                                <i class="fas fa-magic me-2"></i> คำนวณ
                            </button>
                        </div>
                    </div>

                    <hr class="opacity-10 my-4">

                    <div id="resultSection" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="text-purple fw-bold mb-0"><i class="fas fa-clipboard-list me-2"></i>รายการวัตถุดิบที่ต้องใช้</h5>
                            <button onclick="window.print()" class="btn btn-sm btn-outline-secondary rounded-pill">
                                <i class="fas fa-print me-1"></i> พิมพ์สูตร
                            </button>
                        </div>

                        <div class="table-responsive rounded-4 shadow-sm border">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-secondary">
                                    <tr>
                                        <th class="ps-4 py-3">วัตถุดิบ (Ingredient)</th>
                                        <th class="text-center">ปริมาณต่อหน่วย</th>
                                        <th class="text-end text-primary">รวมปริมาณที่ต้องใช้</th>
                                        <th class="text-center">หน่วย</th>
                                        <th class="text-end pe-4">ต้นทุนโดยประมาณ (บาท)*</th>
                                    </tr>
                                </thead>
                                <tbody id="resultBody"></tbody>
                                <tfoot class="bg-light">
                                    <tr>
                                        <td colspan="4" class="text-end fw-bold py-3">รวมต้นทุนวัตถุดิบทั้งหมด:</td>
                                        <td class="text-end fw-bold text-danger pe-4 fs-5" id="totalCostDisplay">0.00</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div id="emptyState" class="text-center py-5">
                        <div class="bg-light rounded-circle d-inline-flex p-4 mb-3">
                            <i class="fas fa-cookie-bite fa-3x text-muted opacity-25"></i>
                        </div>
                        <p class="text-muted">กรุณาเลือกสินค้าและใส่จำนวน แล้วกดปุ่ม "คำนวณ"</p>
                    </div>

                </div>
            </div>
            
        </div>
    </div>
</div>

<script>
// รับข้อมูลจาก PHP
const recipes = <?= json_encode($recipes_data, JSON_UNESCAPED_UNICODE) ?>;

function calculate() {
    const menuKey = document.getElementById('menuSelect').value;
    const qty = parseFloat(document.getElementById('quantity').value);
    
    const resultBody = document.getElementById('resultBody');
    const resultSection = document.getElementById('resultSection');
    const emptyState = document.getElementById('emptyState');
    const totalCostDisplay = document.getElementById('totalCostDisplay');

    if (!menuKey) {
        Swal.fire({ icon: 'warning', title: 'กรุณาเลือกสินค้า', confirmButtonColor: '#2D1F57' });
        return;
    }
    if (isNaN(qty) || qty <= 0) {
        Swal.fire({ icon: 'warning', title: 'ระบุจำนวนไม่ถูกต้อง', confirmButtonColor: '#2D1F57' });
        return;
    }

    let html = '';
    let grandTotalCost = 0;

    if(recipes[menuKey]) {
        if(recipes[menuKey].ingredients.length === 0) {
            Swal.fire({ 
                icon: 'info', 
                title: 'ไม่พบข้อมูลส่วนผสม', 
                text: 'สินค้านี้ยังไม่ได้บันทึกข้อมูลส่วนผสมในระบบ',
                confirmButtonColor: '#2D1F57' 
            });
            return;
        }

        recipes[menuKey].ingredients.forEach(item => {
            let totalAmount = item.amount * qty;
            let totalCost = totalAmount * item.unit_cost;
            grandTotalCost += totalCost;

            let showAmount = totalAmount.toLocaleString(undefined, {minimumFractionDigits: 0, maximumFractionDigits: 2});
            let showCost = totalCost.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});

            html += `<tr>
                <td class="ps-4"><span class="fw-medium text-dark">${item.name}</span></td>
                <td class="text-center text-muted small">${item.amount}</td>
                <td class="text-end fw-bold text-primary fs-5">${showAmount}</td>
                <td class="text-center text-muted">${item.unit}</td>
                <td class="text-end pe-4 fw-bold text-dark">${showCost}</td>
            </tr>`;
        });

        resultBody.innerHTML = html;
        totalCostDisplay.innerHTML = '฿' + grandTotalCost.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        
        emptyState.style.display = 'none';
        resultSection.style.display = 'block';
    }
}
</script>

<style>
@media print {
    body * { visibility: hidden; }
    #resultSection, #resultSection * { visibility: visible; }
    #resultSection { position: absolute; left: 0; top: 0; width: 100%; }
    .btn, .alert { display: none !important; }
}
</style>

<?php include '../includes/footer.php'; ?>