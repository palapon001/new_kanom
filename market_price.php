<?php
session_start();
require_once 'config.php';
require_once 'function.php';

// SQL เทพ: ดึงข้อมูลมาตรฐาน + คำนวณราคาเฉลี่ยจากสินค้าที่ร้านค้าวางขายจริง
// ใช้ LEFT JOIN เพื่อดึง products มาคำนวณ
// ใช้ GROUP BY เพื่อรวมผลลัพธ์ตามชนิดวัตถุดิบ
$sql = "SELECT 
            c.id, 
            c.name, 
            c.unit,
            COUNT(p.id) as shop_count,       /* มีกี่ร้านที่ขายสิ่งนี้ */
            MIN(p.price) as min_price,       /* ราคาต่ำสุดที่เจอ */
            MAX(p.price) as max_price,       /* ราคาสูงสุดที่เจอ */
            AVG(p.price) as avg_price        /* ราคากลาง (เฉลี่ย) */
        FROM central_ingredients c
        LEFT JOIN products p ON c.id = p.central_id AND p.status = 'active'
        GROUP BY c.id
        ORDER BY c.name ASC";

$market_data = select($sql);

$theme = $config['theme'];
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container py-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold text-purple mb-2">
            <i class="fas fa-chart-line me-2"></i> ราคากลางวัตถุดิบ (Real-time)
        </h2>
        <p class="text-muted">
            คำนวณจากสินค้าจริงที่วางขายโดยร้านค้าในระบบ <br>
            <small class="text-danger">*ข้อมูลอัปเดตตามการตั้งราคาของร้านค้า</small>
        </p>
    </div>

    <div class="row g-4">
        <?php foreach ($market_data as $item): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100 rounded-4 hover-up">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($item['name']) ?></h5>
                                <span class="badge bg-light text-muted border">
                                    หน่วย: <?= htmlspecialchars($item['unit']) ?>
                                </span>
                            </div>
                            <div class="bg-purple bg-opacity-10 rounded-circle p-2 text-purple">
                                <i class="fas fa-tag"></i>
                            </div>
                        </div>

                        <?php if ($item['shop_count'] > 0): ?>
                            <div class="text-center py-3 bg-light rounded-3 mb-3 border border-dashed">
                                <small class="text-muted d-block mb-1">ราคากลาง (เฉลี่ย)</small>
                                <h2 class="fw-bold text-purple mb-0">
                                    ฿<?= number_format($item['avg_price'], 2) ?>
                                </h2>
                            </div>

                            <div class="d-flex justify-content-between text-muted small px-2">
                                <div>
                                    <i class="fas fa-arrow-down text-success"></i> ต่ำสุด: 
                                    <span class="fw-bold text-dark">฿<?= number_format($item['min_price'], 2) ?></span>
                                </div>
                                <div>
                                    <i class="fas fa-arrow-up text-danger"></i> สูงสุด: 
                                    <span class="fw-bold text-dark">฿<?= number_format($item['max_price'], 2) ?></span>
                                </div>
                            </div>
                            
                            <div class="mt-3 text-center">
                                <span class="badge bg-success rounded-pill">
                                    <i class="fas fa-store me-1"></i> มีขาย <?= $item['shop_count'] ?> ร้าน
                                </span>
                            </div>

                        <?php else: ?>
                            <div class="text-center py-4 text-muted opacity-50">
                                <i class="fas fa-box-open fa-2x mb-2"></i>
                                <p class="mb-0 small">ยังไม่มีร้านค้าวางขาย</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
    .hover-up { transition: transform 0.3s; }
    .hover-up:hover { transform: translateY(-5px); }
    .border-dashed { border-style: dashed !important; }
</style>

<?php include 'includes/footer.php'; ?>