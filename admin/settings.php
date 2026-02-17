<?php
session_start();
$path_prefix = '../';
require_once '../config.php';
require_once '../function.php'; 

// 1. เช็คสิทธิ์ Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// ==================================================================================
// 💾 ส่วนจัดการบันทึกข้อมูล
// ==================================================================================

// A. บันทึก Page Settings (SEO & Maintenance รายหน้า)
if (isset($_POST['action']) && $_POST['action'] == 'save_pages') {
    $data = [
        'page_url'      => trim($_POST['page_url']),
        'page_title'    => trim($_POST['page_title']),
        'page_desc'     => trim($_POST['page_desc']),
        'is_maintenance'=> isset($_POST['is_maintenance']) ? 1 : 0
    ];
    
    if(isset($_POST['page_keywords'])) $data['page_keywords'] = trim($_POST['page_keywords']);

    $page_id = $_POST['page_id'];

    if (!empty($page_id)) {
        // แก้ไข
        $result = update('page_settings', $data, "id = ?", [$page_id]);
    } else {
        // เพิ่มใหม่
        $result = insert('page_settings', $data);
    }
    
    if ($result) {
        $_SESSION['success'] = "บันทึกข้อมูลหน้าเว็บเรียบร้อย";
    } else {
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการบันทึก";
    }
    
    header("Location: settings.php?tab=seo");
    exit();
}

// B. ลบ Page Settings
if (isset($_GET['delete_page'])) {
    $id = intval($_GET['delete_page']);
    delete('page_settings', "id = ?", [$id]);
    $_SESSION['success'] = "ลบการตั้งค่าหน้าเว็บแล้ว";
    header("Location: settings.php?tab=seo");
    exit();
}

// C. บันทึก Site Settings
if (isset($_POST['action']) && $_POST['action'] == 'save_settings') {
    foreach ($_POST as $key => $val) {
        if ($key == 'action' || $key == 'current_tab') continue;
        
        $val = trim($val);
        $exists = selectOne("SELECT setting_key FROM site_settings WHERE setting_key = ?", [$key]);
        
        if ($exists) {
            update('site_settings', ['setting_value' => $val], "setting_key = ?", [$key]);
        } else {
            insert('site_settings', ['setting_key' => $key, 'setting_value' => $val]);
        }
    }
    $_SESSION['success'] = "บันทึกการตั้งค่าเรียบร้อย";
    header("Location: settings.php?tab=" . $_POST['current_tab']);
    exit();
}

// ==================================================================================
// 📥 ส่วนดึงข้อมูล
// ==================================================================================

$s = [];
$settings_data = select("SELECT * FROM site_settings");
if($settings_data) {
    foreach ($settings_data as $row) {
        $s[$row['setting_key']] = $row['setting_value'];
    }
}

function getVal($key, $data, $default = '') { 
    return isset($data[$key]) ? htmlspecialchars($data[$key]) : $default; 
}

$pages_list = select("SELECT * FROM page_settings ORDER BY id DESC");
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';

include '../includes/header.php';

if(file_exists('../includes/admin_sidebar.php')) {
    include '../includes/admin_sidebar.php'; 
} else {
    include '../includes/navbar.php';
}
?>

