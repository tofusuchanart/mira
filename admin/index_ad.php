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
        body { 
            font-family: 'Sarabun', sans-serif; 
            /* ใส่รูป Background โทนเดียวกับหน้าสมัครสมาชิก */
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://images.unsplash.com/photo-1594035910387-fea47794261f?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
            min-height: 100vh;
            color: white;
        }

        .glass-header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding: 20px 0;
            margin-bottom: 50px;
        }

        .action-card { 
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px; 
            transition: all 0.4s ease;
            color: white;
        }

        .action-card:hover { 
            transform: translateY(-10px); 
            background: rgba(255, 255, 255, 0.25);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }

        .icon-circle {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: bold;
            margin: 10px 0;
        }

        .btn-glass {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.4);
            color: white;
            border-radius: 10px;
            transition: 0.3s;
        }

        .btn-glass:hover {
            background: white;
            color: #333;
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

<div class="container">
    <div class="row g-4">
        
        <div class="col-md-4">
            <div class="card action-card h-100 shadow-sm text-center p-4 border-0">
                <div class="card-body">
                    <div class="icon-circle text-info">
                        <i class="bi bi-box-seam fs-1"></i>
                    </div>
                    <h4 class="fw-bold">จัดการสินค้า</h4>
                    <div class="stat-value text-info"><?php echo $countProducts; ?></div>
                    <p class="text-white-50">จำนวนสินค้าทั้งหมดในคลัง</p>
                    <a href="manage_order.php" class="btn btn-glass w-100 mt-3">ดูรายการสินค้า</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card action-card h-100 shadow-sm text-center p-4 border-0">
                <div class="card-body">
                    <div class="icon-circle text-warning">
                        <i class="bi bi-chat-left-heart fs-1"></i>
                    </div>
                    <h4 class="fw-bold">รีวิวและคอมเมนต์</h4>
                    <div class="stat-value text-warning"><?php echo $countReviews; ?></div>
                    <p class="text-white-50">ความคิดเห็นจากลูกค้า</p>
                    <a href="#" class="btn btn-glass w-100 mt-3">จัดการรีวิว</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card action-card h-100 shadow-sm text-center p-4 border-0">
                <div class="card-body">
                    <div class="icon-circle text-success">
                        <i class="bi bi-wallet2 fs-1"></i>
                    </div>
                    <h4 class="fw-bold">จัดการการเงิน</h4>
                    <div class="stat-value text-success"><?php echo $countPayments; ?></div>
                    <p class="text-white-50">รายการแจ้งชำระเงิน</p>
                    <a href="#" class="btn btn-glass w-100 mt-3">ดูประวัติรายรับ</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card action-card h-100 shadow-sm text-center p-4 border-0">
                <div class="card-body">
                    <div class="icon-circle text-danger">
                        <i class="bi bi-people fs-1"></i>
                    </div>
                    <h4 class="fw-bold">ผู้ใช้ทั้งหมด</h4>
                    <div class="stat-value text-danger"><?php echo $countUsers; ?></div>
                    <p class="text-white-50">สมาชิกที่ลงทะเบียน</p>
                </div>
            </div>
        </div>

    </div>

    <div class="text-center mt-5 pb-5">
        <a href="../index.php" class="btn btn-outline-light px-4">กลับหน้าเว็บไซต์</a>
    </div>
</div>

<script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>