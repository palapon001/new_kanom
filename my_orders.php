<?php
session_start();
require_once 'config.php';
require_once 'function.php';

// 1. ตรวจสอบสิทธิ์ (ต้อง Login)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$theme = $config['theme'];

// 2. ดึงข้อมูลออเดอร์ของฉัน (เรียงจากล่าสุดไปเก่าสุด)
// JOIN กับ users (shop) เพื่อเอาชื่อร้านค้ามาแสดง
$sql = "SELECT o.*, s.shop_name, s.phone as shop_phone 
        FROM orders o
        JOIN users s ON o.shop_id = s.id
        WHERE o.customer_id = ?
        ORDER BY o.created_at DESC";

$orders = select($sql, [$user_id]);

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container py-5">
    
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h2 class="fw-bold text-purple mb-0">
                <i class="fas fa-history me-2"></i> ประวัติการสั่งซื้อ
            </h2>
            <p class="text-muted mb-0">รายการคำสั่งซื้อทั้งหมดของคุณ</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <a href="index.php" class="btn btn-outline-secondary rounded-pill">
                <i class="fas fa-shopping-bag me-1"></i> ไปเลือกซื้อสินค้าต่อ
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center">
            <i class="fas fa-check-circle fa-2x me-3 text-success"></i>
            <div>
                <h5 class="fw-bold mb-0">ทำรายการสำเร็จ!</h5>
                <p class="mb-0"><?= $_SESSION['success']; ?></p>
            </div>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
        <div class="text-center py-5 bg-white shadow-sm rounded-4">
            <div class="mb-3">
                <div class="bg-light rounded-circle d-inline-flex p-4">
                    <i class="fas fa-clipboard-list fa-4x text-muted opacity-25"></i>
                </div>
            </div>
            <h4 class="fw-bold text-muted">คุณยังไม่มีรายการคำสั่งซื้อ</h4>
            <p class="text-muted mb-4">ลองเลือกขนมอร่อยๆ สักชิ้นไหม?</p>
            <a href="index.php" class="btn btn-nia btn-lg rounded-pill px-5 shadow-sm">
                <i class="fas fa-store me-2"></i> ไปหน้าแรก
            </a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($orders as $order): ?>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden hover-card">
                        
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-light text-dark border fw-bold me-2">
                                    #<?= htmlspecialchars($order['order_no']) ?>
                                </span>
                                <small class="text-muted">
                                    <i class="far fa-clock me-1"></i> <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                                </small>
                            </div>
                            
                            <?php 
                                $status_info = [
                                    'pending'   => ['class' => 'warning text-dark', 'label' => '⏳ รอตรวจสอบ'],
                                    'paid'      => ['class' => 'info text-dark',    'label' => '💰 ชำระแล้ว'],
                                    'shipped'   => ['class' => 'primary',           'label' => '🚚 จัดส่งแล้ว'],
                                    'completed' => ['class' => 'success',           'label' => '✅ สำเร็จ'],
                                    'cancelled' => ['class' => 'danger',            'label' => '❌ ยกเลิก']
                                ];
                                $st = $status_info[$order['status']] ?? ['class' => 'secondary', 'label' => $order['status']];
                            ?>
                            <span class="badge bg-<?= $st['class'] ?> rounded-pill px-3">
                                <?= $st['label'] ?>
                            </span>
                        </div>
                        
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-4">
                                <div class="bg-purple bg-opacity-10 rounded-circle p-3 me-3">
                                    <i class="fas fa-store fa-lg text-purple"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.7rem;">ร้านค้า</small>
                                    <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($order['shop_name']) ?></h6>
                                </div>
                            </div>

                            <hr class="border-dashed my-3">

                            <div class="d-flex justify-content-between align-items-end">
                                <div>
                                    <small class="text-muted d-block">ยอดสุทธิ</small>
                                    <h4 class="fw-bold text-magenta mb-0">฿<?= number_format($order['total_amount'], 2) ?></h4>
                                </div>
                                
                                <a href="my_order_detail.php?id=<?= $order['id'] ?>" class="btn btn-outline-nia rounded-pill px-4 fw-bold shadow-sm">
                                    รายละเอียด <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                            
                            <?php if(!empty($order['tracking_no'])): ?>
                                <div class="mt-3 bg-light p-2 rounded text-center small text-purple fw-bold border border-purple border-opacity-25">
                                    <i class="fas fa-box me-1"></i> เลขพัสดุ: <?= htmlspecialchars($order['tracking_no']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<style>
    .hover-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .hover-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    .border-dashed {
        border-style: dashed !important;
        border-color: #e0e0e0;
    }
    .btn-outline-nia {
        color: var(--nia-magenta);
        border-color: var(--nia-magenta);
    }
    .btn-outline-nia:hover {
        background-color: var(--nia-magenta);
        color: white;
    }
</style>

<?php include 'includes/footer.php'; ?>