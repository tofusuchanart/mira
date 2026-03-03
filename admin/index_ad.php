<?php
require_once "../config.php";


try {
    // --- LOGIC จากหน้า วิเคราะห์ยอดขาย ---
    $countProducts = $conn->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $countOrders = $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $totalSales = $conn->query("SELECT SUM(total_price) FROM orders WHERE status != 'cancelled'")->fetchColumn() ?: 0;
    $estimatedProfit = $totalSales * 0.3;

    $dailySalesQuery = $conn->query("SELECT DATE(order_date) as day, SUM(total_price) as total 
                                   FROM orders 
                                   WHERE status != 'cancelled'
                                   GROUP BY day 
                                   ORDER BY day ASC LIMIT 7")->fetchAll(PDO::FETCH_ASSOC);
    $days = []; $totals = [];
    foreach($dailySalesQuery as $row) {
        $days[] = date('d/m', strtotime($row['day']));
        $totals[] = $row['total'];
    }

    $sexSales = $conn->query("SELECT p.sex, SUM(oi.price * oi.quantity) as total 
                             FROM order_items oi 
                             JOIN products p ON oi.product_id = p.product_id 
                             GROUP BY p.sex")->fetchAll(PDO::FETCH_ASSOC);
    $sexLabels = []; $sexData = [];
    foreach($sexSales as $row) {
        $sexLabels[] = ($row['sex'] == 'male') ? 'น้ำหอมผู้ชาย' : (($row['sex'] == 'female') ? 'น้ำหอมผู้หญิง' : 'Unisex');
        $sexData[] = $row['total'];
    }

    $topCustomers = $conn->query("SELECT u.fullname, SUM(o.total_price) as spent 
                                 FROM orders o 
                                 JOIN users u ON o.user_id = u.user_id 
                                 WHERE o.status != 'cancelled'
                                 GROUP BY u.user_id 
                                 ORDER BY spent DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

    $lowStockProducts = $conn->query("SELECT product_name, stock FROM products WHERE stock < 5 ORDER BY stock ASC")->fetchAll(PDO::FETCH_ASSOC);

    // --- LOGIC จากหน้า Control Center (นับจำนวนเพิ่มเติม) ---
    $countReviews = $conn->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
    $countUsers = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $countPayments = $conn->query("SELECT COUNT(*) FROM payments")->fetchColumn();
    $countMessages = $conn->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();
    // ดึงจำนวนโปรโมชั่นที่ยังไม่หมดอายุมาโชว์ที่ปุ่ม
$countPromos = $conn->query("SELECT COUNT(*) FROM promotions WHERE end_date >= NOW() AND status = 'active'")->fetchColumn() ?: 0;

} catch(PDOException $e) {
    $error = $e->getMessage();
}


?>

<?php
// แก้ไขส่วนดึงข้อมูลให้รองรับ PDO
// 1. จัดการสต็อก
$lowStockQuery = $conn->query("SELECT COUNT(*) as total FROM products WHERE stock < 10");
$lowStockCount = $lowStockQuery->fetch(PDO::FETCH_ASSOC)['total'];

// 2. รายการสั่งซื้อ (นับ pending และ paid)
$newOrdersQuery = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status IN ('pending', 'paid')");
$newOrdersCount = $newOrdersQuery->fetch(PDO::FETCH_ASSOC)['total'];

// 3. ข้อความติดต่อ (นับที่ยังไม่ตอบ)
$newMsgQuery = $conn->query("SELECT COUNT(*) as total FROM contact_messages WHERE admin_reply IS NULL");
$newMsgCount = $newMsgQuery->fetch(PDO::FETCH_ASSOC)['total'];

// 4. รีวิวลูกค้า (นับของวันนี้)
$today = date('Y-m-d');
$newReviewQuery = $conn->query("SELECT COUNT(*) as total FROM reviews WHERE DATE(review_date) = '$today'");
$newReviewCount = $newReviewQuery->fetch(PDO::FETCH_ASSOC)['total'];

// 5. จัดการสมาชิก: นับจำนวนสมาชิกทั้งหมดที่มีบทบาทเป็นลูกค้า
$userQuery = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'customer'");
$countUsers = $userQuery->fetch(PDO::FETCH_ASSOC)['total'];
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIRA Ultimate Admin Panel</title>
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --mira-pink: #b3365b;
            --mira-soft-pink: #f8a5c2;
            --mira-bg: #fff5f7;
            --mira-card-shadow: 0 10px 30px rgba(179, 54, 91, 0.05);
        }

        body { 
            font-family: 'Sarabun', sans-serif; 
            background-color: var(--mira-bg);
            background-image: linear-gradient(rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0.8)), url('../admin/photo_ad/ro.jpg');
            background-size: cover;
            background-attachment: fixed;
            color: #4a4a4a;
            overflow-x: hidden;
        }

        /* Layout Structure */
        .main-wrapper { display: flex; min-height: 100vh; }
        .content-area { flex: 1; padding: 40px; }
        .right-sidebar { 
            width: 320px; 
            background: rgba(255, 255, 255, 0.9); 
            backdrop-filter: blur(10px);
            border-left: 1px solid rgba(179, 54, 91, 0.1);
            padding: 30px 20px;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }

       /* ปรับขนาด Glass Card ให้เล็กลงและดูง่ายขึ้น */
.glass-card {
    background: rgba(255, 255, 255, 0.9); /* เพิ่มความใสเล็กน้อย */
    border: none;
    border-radius: 20px; /* ลดความมนลงนิดหน่อยให้ดูไม่เทอะทะ */
    padding: 20px; /* ลด Padding จาก 25px เหลือ 15px */
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(179, 54, 91, 0.04);
    margin-bottom: 15px;
}

/* ปรับหัวข้อใน Card ให้เล็กลงเพื่อให้สมดุลกับขนาดการ์ด */
.glass-card h6 {
    font-size: 0.9rem;
    margin-bottom: 12px !important;
    color: var(--mira-pink);
}

/* ปรับขนาด Canvas ของกราฟวงกลมให้เล็กลงแบบพอดี */
#categoryChart {
    max-height: 300px !important; /* จำกัดความสูงของกราฟ */
    margin: 0 auto;
}


        .glass-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(179, 54, 91, 0.1); }

        

        /* Typography */
        h2.fw-bold { color: var(--mira-pink); letter-spacing: -1px; }
        .stat-icon { font-size: 1.8rem; color: var(--mira-soft-pink); }
        .stat-label { color: #8e8e8e; font-size: 0.9rem; }
        .stat-number { color: var(--mira-pink); font-weight: 700; font-size: 1.5rem; }

        /* Sidebar Menu Items */
        .menu-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-radius: 15px;
            background: white;
            margin-bottom: 12px;
            text-decoration: none;
            color: #555;
            transition: 0.3s;
            border: 1px solid transparent;
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        }
        .menu-item:hover {
            border-color: var(--mira-soft-pink);
            background: var(--mira-bg);
            color: var(--mira-pink);
            transform: translateX(-5px);
        }
        .menu-icon {
            width: 40px; height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-size: 1.2rem;
        }

        /* Specific Menu Colors */
        .bg-p { background: #fee2e2; color: #ef4444; }
        .bg-r { background: #fef3c7; color: #f59e0b; }
        .bg-m { background: #d1fae5; color: #10b981; }
        .bg-u { background: #e0e7ff; color: #6366f1; }
        .bg-msg { background: #f3e5f5; color: #9c27b0; }
        .bg-ord { background: #e0f2f1; color: #009688; }

        .table-glass td { padding: 12px; vertical-align: middle; font-size: 0.95rem; }
        .badge-low { background: #ff4d7d; border-radius: 50px; font-weight: 400; }
        
        .logout-btn {
            border-radius: 50px;
            padding: 10px;
            width: 100%;
            margin-top: 20px;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="main-wrapper">
    <div class="content-area">
        <div class="mb-4">
            <h2 class="fw-bold mb-1">MIRA Dashboard</h2>
            <p class="text-muted">วิเคราะห์ภาพรวมธุรกิจและสถิติการขาย</p>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="glass-card text-center p-3">
                    <div class="stat-label">ยอดขายรวม</div>
                    <div class="stat-number">฿<?= number_format($totalSales) ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-card text-center p-3">
                    <div class="stat-label">กำไรโดยประมาณ</div>
                    <div class="stat-number">฿<?= number_format($estimatedProfit) ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-card text-center p-3">
                    <div class="stat-label">ออเดอร์สำเร็จ</div>
                    <div class="stat-number"><?= number_format($countOrders) ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-card text-center p-3">
                    <div class="stat-label">สินค้าทั้งหมด</div>
                    <div class="stat-number"><?= number_format($countProducts) ?></div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-8">
                <div class="glass-card">
                    <h6 class="fw-bold mb-4"><i class="bi bi-graph-up me-2"></i>แนวโน้มยอดขาย (7 วันล่าสุด)</h6>
                    <canvas id="salesChart" style="max-height: 280px;"></canvas>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card">
                    <h6 class="fw-bold mb-4 text-center">สัดส่วนตามเพศ</h6>
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="glass-card">
                    <h6 class="fw-bold mb-3"><i class="bi bi-star-fill me-2 text-warning"></i>ลูกค้าชั้นดี (Top 5)</h6>
                    <table class="table table-borderless">
                        <?php foreach($topCustomers as $cus): ?>
                        <tr>
                            <td><?= $cus['fullname'] ?></td>
                            <td class="text-end fw-bold text-info">฿<?= number_format($cus['spent']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
            <div class="col-md-6">
                <div class="glass-card">
                    <h6 class="fw-bold mb-3"><i class="bi bi-exclamation-circle me-2 text-danger"></i>สินค้าใกล้หมด</h6>
                    <?php foreach($lowStockProducts as $low): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 rounded bg-light">
                            <span class="small"><?= $low['product_name'] ?></span>
                            <span class="badge badge-low">เหลือ <?= $low['stock'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="right-sidebar">
        <div class="text-center mb-4">
            <h5 class="fw-bold" style="color: var(--mira-pink);">CONTROL CENTER</h5>
            <p class="small text-muted">ระบบจัดการหลังบ้าน</p>
        </div>

        <a href="orders/manage_products.php" class="menu-item">
    <div class="menu-icon bg-p"><i class="bi bi-box-seam"></i></div>
    <div class="flex-grow-1">
        <div class="fw-bold small">จัดการสินค้า</div>
        <div class="text-muted" style="font-size: 0.75rem;"><?= $countProducts ?> รายการ</div>
    </div>
    <i class="bi bi-chevron-right small opacity-50"></i>
</a>

<a href="orderss/manage_orders.php" class="menu-item">
    <div class="menu-icon bg-ord"><i class="bi bi-cart-check"></i></div>
    <div class="flex-grow-1">
        <div class="fw-bold small">รายการสั่งซื้อ</div>
        <div class="text-muted" style="font-size: 0.75rem;"><?= $countOrders ?> ออเดอร์ทั้งหมด</div>
    </div>
    <?php if ($newOrdersCount > 0): ?>
        <div class="text-end me-2">
            <span class="badge rounded-pill bg-primary" style="font-size: 0.65rem;">
                +<?= $newOrdersCount ?> ใหม่
            </span>
        </div>
    <?php endif; ?>
    <i class="bi bi-chevron-right small opacity-50"></i>
</a>

<a href="stock/manage_stock.php" class="menu-item">
    <div class="menu-icon" style="background-color: #fff7ed; color: #f97316;">
        <i class="bi bi-boxes"></i>
    </div>
    <div class="flex-grow-1">
        <div class="fw-bold small">จัดการ Stock สินค้า</div>
        <div class="text-muted" style="font-size: 0.75rem;">เช็คจำนวนคงเหลือ/เติมสินค้า</div>
    </div>
    <?php if ($lowStockCount > 0): ?>
        <div class="text-end me-2">
            <span class="badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                <?= $lowStockCount ?> วิกฤต
            </span>
        </div>
    <?php endif; ?>
    <i class="bi bi-chevron-right small opacity-50"></i>
</a>
<a href="promotion/manage_promotions.php" class="menu-item">
    <div class="menu-icon" style="background-color: #fff1f2; color: #e11d48;">
        <i class="bi bi-lightning-charge-fill"></i>
    </div>
    <div class="flex-grow-1">
        <div class="fw-bold small">โปรโมชั่น & Flash Sale</div>
        <div class="text-muted" style="font-size: 0.75rem;">ตั้งค่าส่วนลด / กำหนดเวลา</div>
    </div>
    <?php if ($countPromos > 0): ?>
        <div class="text-end me-2">
            <span class="badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                <?= $countPromos ?> กำลังรัน
            </span>
        </div>
    <?php endif; ?>
    <i class="bi bi-chevron-right small opacity-50"></i>
</a>

<a href="member/member.php" class="menu-item">
    <div class="menu-icon bg-u"><i class="bi bi-people"></i></div>
    <div class="flex-grow-1">
        <div class="fw-bold small">สมาชิกทั้งหมด</div>
        <div class="text-muted" style="font-size: 0.75rem;"><?= $countUsers ?> คน</div>
    </div>
    <i class="bi bi-chevron-right small opacity-50"></i>
</a>

<a href="review/review.php" class="menu-item">
    <div class="menu-icon bg-r"><i class="bi bi-chat-left-heart"></i></div>
    <div class="flex-grow-1">
        <div class="fw-bold small">รีวิวจากลูกค้า</div>
        <div class="text-muted" style="font-size: 0.75rem;"><?= $countReviews ?> รีวิว</div>
    </div>
    <?php if ($newReviewCount > 0): ?>
        <div class="text-end me-2">
            <span class="badge rounded-pill bg-info text-white" style="font-size: 0.65rem;">
                +<?= $newReviewCount ?> วันนี้
            </span>
        </div>
    <?php endif; ?>
    <i class="bi bi-chevron-right small opacity-50"></i>
</a>

<a href="contact/admin_chat.php" class="menu-item">
    <div class="menu-icon bg-msg"><i class="bi bi-envelope-paper"></i></div>
    <div class="flex-grow-1">
        <div class="fw-bold small">ข้อความติดต่อ</div>
        <div class="text-muted" style="font-size: 0.75rem;"><?= $countMessages ?> ข้อความ</div>
    </div>
    <?php if ($newMsgCount > 0): ?>
        <div class="text-end me-2">
            <span class="badge rounded-pill bg-warning text-dark" style="font-size: 0.65rem;">
                รอดำเนินการ <?= $newMsgCount ?>
            </span>
        </div>
    <?php endif; ?>
    <i class="bi bi-chevron-right small opacity-50"></i>
</a>
        <hr class="my-4 opacity-10">
        
        <a href="../login/logout.php" class="btn btn-outline-danger logout-btn btn-sm">
            <i class="bi bi-box-arrow-right me-2"></i> ออกจากระบบ
        </a>
    </div>
</div>

<script>
// Sales Trend Chart
const ctxSales = document.getElementById('salesChart').getContext('2d');
new Chart(ctxSales, {
    type: 'line',
    data: {
        labels: <?= json_encode($days) ?>, 
        datasets: [{
            data: <?= json_encode($totals) ?>,
            borderColor: '#f8a5c2',
            backgroundColor: 'rgba(248, 165, 194, 0.1)',
            fill: true,
            tension: 0.4,
            pointRadius: 4,
            pointBackgroundColor: '#b3365b'
        }]
    },
    options: { 
        plugins: { legend: { display: false } }, 
        scales: { y: { beginAtZero: true, grid: { display: false } }, x: { grid: { display: false } } } 
    }
});

// Gender Proportion Chart
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
        plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, font: { size: 11 } } } },
        cutout: '70%'
    }
});
</script>

</body>
</html>