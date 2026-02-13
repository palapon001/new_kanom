<?php
session_start();
require_once '../config.php';
require_once '../function.php';

// 1. ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// ... (ส่วนดึงข้อมูล PHP เหมือนเดิม ไม่ต้องแก้) ...
// ---------------------------------------------
$sql_chart = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month_year, 
                     SUM(total_amount) as total 
              FROM orders 
              WHERE status = 'completed' 
              GROUP BY month_year 
              ORDER BY month_year ASC 
              LIMIT 6";
$chart_data = select($sql_chart);

$labels = [];
$sales = [];
$thai_months = [
    '01'=>'ม.ค.', '02'=>'ก.พ.', '03'=>'มี.ค.', '04'=>'เม.ย.', '05'=>'พ.ค.', '06'=>'มิ.ย.',
    '07'=>'ก.ค.', '08'=>'ส.ค.', '09'=>'ก.ย.', '10'=>'ต.ค.', '11'=>'พ.ย.', '12'=>'ธ.ค.'
];

foreach ($chart_data as $d) {
    $parts = explode('-', $d['month_year']); 
    $m_key = $parts[1];
    $labels[] = $thai_months[$m_key] . ' ' . ($parts[0] + 543); 
    $sales[] = $d['total'];
}

$sql_top_shops = "SELECT u.shop_name, u.profile_image, COUNT(o.id) as order_count, SUM(o.total_amount) as total_revenue
                  FROM orders o JOIN users u ON o.shop_id = u.id
                  WHERE o.status = 'completed' GROUP BY o.shop_id ORDER BY total_revenue DESC LIMIT 5";
$top_shops = select($sql_top_shops);

$sql_top_products = "SELECT p.name, p.image, SUM(oi.quantity) as qty_sold, SUM(oi.subtotal) as total_earned
                     FROM order_items oi JOIN products p ON oi.product_id = p.id JOIN orders o ON oi.order_id = o.id
                     WHERE o.status = 'completed' GROUP BY oi.product_id ORDER BY qty_sold DESC LIMIT 5";
$top_products = select($sql_top_products);

$theme = $config['theme'];
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-purple mb-0"><i class="fas fa-chart-line me-2"></i>รายงานสรุปยอดขาย</h3>
            <p class="text-muted small mb-0">วิเคราะห์ข้อมูลการขายของระบบ</p>
        </div>
        
        <div class="dropdown">
            <button class="btn btn-nia dropdown-toggle fw-bold shadow-sm" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-download me-2"></i> Export Report
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                <li><h6 class="dropdown-header">รูปภาพ (Image)</h6></li>
                <li><button class="dropdown-item" onclick="exportImage('png')"><i class="far fa-image me-2 text-primary"></i>Save as PNG</button></li>
                <li><button class="dropdown-item" onclick="exportImage('jpeg')"><i class="far fa-image me-2 text-warning"></i>Save as JPG</button></li>
                <li><hr class="dropdown-divider"></li>
                <li><h6 class="dropdown-header">เอกสาร (Document)</h6></li>
                <li><button class="dropdown-item" onclick="exportPDF()"><i class="far fa-file-pdf me-2 text-danger"></i>Save as PDF</button></li>
                <li><button class="dropdown-item" onclick="exportExcel()"><i class="far fa-file-excel me-2 text-success"></i>Save as Excel</button></li>
            </ul>
        </div>
    </div>

    <div id="report-content" class="bg-white p-4 rounded-4">
        
        <div class="text-center mb-4 d-none d-print-block">
            <h3 class="fw-bold">รายงานสรุปยอดขาย (Sales Report)</h3>
            <p>วันที่พิมพ์: <?= date('d/m/Y H:i') ?></p>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold text-dark mb-0">ยอดขายรวม 6 เดือนย้อนหลัง</h6>
            </div>
            <div class="card-body">
                <div style="height: 400px; width: 100%;">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-purple text-white py-3">
                        <h6 class="fw-bold mb-0"><i class="fas fa-trophy me-2 text-warning"></i>ร้านค้าทำเงินสูงสุด</h6>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0" id="table-shops">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">ร้านค้า</th>
                                    <th class="text-center">ออเดอร์</th>
                                    <th class="text-end pe-4">ยอดขาย</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($top_shops as $index => $s): ?>
                                <tr>
                                    <td class="ps-4">
                                        <span class="badge bg-light text-dark border me-2">#<?= $index+1 ?></span>
                                        <span class="fw-bold text-dark"><?= htmlspecialchars($s['shop_name']) ?></span>
                                    </td>
                                    <td class="text-center"><?= number_format($s['order_count']) ?></td>
                                    <td class="text-end pe-4 fw-bold text-success">฿<?= number_format($s['total_revenue']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white py-3">
                        <h6 class="fw-bold text-dark mb-0"><i class="fas fa-box-open me-2 text-purple"></i>สินค้าขายดี</h6>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0" id="table-products">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">สินค้า</th>
                                    <th class="text-center">ขายได้ (ชิ้น)</th>
                                    <th class="text-end pe-4">รวมเป็นเงิน</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($top_products as $index => $p): ?>
                                <tr>
                                    <td class="ps-4">
                                        <span class="badge bg-light text-dark border me-2">#<?= $index+1 ?></span>
                                        <span class="text-dark small"><?= htmlspecialchars($p['name']) ?></span>
                                    </td>
                                    <td class="text-center fw-bold"><?= number_format($p['qty_sold']) ?></td>
                                    <td class="text-end pe-4 text-muted small">฿<?= number_format($p['total_earned']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div> </div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
    // 1. Setup Chart
    const ctx = document.getElementById('salesChart').getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(111, 66, 193, 0.5)');
    gradient.addColorStop(1, 'rgba(111, 66, 193, 0.0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [{
                label: 'ยอดขายรวม (บาท)',
                data: <?= json_encode($sales) ?>,
                borderColor: '#6f42c1',
                backgroundColor: gradient,
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true } }
        }
    });

    // ------------------------------------------------
    // 📸 ฟังก์ชัน Export: Image (PNG / JPG)
    // ------------------------------------------------
    function exportImage(type) {
        const element = document.getElementById('report-content');
        
        html2canvas(element, { scale: 2, backgroundColor: '#ffffff' }).then(canvas => {
            const link = document.createElement('a');
            link.download = 'sales-report.' + type;
            link.href = canvas.toDataURL('image/' + type);
            link.click();
        });
    }

    // ------------------------------------------------
    // 📄 ฟังก์ชัน Export: PDF
    // ------------------------------------------------
    function exportPDF() {
        const element = document.getElementById('report-content');
        const opt = {
            margin:       0.5,
            filename:     'sales-report.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2 },
            jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
        };
        html2pdf().set(opt).from(element).save();
    }

    // ------------------------------------------------
    // 📊 ฟังก์ชัน Export: Excel
    // ------------------------------------------------
    function exportExcel() {
        // สร้าง Workbook ใหม่
        const wb = XLSX.utils.book_new();

        // ดึงตาราง 1: ร้านค้าขายดี
        const table1 = document.getElementById('table-shops');
        const ws1 = XLSX.utils.table_to_sheet(table1);
        XLSX.utils.book_append_sheet(wb, ws1, "Top Shops");

        // ดึงตาราง 2: สินค้าขายดี
        const table2 = document.getElementById('table-products');
        const ws2 = XLSX.utils.table_to_sheet(table2);
        XLSX.utils.book_append_sheet(wb, ws2, "Top Products");

        // บันทึกไฟล์
        XLSX.writeFile(wb, 'sales-report-data.xlsx');
    }
</script>

<?php include '../includes/footer.php'; ?>