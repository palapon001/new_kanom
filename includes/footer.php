<?php
// ดึงสีจาก Config (ถ้าไม่มีใช้ Default)
$footer_bg   = $config['theme']['colors']['footer_bg']   ?? '#343a40';
$footer_text = $config['theme']['colors']['footer_text'] ?? '#ffffff';
$accent_color = $config['theme']['colors']['accent']     ?? '#FDB913'; // สีทอง
$primary_color = $config['theme']['colors']['primary']   ?? '#E6007E'; // สีชมพู
?>

<footer class="mt-auto position-relative" 
        style="background-color: <?= $footer_bg ?> !important; color: <?= $footer_text ?> !important; border-top: 5px solid <?= $primary_color ?>;">

    <div style="position: absolute; top: 0; right: 0; width: 200px; height: 100%; opacity: 0.05; background: url('https://www.transparenttextures.com/patterns/cubes.png'); pointer-events: none;"></div>

    <div class="container py-5 position-relative z-1">
        <div class="row g-4">
            
            <div class="col-lg-4 col-md-6">
                <a href="#" class="d-flex align-items-center mb-3 text-decoration-none" style="color: <?= $footer_text ?> !important;">
                    <div class="bg-white rounded-circle d-flex justify-content-center align-items-center me-2 p-1" style="width: 40px; height: 40px;">
                        <img src="<?= $path_prefix ?>assets/icon.svg" alt="Logo" class="img-fluid">
                    </div>
                    <span class="fw-bold text-uppercase ls-1 fs-5"><?= $config['app']['name'] ?></span>
                </a>
                <p class="small mb-4 opacity-75" style="line-height: 1.6;">
                    <?= $config['app']['desc'] ?><br>
                    แพลตฟอร์มที่เชื่อมโยงภูมิปัญญาท้องถิ่นสู่นวัตกรรมสากล<br>สนับสนุนโดยเครือข่ายเมืองนวัตกรรมอาหาร
                </p>
                <div class="d-flex gap-2">
                    <a href="#" class="btn btn-sm rounded-circle d-flex align-items-center justify-content-center border-white-50 text-white hover-up" 
                       style="width: 36px; height: 36px; border: 1px solid rgba(255,255,255,0.3);">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="btn btn-sm rounded-circle d-flex align-items-center justify-content-center border-white-50 text-white hover-up" 
                       style="width: 36px; height: 36px; border: 1px solid rgba(255,255,255,0.3);">
                        <i class="fab fa-line"></i>
                    </a>
                    <a href="#" class="btn btn-sm rounded-circle d-flex align-items-center justify-content-center border-white-50 text-white hover-up" 
                       style="width: 36px; height: 36px; border: 1px solid rgba(255,255,255,0.3);">
                        <i class="fas fa-globe"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 ps-lg-5">
                <h5 class="fw-bold mb-3" style="color: <?= $accent_color ?> !important;">เมนูด่วน</h5>
                <ul class="list-unstyled small opacity-75">
                    <li class="mb-2"><a href="<?= $path_prefix ?>index.php" class="text-decoration-none hover-link" style="color: inherit;"><i class="fas fa-angle-right me-2"></i>หน้าแรก</a></li>
                    <li class="mb-2"><a href="<?= $path_prefix ?>market_price.php" class="text-decoration-none hover-link" style="color: inherit;"><i class="fas fa-angle-right me-2"></i>ราคากลางสินค้า</a></li>
                    <li class="mb-2"><a href="<?= $path_prefix ?>login.php" class="text-decoration-none hover-link" style="color: inherit;"><i class="fas fa-angle-right me-2"></i>เข้าสู่ระบบร้านค้า</a></li>
                    <li class="mb-2"><a href="#" class="text-decoration-none hover-link" style="color: inherit;"><i class="fas fa-angle-right me-2"></i>นโยบายความเป็นส่วนตัว</a></li>
                </ul>
            </div>

            <div class="col-lg-4 col-md-12">
                <h5 class="fw-bold mb-3" style="color: <?= $accent_color ?> !important;">ติดต่อโครงการ</h5>
                <ul class="list-unstyled small opacity-75">
                    <!-- <li class="mb-3 d-flex">
                        <i class="fas fa-map-marker-alt mt-1 me-3" style="color: <?= $primary_color ?>;"></i>
                        <span><?= $config['contact']['address'] ?></span>
                    </li> -->
                    <li class="mb-3 d-flex">
                        <i class="fas fa-phone mt-1 me-3" style="color: <?= $primary_color ?>;"></i>
                        <span><?= $config['contact_phone'] ?></span>
                    </li>
                    <li class="mb-3 d-flex">
                        <i class="fas fa-envelope mt-1 me-3" style="color: <?= $primary_color ?>;"></i>
                        <span><?= $config['contact_email'] ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="py-3" style="background-color: rgba(0,0,0,0.2);">
        <div class="container text-center">
            <small class="opacity-50">
                &copy; <?= date('Y') ?> <strong>Phetchaburi Smart City</strong>. All rights reserved. 
                <span class="d-none d-md-inline">| Powered by NIA Style Theme</span>
            </small>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<style>
    .hover-link:hover {
        text-decoration: underline !important;
        opacity: 1 !important;
    }
    .hover-up { transition: transform 0.2s; }
    .hover-up:hover { transform: translateY(-3px); }
</style>

</body>
</html> 