<?php
require_once "../../config.php";

try {
    // 1. สถิติพื้นฐาน
    $countProducts = $conn->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $countOrders = $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn(); // สมมติว่ามีตาราง orders
    $totalSales = $conn->query("SELECT SUM(total_price) FROM orders WHERE status = 'completed'")->fetchColumn() ?: 0;
    $estimatedProfit = $totalSales * 0.3; // สมมติกำไร 30%

    // 2. ข้อมูลกราฟยอดขาย (รายวัน 7 วันล่าสุด)
    $dailySales = $conn->query("SELECT DATE(order_date) as day, SUM(total_price) as total 
                               FROM orders GROUP BY day ORDER BY day DESC LIMIT 7")->fetchAll(PDO::FETCH_ASSOC);

    // 3. ยอดขายแยกตามประเภท (ชาย/หญิง) - สมมติว่าใน products มีคอลัมน์ category
    $catSales = $conn->query("SELECT p.category, SUM(o.total_price) as total 
                             FROM order_details o JOIN products p ON o.product_id = p.product_id 
                             GROUP BY p.category")->fetchAll(PDO::FETCH_ASSOC);

    // 4. ลูกค้ายอดซื้อสูงสุด
    $topCustomers = $conn->query("SELECT u.fullname, SUM(o.total_price) as spent 
                                 FROM orders o JOIN users u ON o.user_id = u.user_id 
                                 GROUP BY u.user_id ORDER BY spent DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

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
        body { 
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('../../admin/photo_ad/ro.jpg');
            background-size: cover; background-attachment: fixed;
            font-family: 'Sarabun', sans-serif; color: white;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 20px;
            padding: 20px; transition: 0.3s; height: 100%;
        }
        .stat-icon { font-size: 2.5rem; color: #f8a5c2; }
        .table-glass { color: white; }
        .table-glass thead { background: rgba(248, 165, 194, 0.2); }
    </style>
</head>
<body>

<div class="container py-5">
    <h2 class="fw-bold mb-4 text-center">MIRA ADMIN INSIGHTS</h2>

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
                <div class="p-3 mb-2 rounded bg-danger bg-opacity-25 border border-danger">
                    <div class="d-flex justify-content-between">
                        <span>น้ำหอม มายควีน (8 มล.)</span>
                        <span class="badge bg-danger">เหลือ 2 ชิ้น</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// กราฟเส้นยอดขาย
const ctxSales = document.getElementById('salesChart').getContext('2d');
new Chart(ctxSales, {
    type: 'line',
    data: {
        labels: ['จ.', 'อ.', 'พ.', 'พฤ.', 'ศ.', 'ส.', 'อา.'],
        datasets: [{
            label: 'ยอดขายรายวัน',
            data: [1200, 1900, 3000, 5000, 2400, 3300, 4500], // ข้อมูลสมมติ
            borderColor: '#f8a5c2',
            backgroundColor: 'rgba(248, 165, 194, 0.2)',
            fill: true,
            tension: 0.4
        }]
    },
    options: { plugins: { legend: { labels: { color: 'white' } } }, scales: { y: { ticks: { color: 'white' } }, x: { ticks: { color: 'white' } } } }
});

// กราฟวงกลมแยกประเภท
const ctxCat = document.getElementById('categoryChart').getContext('2d');
new Chart(ctxCat, {
    type: 'doughnut',
    data: {
        labels: ['น้ำหอมผู้ชาย', 'น้ำหอมผู้หญิง'],
        datasets: [{
            data: [45, 55],
            backgroundColor: ['#74b9ff', '#f8a5c2'],
            borderWidth: 0
        }]
    },
    options: { plugins: { legend: { position: 'bottom', labels: { color: 'white' } } } }
});
</script>

</body>
</html>