<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark"><i class="fas fa-cogs me-2 text-secondary"></i>ตั้งค่าระบบ</h3>
        <a href="../index.php" target="_blank" class="btn btn-outline-secondary rounded-pill btn-sm">
            <i class="fas fa-external-link-alt me-1"></i> ดูหน้าเว็บ
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-bottom-0 p-0">
            <ul class="nav nav-tabs nav-fill" id="settingTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link py-3 fw-bold <?= $active_tab=='general'?'active':'' ?>" data-bs-toggle="tab" data-bs-target="#tab-general">
                        <i class="fas fa-info-circle me-2"></i>ทั่วไป
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link py-3 fw-bold <?= $active_tab=='theme'?'active':'' ?>" data-bs-toggle="tab" data-bs-target="#tab-theme">
                        <i class="fas fa-palette me-2"></i>ธีม & สี
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link py-3 fw-bold <?= $active_tab=='api'?'active':'' ?>" data-bs-toggle="tab" data-bs-target="#tab-api">
                        <i class="fas fa-code me-2"></i>API
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link py-3 fw-bold <?= $active_tab=='seo'?'active':'' ?>" data-bs-toggle="tab" data-bs-target="#tab-seo">
                        <i class="fas fa-search me-2"></i>SEO รายหน้า
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-4 bg-light bg-opacity-10">
            <div class="tab-content">
                
                <div class="tab-pane fade <?= $active_tab=='general'?'show active':'' ?>" id="tab-general">
                    <form method="POST">
                        <input type="hidden" name="action" value="save_settings">
                        <input type="hidden" name="current_tab" value="general">
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="bg-white p-4 rounded shadow-sm h-100">
                                    <h6 class="fw-bold text-primary mb-3"><i class="fas fa-desktop me-2"></i>ข้อมูลเว็บไซต์</h6>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">ชื่อเว็บไซต์ (App Name)</label>
                                        <input type="text" name="app_name" class="form-control" value="<?= getVal('app_name', $s, 'KanomMuangPhet') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">หัวข้อเว็บ (Browser Title)</label>
                                        <input type="text" name="site_title" class="form-control" value="<?= getVal('site_title', $s) ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">คำอธิบาย (Meta Description)</label>
                                        <textarea name="site_desc" class="form-control" rows="3"><?= getVal('site_desc', $s) ?></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-white p-4 rounded shadow-sm h-100">
                                    <h6 class="fw-bold text-success mb-3"><i class="fas fa-address-book me-2"></i>ข้อมูลติดต่อ</h6>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">เบอร์โทรศัพท์</label>
                                        <input type="text" name="contact_phone" class="form-control" value="<?= getVal('contact_phone', $s) ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">อีเมล</label>
                                        <input type="email" name="contact_email" class="form-control" value="<?= getVal('contact_email', $s) ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">ที่อยู่ติดต่อ</label>
                                        <textarea name="contact_address" class="form-control" rows="3"><?= getVal('contact_address', $s) ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                                <i class="fas fa-save me-2"></i> บันทึกข้อมูลทั่วไป
                            </button>
                        </div>
                    </form>
                </div>

                <div class="tab-pane fade <?= $active_tab=='theme'?'show active':'' ?>" id="tab-theme">
                    <form method="POST">
                        <input type="hidden" name="action" value="save_settings">
                        <input type="hidden" name="current_tab" value="theme">

                        <div class="bg-white p-4 rounded shadow-sm">
                            <h6 class="fw-bold text-dark mb-4 border-bottom pb-3">ปรับแต่งสี (Color Scheme)</h6>
                            
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <div class="card bg-light border-0 h-100">
                                        <div class="card-body text-center">
                                            <h6 class="fw-bold text-muted mb-3"><i class="fas fa-bars me-2"></i>ส่วนหัว (Navbar)</h6>
                                            <div class="mb-3">
                                                <input type="color" name="color_navbar" class="form-control form-control-color w-100 mb-1" value="<?= getVal('color_navbar', $s, '#2D1F57') ?>">
                                                <small class="text-muted fw-bold" style="font-size: 0.8rem;">สีพื้นหลัง</small>
                                            </div>
                                            <div>
                                                <input type="color" name="color_navbar_text" class="form-control form-control-color w-100 mb-1" value="<?= getVal('color_navbar_text', $s, '#ffffff') ?>">
                                                <small class="text-muted fw-bold" style="font-size: 0.8rem;">สีตัวอักษร</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="card bg-light border-0 h-100">
                                        <div class="card-body text-center">
                                            <h6 class="fw-bold text-muted mb-3"><i class="fas fa-file-alt me-2"></i>เนื้อหา (Content)</h6>
                                            <div class="mb-3">
                                                <input type="color" name="color_body" class="form-control form-control-color w-100 mb-1" value="<?= getVal('color_body', $s, '#F4F6F9') ?>">
                                                <small class="text-muted fw-bold" style="font-size: 0.8rem;">สีพื้นหลัง</small>
                                            </div>
                                            <div>
                                                <input type="color" name="color_body_text" class="form-control form-control-color w-100 mb-1" value="<?= getVal('color_body_text', $s, '#333333') ?>">
                                                <small class="text-muted fw-bold" style="font-size: 0.8rem;">สีตัวอักษร</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="card bg-light border-0 h-100">
                                        <div class="card-body text-center">
                                            <h6 class="fw-bold text-muted mb-3"><i class="fas fa-shoe-prints me-2"></i>ส่วนท้าย (Footer)</h6>
                                            <div class="mb-3">
                                                <input type="color" name="color_footer_bg" class="form-control form-control-color w-100 mb-1" value="<?= getVal('color_footer_bg', $s, '#343a40') ?>">
                                                <small class="text-muted fw-bold" style="font-size: 0.8rem;">สีพื้นหลัง</small>
                                            </div>
                                            <div>
                                                <input type="color" name="color_footer_text" class="form-control form-control-color w-100 mb-1" value="<?= getVal('color_footer_text', $s, '#ffffff') ?>">
                                                <small class="text-muted fw-bold" style="font-size: 0.8rem;">สีตัวอักษร</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                                <i class="fas fa-save me-2"></i> บันทึกการตั้งค่าธีม
                            </button>
                        </div>
                    </form>
                </div>

                <div class="tab-pane fade <?= $active_tab=='api'?'show active':'' ?>" id="tab-api">
                    <form method="POST">
                        <input type="hidden" name="action" value="save_settings">
                        <input type="hidden" name="current_tab" value="api">
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="bg-white p-4 rounded shadow-sm h-100">
                                    <h6 class="fw-bold text-success mb-3"><i class="fab fa-line me-2"></i>LINE Official & Notify</h6>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Channel ID</label>
                                        <input type="text" name="line_client_id" class="form-control" value="<?= getVal('line_client_id', $s) ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Channel Secret</label>
                                        <input type="password" name="line_client_secret" class="form-control" value="<?= getVal('line_client_secret', $s) ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Notify Token (Admin Alert)</label>
                                        <input type="text" name="line_notify_token" class="form-control" value="<?= getVal('line_notify_token', $s) ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="bg-white p-4 rounded shadow-sm h-100">
                                    <h6 class="fw-bold text-danger mb-3"><i class="fas fa-map-marked-alt me-2"></i>Map API Keys</h6>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Google Maps API Key</label>
                                        <input type="text" name="google_map_key" class="form-control" value="<?= getVal('google_map_key', $s) ?>">
                                    </div>
                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <label class="form-label small fw-bold">Default Lat</label>
                                            <input type="text" name="map_default_lat" class="form-control" value="<?= getVal('map_default_lat', $s) ?>">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small fw-bold">Default Long</label>
                                            <input type="text" name="map_default_long" class="form-control" value="<?= getVal('map_default_long', $s) ?>">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Longdo Map Key</label>
                                        <input type="text" name="longdo_map_key" class="form-control" value="<?= getVal('longdo_map_key', $s) ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                                <i class="fas fa-save me-2"></i> บันทึก API
                            </button>
                        </div>
                    </form>
                </div>

                <div class="tab-pane fade <?= $active_tab=='seo'?'show active':'' ?>" id="tab-seo">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="alert alert-info border-0 bg-info bg-opacity-10 small mb-0 py-2 px-3">
                            <i class="fas fa-info-circle me-2"></i> จัดการ Meta Tag และสั่งปิดปรับปรุงเฉพาะหน้า
                        </div>
                        <button class="btn btn-primary rounded-pill shadow-sm btn-sm px-3" onclick="openAddModal()">
                            <i class="fas fa-plus me-1"></i> เพิ่มหน้า
                        </button>
                    </div>

                    <div class="table-responsive bg-white rounded shadow-sm">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th>URL / File</th>
                                    <th>SEO Title</th>
                                    <th class="text-center">สถานะ</th>
                                    <th class="text-end">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(count($pages_list) > 0): ?>
                                    <?php foreach($pages_list as $p): ?>
                                    <tr>
                                        <td><code class="text-primary fw-bold"><?= $p['page_url'] ?></code></td>
                                        <td><?= $p['page_title'] ?: '<span class="text-muted">-</span>' ?></td>
                                        <td class="text-center">
                                            <?php if($p['is_maintenance']): ?>
                                                <span class="badge bg-danger rounded-pill">ปิดปรับปรุง</span>
                                            <?php else: ?>
                                                <span class="badge bg-success rounded-pill">ใช้งานปกติ</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-warning me-1" onclick='editPage(<?= htmlspecialchars(json_encode($p), ENT_QUOTES, 'UTF-8') ?>)'>
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="?delete_page=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('ยืนยันที่จะลบการตั้งค่าหน้านี้?')"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center py-4 text-muted">ยังไม่มีข้อมูล</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="pageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="pageForm">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalTitle">จัดการหน้าเว็บ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="save_pages">
                    <input type="hidden" name="page_id" id="page_id">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">URL / File Name <span class="text-danger">*</span></label>
                        <input type="text" name="page_url" id="page_url" class="form-control" placeholder="เช่น index.php" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">SEO Title</label>
                        <input type="text" name="page_title" id="page_title" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Meta Description</label>
                        <textarea name="page_desc" id="page_desc" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Keywords</label>
                        <input type="text" name="page_keywords" id="page_keywords" class="form-control">
                    </div>
                    <div class="form-check form-switch bg-light p-3 rounded">
                        <input class="form-check-input ms-0 me-2" type="checkbox" name="is_maintenance" id="is_maintenance">
                        <label class="form-check-label fw-bold text-danger" for="is_maintenance">เปิดโหมดปิดปรับปรุงหน้านี้</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .nav-tabs .nav-link { color: #6c757d; border: none; border-bottom: 3px solid transparent; background-color: var(--nia-primary, #E6007E); }
    .nav-tabs .nav-link.active { color: var(--nia-primary, #E6007E); border-bottom: 3px solid var(--nia-primary, #E6007E);  background-color: var(--nia-primary, #E6007E) !important;  }
    .form-control-color { width: 100%; height: 40px; padding: 2px; cursor: pointer; }
    .card { transition: transform 0.2s; }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php if (isset($_SESSION['success'])): ?>
    <script>Swal.fire({icon: 'success', title: 'สำเร็จ!', text: '<?= $_SESSION['success'] ?>', timer: 1500, showConfirmButton: false}); <?php unset($_SESSION['success']); ?></script>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
    <script>Swal.fire({icon: 'error', title: 'เกิดข้อผิดพลาด', text: '<?= $_SESSION['error'] ?>', timer: 2000, showConfirmButton: false}); <?php unset($_SESSION['error']); ?></script>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const myModal = new bootstrap.Modal(document.getElementById('pageModal'));

    function openAddModal() {
        document.getElementById('pageForm').reset();
        document.getElementById('page_id').value = '';
        document.getElementById('modalTitle').innerText = 'เพิ่มการตั้งค่าหน้าใหม่';
        myModal.show();
    }

    function editPage(data) {
        document.getElementById('page_id').value = data.id;
        document.getElementById('page_url').value = data.page_url;
        document.getElementById('page_title').value = data.page_title;
        document.getElementById('page_desc').value = data.page_desc;
        document.getElementById('page_keywords').value = data.page_keywords || '';
        document.getElementById('is_maintenance').checked = (data.is_maintenance == 1);
        
        document.getElementById('modalTitle').innerText = 'แก้ไขหน้า: ' + data.page_url;
        myModal.show();
    }
</script>

</body>
</html>