<?php 
require_once "../config.php";

try {
    // 1. จัดการสินค้า: นับจากตาราง products
    $countProducts = $conn->query("SELECT COUNT(*) FROM products")->fetchColumn();

    // 2. จัดการรีวิว: นับจากตาราง reviews
    $countReviews = $conn->query("SELECT COUNT(*) FROM reviews")->fetchColumn();

    // 3. จัดการผู้ใช้: นับจากตาราง users
    $countUsers = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
    
    // 4. จัดการการเงิน: นับจำนวนรายการชำระเงินจากตาราง payments
    $countPayments = $conn->query("SELECT COUNT(*) FROM payments")->fetchColumn();

} catch(PDOException $e) {
    echo "เกิดข้อผิดพลาด: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIRA Admin Dashboard</title>
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --mira-pink: #D65A8D;
            --mira-bg: #FDF2F5;
            --mira-card-bg: #FFFFFF;
        }

        body { 
            font-family: 'Sarabun', sans-serif; 
            background-color: var(--mira-bg);
            /* สามารถใส่รูปพื้นหลังจางๆ ได้ถ้าต้องการ */
            background-image: linear-gradient(rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0.8)), url('../admin/photo_ad/ro.jpg');
            background-size: cover;
            background-attachment: fixed;
            min-height: 100vh;
            color: #444;
        }

        .header-section {
            padding: 40px 0;
            text-align: left;
        }

        .header-section h1 {
            color: var(--mira-pink);
            font-weight: 700;
            font-size: 2.2rem;
        }

        .header-section p {
            color: #888;
            font-size: 1rem;
        }

        /* ปรับแต่ง Card ให้เหมือนรูปตัวอย่าง */
        .stat-card { 
            background: var(--mira-card-bg);
            border: none;
            border-radius: 20px; 
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(214, 90, 141, 0.08);
            padding: 25px;
        }

        .stat-card:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 8px 25px rgba(214, 90, 141, 0.15);
        }

        .icon-box {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }

        /* สีไอคอนตามประเภท */
        .bg-product { background-color: #FEE2E2; color: #EF4444; }
        .bg-review { background-color: #FEF3C7; color: #F59E0B; }
        .bg-money { background-color: #D1FAE5; color: #10B981; }
        .bg-user { background-color: #E0E7FF; color: #6366F1; }

        .stat-label {
            color: #777;
            font-size: 0.95rem;
            margin-bottom: 5px;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 15px;
        }

        .btn-mira {
            background-color: #f8f9fa;
            border: 1px solid #eee;
            color: #666;
            border-radius: 10px;
            font-size: 0.9rem;
            padding: 8px;
            transition: 0.3s;
        }

        .btn-mira:hover {
            background-color: var(--mira-pink);
            color: white;
            border-color: var(--mira-pink);
        }

        .btn-dashboard {
            border: 1px solid #333;
            border-radius: 20px;
            padding: 5px 20px;
            color: #333;
            text-decoration: none;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<header class="glass-header text-center">
    <div class="container">
        <h1 class="fw-bold mb-0">MIRA CONTROL CENTER</h1>
        <p class="text-white-50">ระบบจัดการหลังบ้าน MIRA Store</p>
    </div>
</header>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-start header-section">
        <div>
            <h1>Customer Directory</h1>
            <p>บริหารจัดการข้อมูลสมาชิกและสถิติการซื้อ</p>
        </div>
        <a href="#" class="btn-dashboard"><i class="bi bi-layout-text-sidebar-reverse"></i> Dashboard</a>
    </div>

    <div class="row g-4">
        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="icon-box bg-product">
                    <i class="bi bi-box-seam fs-4"></i>
                </div>
                <div class="stat-label">จำนวนสินค้าทั้งหมด</div>
                <div class="stat-number"><?php echo number_format($countProducts); ?></div>
                <a href="orders/manage_order.php" class="btn btn-mira w-100">ดูรายการสินค้า</a>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="icon-box bg-review">
                    <i class="bi bi-chat-left-heart fs-4"></i>
                </div>
                <div class="stat-label">ความคิดเห็นลูกค้า</div>
                <div class="stat-number"><?php echo number_format($countReviews); ?></div>
                <a href="review/review.php" class="btn btn-mira w-100">จัดการรีวิว</a>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="icon-box bg-money">
                    <i class="bi bi-wallet2 fs-4"></i>
                </div>
                <div class="stat-label">รายการชำระเงิน</div>
                <div class="stat-number"><?php echo number_format($countPayments); ?></div>
                <a href="dashboard/dashboard.php" class="btn btn-mira w-100">ดูประวัติรายรับ</a>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card h-100">
                <div class="icon-box bg-user">
                    <i class="bi bi-people fs-4"></i>
                </div>
                <div class="stat-label">สมาชิกทั้งหมด</div>
                <div class="stat-number"><?php echo number_format($countUsers); ?></div>
                <a href="member/member.php" class="btn btn-mira w-100">ดูสมาชิก</a>
            </div>
        </div>
    </div>

    <div class="text-center mt-5 pb-5">
        <hr class="opacity-10 mb-4">
        <a href="../login/logout.php" class="btn btn-outline-danger px-4 rounded-pill">ออกจากระบบ</a>
    </div>
</div>
<script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>