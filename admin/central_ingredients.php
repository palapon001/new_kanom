<?php
session_start();
require_once '../config.php';
require_once '../function.php';

// 1. เช็คสิทธิ์ Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$theme = $config['theme'];
$error = $success = '';

// ==========================================
// 🛠️ Process: จัดการข้อมูล (Add / Edit / Delete)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 🟢 เพิ่มข้อมูลใหม่
    if (isset($_POST['action']) && $_POST['action'] == 'add') {
        $name = trim($_POST['name']);
        $unit = trim($_POST['unit']);
        
        if (!empty($name) && !empty($unit)) {
            insert('central_ingredients', ['name' => $name, 'unit' => $unit]);
            $success = "เพิ่มข้อมูลเรียบร้อยแล้ว";
        } else {
            $error = "กรุณากรอกข้อมูลให้ครบถ้วน";
        }
    }
    // 🟡 แก้ไขข้อมูล
    elseif (isset($_POST['action']) && $_POST['action'] == 'edit') {
        $id = $_POST['id'];
        $name = trim($_POST['name']);
        $unit = trim($_POST['unit']);
        
        update('central_ingredients', ['name' => $name, 'unit' => $unit], "id = ?", [$id]);
        $success = "แก้ไขข้อมูลเรียบร้อยแล้ว";
    }
}

// 🔴 ลบข้อมูล (GET Request)
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    // เช็คก่อนว่ามีสินค้าใช้อยู่ไหม (ถ้ามีไม่ควรลบ หรือแค่แจ้งเตือน)
    // ในที่นี้ลบเลยเพื่อความง่าย
    delete('central_ingredients', "id = ?", [$id]);
    $success = "ลบข้อมูลเรียบร้อยแล้ว";
}

// ดึงข้อมูลทั้งหมดมาแสดง
$items = select("SELECT * FROM central_ingredients ORDER BY name ASC");

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold text-purple"><i class="fas fa-layer-group me-2"></i>จัดการชนิดวัตถุดิบ (ราคากลาง)</h3>
                <button class="btn btn-nia rounded-pill px-4 shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="fas fa-plus me-1"></i> เพิ่มรายการใหม่
                </button>
            </div>

            <?php if($success): ?>
                <div class="alert alert-success rounded-3 shadow-sm border-0 mb-4"><i class="fas fa-check-circle me-2"></i><?= $success ?></div>
            <?php endif; ?>
            <?php if($error): ?>
                <div class="alert alert-danger rounded-3 shadow-sm border-0 mb-4"><i class="fas fa-exclamation-circle me-2"></i><?= $error ?></div>
            <?php endif; ?>

            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-secondary">
                                <tr>
                                    <th class="ps-4 py-3">ชื่อวัตถุดิบ</th>
                                    <th class="py-3">หน่วยนับ</th>
                                    <th class="py-3 text-end pe-4">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($items)): ?>
                                    <tr><td colspan="3" class="text-center py-5 text-muted">ยังไม่มีข้อมูล</td></tr>
                                <?php else: ?>
                                    <?php foreach($items as $row): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold"><?= htmlspecialchars($row['name']) ?></td>
                                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($row['unit']) ?></span></td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-sm btn-outline-warning border-0" 
                                                    onclick="editItem(<?= $row['id'] ?>, '<?= htmlspecialchars($row['name']) ?>', '<?= htmlspecialchars($row['unit']) ?>')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="?action=delete&id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger border-0" onclick="return confirm('ยืนยันการลบ?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="mt-3 text-muted small">
                <i class="fas fa-info-circle me-1"></i> ข้อมูลเหล่านี้จะไปปรากฏในหน้า "เพิ่มสินค้า" ของร้านค้า เพื่อให้ร้านค้าเลือกชนิดให้ตรงกัน
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-purple text-white">
                <h5 class="modal-title fw-bold">เพิ่มวัตถุดิบมาตรฐาน</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="action" value="add">
                <div class="mb-3">
                    <label class="form-label fw-bold">ชื่อวัตถุดิบ</label>
                    <input type="text" name="name" class="form-control" placeholder="เช่น ไข่ไก่, น้ำตาลโตนด" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">หน่วยนับ</label>
                    <input type="text" name="unit" class="form-control" placeholder="เช่น ฟอง, กิโลกรัม, ลิตร" required>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="btn btn-nia fw-bold">บันทึก</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold">แก้ไขข้อมูล</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="mb-3">
                    <label class="form-label fw-bold">ชื่อวัตถุดิบ</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">หน่วยนับ</label>
                    <input type="text" name="unit" id="edit_unit" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="btn btn-warning fw-bold">บันทึกการแก้ไข</button>
            </div>
        </form>
    </div>
</div>

<script>
// ฟังก์ชันส่งค่าเข้า Modal แก้ไข
function editItem(id, name, unit) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_unit').value = unit;
    
    var myModal = new bootstrap.Modal(document.getElementById('editModal'));
    myModal.show();
}
</script>

<?php include '../includes/footer.php'; ?>