<footer class="text-white mt-auto position-relative" 
        style="background-color: <?= $config['theme']['colors']['secondary'] ?>; border-top: 5px solid <?= $config['theme']['colors']['primary'] ?>;">

    <div style="position: absolute; top: 0; right: 0; width: 200px; height: 100%; opacity: 0.05; background: url('https://www.transparenttextures.com/patterns/cubes.png'); pointer-events: none;"></div>

    <div class="container py-5 position-relative z-1">
        <div class="row g-4">
            
            <div class="col-lg-4 col-md-6">
                <a href="#" class="d-flex align-items-center mb-3 text-decoration-none text-white">
                    <div class="bg-white text-purple rounded-circle d-flex justify-content-center align-items-center me-2" style="width: 35px; height: 35px;">
                        <i class="fas fa-crown fa-sm" style="color: <?= $config['theme']['colors']['primary'] ?>;"></i>
                    </div>
                    <span class="fw-bold text-uppercase ls-1">KanomMuangPhet</span>
                </a>
                <p class="small text-white-50 mb-3" style="line-height: 1.6;">
                    <?= $config['app']['desc'] ?><br>
                    แพลตฟอร์มที่เชื่อมโยงภูมิปัญญาท้องถิ่นสู่นวัตกรรมสากล สนับสนุนโดยเครือข่ายเมืองนวัตกรรมอาหาร
                </p>
                <div class="d-flex gap-2">
                    <a href="#" class="btn btn-outline-light btn-sm rounded-circle" style="width: 35px; height: 35px;"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="btn btn-outline-light btn-sm rounded-circle" style="width: 35px; height: 35px;"><i class="fab fa-line"></i></a>
                    <a href="#" class="btn btn-outline-light btn-sm rounded-circle" style="width: 35px; height: 35px;"><i class="fas fa-globe"></i></a>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 ps-lg-5">
                <h5 class="fw-bold text-white mb-3" style="color: <?= $config['theme']['colors']['accent'] ?> !important;">เมนูด่วน</h5>
                <ul class="list-unstyled small text-white-50">
                    <li class="mb-2"><a href="<?= $path_prefix ?>index.php" class="text-white-50 text-decoration-none hover-white"><i class="fas fa-angle-right me-2"></i>หน้าแรก</a></li>
                    <li class="mb-2"><a href="<?= $path_prefix ?>market_price.php" class="text-white-50 text-decoration-none hover-white"><i class="fas fa-angle-right me-2"></i>ราคากลาง</a></li>
                    <li class="mb-2"><a href="<?= $path_prefix ?>login.php" class="text-white-50 text-decoration-none hover-white"><i class="fas fa-angle-right me-2"></i>สำหรับผู้ประกอบการ</a></li>
                    <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none hover-white"><i class="fas fa-angle-right me-2"></i>นโยบายความเป็นส่วนตัว</a></li>
                </ul>
            </div>

            <div class="col-lg-4 col-md-12">
                <h5 class="fw-bold text-white mb-3" style="color: <?= $config['theme']['colors']['accent'] ?> !important;">ติดต่อโครงการ</h5>
                <ul class="list-unstyled small text-white-50">
                    <li class="mb-3 d-flex">
                        <i class="fas fa-map-marker-alt mt-1 me-3 text-magenta"></i>
                        <span><?= $config['contact']['address'] ?></span>
                    </li>
                    <li class="mb-3 d-flex">
                        <i class="fas fa-phone mt-1 me-3 text-magenta"></i>
                        <span><?= $config['contact']['phone'] ?></span>
                    </li>
                    <li class="mb-3 d-flex">
                        <i class="fas fa-envelope mt-1 me-3 text-magenta"></i>
                        <span><?= $config['contact']['email'] ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="py-3 bg-black bg-opacity-25">
        <div class="container text-center">
            <small class="text-white-50">
                &copy; <?= date('Y') ?> <strong>Phetchaburi Smart City</strong>. All rights reserved. 
                <span class="d-none d-md-inline">| Powered by NIA Style Theme</span>
            </small>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>