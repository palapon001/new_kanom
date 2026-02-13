<?php
session_start();
require_once 'config.php';
require_once 'function.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ดึงข้อมูล User ล่าสุด
$user = selectOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
$role = $user['role'];

$theme = $config['theme'];
include 'includes/header.php';
include 'includes/navbar.php';
?>

<script src="https://api.longdo.com/map/?key=<?= $config['services']['longdo_map']['api_key'] ?>"></script>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                
                <div class="card-header bg-purple text-white py-4 text-center" style="background-color: <?= $theme['colors']['secondary'] ?>;">
                    <div class="position-relative d-inline-block mb-2">
                        <?php
                        $img = $user['profile_image'] ?? 'https://source.unsplash.com/150x150/?person';
                        if (!filter_var($img, FILTER_VALIDATE_URL)) $img = 'uploads/profiles/' . $img;
                        ?>
                        <img src="<?= $img ?>" class="rounded-circle border border-4 border-white shadow" width="120" height="120" style="object-fit: cover;">
                        <label for="profile_upload" class="position-absolute bottom-0 end-0 btn btn-sm btn-light rounded-circle shadow-sm" style="width: 35px; height: 35px; cursor: pointer;">
                            <i class="fas fa-camera text-muted mt-1"></i>
                        </label>
                    </div>
                    <h4 class="fw-bold mb-0"><?= htmlspecialchars($user['fullname'] ?? $user['shop_name']) ?></h4>
                    <span class="badge bg-white text-purple bg-opacity-75 rounded-pill px-3 mt-2">
                        <?= ($role == 'shop') ? 'ผู้ประกอบการ (Shop)' : 'สมาชิกทั่วไป (User)' ?>
                    </span>
                </div>

                <div class="card-body p-4 p-md-5">

                    <form action="process/profile_update.php" method="POST" enctype="multipart/form-data">

                        <input type="file" id="profile_upload" name="profile_image" class="d-none" accept="image/*">

                        <h6 class="fw-bold text-muted border-bottom pb-2 mb-3">ข้อมูลทั่วไป</h6>

                        <?php if ($role == 'shop'): ?>
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted">ชื่อร้านค้า</label>
                                <input type="text" name="shop_name" class="form-control" value="<?= htmlspecialchars($user['shop_name']) ?>" required>
                            </div>
                        <?php endif; ?>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">ชื่อ-นามสกุล (ผู้ติดต่อ)</label>
                                <input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($user['fullname']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">เบอร์โทรศัพท์</label>
                                <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted">ที่อยู่ (สำหรับจัดส่ง / ที่ตั้งร้าน)</label>
                            <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($user['address']) ?></textarea>
                        </div>

                        <?php if ($role == 'shop'): ?>
                            <h6 class="fw-bold text-muted border-bottom pb-2 mb-3 mt-4">
                                <i class="fas fa-map-marker-alt me-2 text-danger"></i>ตำแหน่งร้านค้า
                            </h6>

                            <div class="mb-3">
                                <p class="small text-muted mb-2">ระบุตำแหน่งร้านของคุณ เพื่อให้ลูกค้าค้นหาเจอ</p>

                                <div class="d-flex gap-2 mb-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" onclick="getCurrentLocation()">
                                        <i class="fas fa-location-arrow me-1"></i> ตำแหน่งปัจจุบัน
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" onclick="pinLocation()">
                                        <i class="fas fa-map-pin me-1"></i> ปักหมุดตรงกลาง
                                    </button>
                                </div>

                                <div id="map" class="rounded-3 border shadow-sm" style="height: 400px; position: relative;">
                                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 1000; pointer-events: none;">
                                        <i class="fas fa-plus text-danger fa-2x" style="text-shadow: 0 0 5px white;"></i>
                                    </div>
                                </div>

                                <div class="mt-2">
                                    <label class="small text-muted fw-bold">พิกัด GPS:</label>
                                    <input type="text" id="GPS" class="form-control bg-light" readonly 
                                           placeholder="ยังไม่ได้ระบุพิกัด" 
                                           value="<?= ($user['latitude']) ? $user['latitude'].', '.$user['longitude'] : '' ?>">
                                </div>

                                <input type="hidden" name="latitude" id="lat" value="<?= $user['latitude'] ?>">
                                <input type="hidden" name="longitude" id="lon" value="<?= $user['longitude'] ?>">
                            </div>
                            <?php endif; ?>

                        <h6 class="fw-bold text-muted border-bottom pb-2 mb-3 mt-4">เปลี่ยนรหัสผ่าน (ถ้าต้องการ)</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <input type="password" name="old_password" class="form-control bg-light" placeholder="รหัสผ่านเดิม">
                            </div>
                            <div class="col-md-4">
                                <input type="password" name="new_password" class="form-control bg-light" placeholder="รหัสผ่านใหม่">
                            </div>
                            <div class="col-md-4">
                                <input type="password" name="confirm_password" class="form-control bg-light" placeholder="ยืนยันรหัสผ่านใหม่">
                            </div>
                        </div>

                        <div class="d-grid mt-5">
                            <button type="submit" class="btn btn-nia py-3 fw-bold shadow-sm">
                                <i class="fas fa-save me-2"></i> บันทึกการเปลี่ยนแปลง
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var map;
    var marker;

    function init() {
        // 1. เริ่มต้นแผนที่
        map = new longdo.Map({
            placeholder: document.getElementById('map'),
            language: 'th'
        });

        // 2. เช็คว่ามีพิกัดเดิมจาก Database ไหม
        var savedLat = parseFloat(document.getElementById('lat').value);
        var savedLon = parseFloat(document.getElementById('lon').value);

        if (savedLat && savedLon) {
            // ✅ CASE A: มีพิกัดเดิม -> โชว์ตำแหน่งเดิม
            var userLocation = { lat: savedLat, lon: savedLon };
            map.location(userLocation);
            addMarker(userLocation);
        } else {
            // ❌ CASE B: ไม่มีพิกัดเดิม -> ขอพิกัดปัจจุบัน (Auto GPS)
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    // เจอตำแหน่ง: ย้ายแมพไป + ปักหมุดให้เลย
                    var currentLat = position.coords.latitude;
                    var currentLon = position.coords.longitude;
                    var loc = { lat: currentLat, lon: currentLon };
                    
                    map.location(loc);
                    updatePosition(loc); // ปักหมุดและอัปเดต Input ทันที
                    
                }, function(error) {
                    // ไม่เจอ/ไม่อนุญาต: ไป Default เพชรบุรี
                    console.warn("GPS Error: " + error.message);
                    useDefaultLocation();
                });
            } else {
                // Browser ไม่รองรับ: ไป Default เพชรบุรี
                useDefaultLocation();
            }
        }

        // 3. ดักจับ event เมื่อคลิกบนแผนที่ (เพื่อย้ายหมุดเอง)
        map.Event.bind('click', function(location) {
            updatePosition(location);
        });
    }

    // ฟังก์ชันสำหรับตำแหน่ง Default (เพชรบุรี)
    function useDefaultLocation() {
        map.location({ lat: 13.1126, lon: 99.9398 });
    }

    // ฟังก์ชันอัปเดตทุกอย่าง (Marker + Input + GPS Field)
    function updatePosition(location) {
        // ลบหมุดเก่า
        map.Overlays.clear();

        // สร้างหมุดใหม่
        marker = new longdo.Marker(location);
        map.Overlays.add(marker);

        // อัปเดตค่าลง Input Hidden (สำหรับส่งเข้า Database)
        document.getElementById('lat').value = location.lat;
        document.getElementById('lon').value = location.lon;

        // อัปเดตค่าลงช่องโชว์ GPS ให้ User เห็น
        document.getElementById('GPS').value = location.lat + ', ' + location.lon;
    }

    // ปุ่มกด: หาตำแหน่งปัจจุบัน (Manual Click)
    function getCurrentLocation() {
        if (navigator.geolocation) {
            Swal.fire({
                title: 'กำลังระบุตำแหน่ง...',
                timer: 2000,
                didOpen: () => { Swal.showLoading() }
            });

            navigator.geolocation.getCurrentPosition(function(position) {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;
                const location = { lat: lat, lon: lon };

                map.location(location, true); // true = animation
                updatePosition(location);
                Swal.close();

            }, function(error) {
                Swal.fire('แจ้งเตือน', 'ไม่สามารถดึงตำแหน่งได้ หรือคุณไม่ได้กดอนุญาต', 'error');
            });
        }
    }

    // ปุ่มกด: ปักหมุดจากจุดกึ่งกลางแผนที่
    function pinLocation() {
        const currentLocation = map.location();
        updatePosition(currentLocation);
    }

    // เรียกใช้งานเมื่อโหลดหน้าเว็บเสร็จ
    init();
</script>

<?php include 'includes/footer.php'; ?>