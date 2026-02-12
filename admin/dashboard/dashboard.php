<?php
require_once "../../config.php";

try {
    // 1. สถิติพื้นฐาน (อิงตามตาราง orders และ products)
    $countProducts = $conn->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $countOrders = $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    
    // ยอดขายรวม (เฉพาะที่สถานะไม่ใช่ cancelled)
    $totalSales = $conn->query("SELECT SUM(total_price) FROM orders WHERE status != 'cancelled'")->fetchColumn() ?: 0;
    $estimatedProfit = $totalSales * 0.3; // กำไรสมมติ 30%

    // 2. ข้อมูลกราฟยอดขาย (รายวัน 7 วันล่าสุด)
    $dailySalesQuery = $conn->query("SELECT DATE(order_date) as day, SUM(total_price) as total 
                                   FROM orders 
                                   WHERE status != 'cancelled'
                                   GROUP BY day 
                                   ORDER BY day ASC LIMIT 7")->fetchAll(PDO::FETCH_ASSOC);
    
    $days = [];
    $totals = [];
    foreach($dailySalesQuery as $row) {
        $days[] = date('d/m', strtotime($row['day']));
        $totals[] = $row['total'];
    }

    // 3. ยอดขายแยกตามเพศ (ชาย/หญิง) - ดึงจากคอลัมน์ sex ในตาราง products
    $sexSales = $conn->query("SELECT p.sex, SUM(oi.price * oi.quantity) as total 
                             FROM order_items oi 
                             JOIN products p ON oi.product_id = p.product_id 
                             GROUP BY p.sex")->fetchAll(PDO::FETCH_ASSOC);
    
    $sexLabels = [];
    $sexData = [];
    foreach($sexSales as $row) {
        $sexLabels[] = ($row['sex'] == 'male') ? 'น้ำหอมผู้ชาย' : (($row['sex'] == 'female') ? 'น้ำหอมผู้หญิง' : 'Unisex');
        $sexData[] = $row['total'];
    }

    // 4. ลูกค้ายอดซื้อสูงสุด (Top 5)
    $topCustomers = $conn->query("SELECT u.fullname, SUM(o.total_price) as spent 
                                 FROM orders o 
                                 JOIN users u ON o.user_id = u.user_id 
                                 WHERE o.status != 'cancelled'
                                 GROUP BY u.user_id 
                                 ORDER BY spent DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

    // 5. สินค้าสต็อกต่ำ (น้อยกว่า 5 ชิ้น)
    $lowStockProducts = $conn->query("SELECT product_name, stock FROM products WHERE stock < 5 ORDER BY stock ASC")->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    $error = $e->getMessage();
}
?>


<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>MIRA Ultimate Dashboard</title>
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@200;400;600&display=swap');

    body { 
        background-color: #fff5f7; /* พื้นหลังชมพูอ่อนมากแบบ Minimal */
        font-family: 'Sarabun', sans-serif; 
        color: #4a4a4a;
    }

    /* ปรับหัวข้อใหญ่ */
    h2.fw-bold {
        color: #b3365b;
        letter-spacing: -1px;
    }

    /* การ์ดสถิติแบบ Minimal */
    .glass-card {
        background: #ffffff;
        border: none;
        border-radius: 25px; /* มนพิเศษแบบในรูป */
        padding: 25px;
        transition: all 0.3s ease;
        height: 100%;
        box-shadow: 0 10px 30px rgba(179, 54, 91, 0.05); /* เงาจางๆ */
    }

    .glass-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(179, 54, 91, 0.1);
    }

    /* ไอคอนและตัวเลข */
    .stat-icon { font-size: 2rem; color: #f8a5c2; margin-bottom: 10px; }
    .text-white-50 { color: #8e8e8e !important; font-weight: 400; }
    h3.fw-bold { color: #b3365b; font-size: 1.8rem; }

    /* ตารางแบบในรูป */
    .table-glass { 
        color: #4a4a4a; 
        border-collapse: separate;
        border-spacing: 0 10px;
    }
    .table-glass thead { 
        background: transparent; 
        color: #b3365b;
        border-bottom: 1px solid #eee;
    }
    .table-glass thead th { font-weight: 600; border: none; }
    .table-glass tbody tr {
        background: #fff;
        transition: 0.2s;
    }
    .table-glass td { padding: 15px; border: none; vertical-align: middle; }

    /* สินค้าสต็อกต่ำ */
    .bg-danger.bg-opacity-25 {
        background-color: #fff0f3 !important;
        border: 1px solid #f8d7da !important;
        color: #d63384;
    }
    .badge.bg-danger {
        background-color: #ff4d7d !important;
        border-radius: 50px;
        padding: 5px 12px;
    }

    /* Scrollbar สีชมพู */
    ::-webkit-scrollbar { width: 8px; }
    ::-webkit-scrollbar-track { background: #f1f1f1; }
    ::-webkit-scrollbar-thumb { background: #f8a5c2; border-radius: 10px; }



    /* เพิ่มสไตล์สำหรับปุ่ม Link กลับ Dashboard */
    .back-link {
        text-decoration: none;
        color: #94a3b8; /* สีเทาตามรูป */
        font-size: 0.95rem;
        transition: 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 15px;
    }
    .back-link:hover {
        color: #b3365b;
    }

</style>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-start mb-0">
        <a href="../index_ad.php" class="back-link">
            <i class="bi bi-arrow-left"></i> กลับสู่หน้า Dashboard
        </a>
    </div>

<div class="container py-1">
    <div class="text-start mb-4">
        <h2 class="fw-bold mb-1">วิเคราะห์ยอดขาย</h2>
        <p class="text-muted">จัดการและดูภาพรวมธุรกิจ Mira ของคุณ</p>
    </div>


    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="glass-card text-center">
                <i class="bi bi-cart-check stat-icon"></i>
                <h6 class="mt-2 text-white-50">ออเดอร์ทั้งหมด</h6>
                <h3 class="fw-bold"><?= number_format($countOrders) ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card text-center">
                <i class="bi bi-box-seam stat-icon"></i>
                <h6 class="mt-2 text-white-50">สินค้าในคลัง</h6>
                <h3 class="fw-bold"><?= number_format($countProducts) ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card text-center">
                <i class="bi bi-currency-bitcoin stat-icon text-success"></i>
                <h6 class="mt-2 text-white-50">ยอดขายรวม</h6>
                <h3 class="fw-bold">฿<?= number_format($totalSales) ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card text-center">
                <i class="bi bi-graph-up-arrow stat-icon text-info"></i>
                <h6 class="mt-2 text-white-50">กำไรโดยประมาณ (30%)</h6>
                <h3 class="fw-bold">฿<?= number_format($estimatedProfit) ?></h3>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-8">
            <div class="glass-card">
                <h5 class="fw-bold mb-4"><i class="bi bi-bar-chart-line me-2"></i>แนวโน้มยอดขาย (7 วันล่าสุด)</h5>
                <canvas id="salesChart" style="max-height: 300px;"></canvas>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card">
                <h5 class="fw-bold mb-4 text-center">สัดส่วนน้ำหอม</h5>
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="glass-card">
                <h5 class="fw-bold mb-3"><i class="bi bi-person-heart me-2"></i>Top Customers</h5>
                <table class="table table-borderless table-glass">
                    <thead><tr><th>ชื่อลูกค้า</th><th class="text-end">ยอดซื้อรวม</th></tr></thead>
                    <tbody>
                        <?php foreach($topCustomers as $cus): ?>
                        <tr>
                            <td><?= $cus['fullname'] ?></td>
                            <td class="text-end fw-bold text-info">฿<?= number_format($cus['spent']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-md-6">
    <div class="glass-card">
        <h5 class="fw-bold mb-3"><i class="bi bi-exclamation-triangle me-2"></i>สินค้าสต็อกต่ำ</h5>
        <?php if (empty($lowStockProducts)): ?>
            <p class="text-muted text-center py-3">ไม่มีสินค้าใกล้หมดสต็อก</p>
        <?php else: ?>
            <?php foreach($lowStockProducts as $low): ?>
                <div class="p-3 mb-2 rounded bg-danger bg-opacity-25 border border-danger">
                    <div class="d-flex justify-content-between">
                        <span><?= htmlspecialchars($low['product_name']) ?></span>
                        <span class="badge bg-danger">เหลือ <?= $low['stock'] ?> ชิ้น</span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
// กราฟเส้นยอดขาย (ดึงข้อมูลจริงจาก $days และ $totals)
const ctxSales = document.getElementById('salesChart').getContext('2d');
new Chart(ctxSales, {
    type: 'line',
    data: {
        labels: <?= json_encode($days) ?>, 
        datasets: [{
            label: 'ยอดขายรายวัน (฿)',
            data: <?= json_encode($totals) ?>,
            borderColor: '#f8a5c2',
            backgroundColor: 'rgba(248, 165, 194, 0.2)',
            fill: true,
            tension: 0.4
        }]
    },
    options: { 
        plugins: { legend: { display: false } }, 
        scales: { 
            y: { beginAtZero: true, ticks: { color: '#8e8e8e' } }, 
            x: { ticks: { color: '#8e8e8e' } } 
        } 
    }
});

// กราฟวงกลมแยกเพศ (ดึงข้อมูลจริงจาก $sexLabels และ $sexData)
const ctxCat = document.getElementById('categoryChart').getContext('2d');
new Chart(ctxCat, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($sexLabels) ?>,
        datasets: [{
            data: <?= json_encode($sexData) ?>,
            backgroundColor: ['#74b9ff', '#f8a5c2', '#a29bfe'],
            borderWidth: 0
        }]
    },
    options: { 
        plugins: { 
            legend: { position: 'bottom', labels: { color: '#4a4a4a', usePointStyle: true } } 
        } 
    }
});
</script>

</body>
</html>