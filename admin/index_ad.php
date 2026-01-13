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
    // แก้ไขปัญหา 'Column not found' โดยเปลี่ยนจากการหา SUM(amount) เป็นการนับรายการก่อนจนกว่าจะทราบชื่อคอลัมน์เงินที่แน่นอน
    $countPayments = $conn->query("SELECT COUNT(*) FROM payments")->fetchColumn();

} catch(PDOException $e) {
    echo "เกิดข้อผิดพลาด: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>MIRA Admin Dashboard</title>
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Sarabun', sans-serif; background-color: #f4f7f6; }
        .action-card { border: none; border-radius: 15px; transition: 0.3s; }
        .action-card:hover { transform: translateY(-5px); }
    </style>
</head>
<body>

<div class="container mt-5">
    <h2 class="mb-4 text-center fw-bold">ระบบจัดการหลังบ้าน MIRA</h2>
    <div class="row g-3">
        
        <div class="col-md-3">
            <div class="card action-card shadow-sm bg-primary text-white text-center p-3">
                <div class="card-body">
                    <i class="bi bi-box-seam fs-1"></i>
                    <h5 class="mt-2">จัดการสินค้า</h5>
                    <p class="display-6"><?php echo $countProducts; ?></p>
                    <a href="manage_order.php" class="btn btn-light btn-sm w-100">ดูรายการ</a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card action-card shadow-sm bg-warning text-dark text-center p-3">
                <div class="card-body">
                    <i class="bi bi-chat-left-text fs-1"></i>
                    <h5 class="mt-2">รีวิวและคอมเมนต์</h5>
                    <p class="display-6"><?php echo $countReviews; ?></p>
                    <a href="#" class="btn btn-dark btn-sm w-100">ตรวจสอบ</a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card action-card shadow-sm bg-success text-white text-center p-3">
                <div class="card-body">
                    <i class="bi bi-currency-dollar fs-1"></i>
                    <h5 class="mt-2">จัดการการเงิน</h5>
                    <p class="display-6"><?php echo $countPayments; ?></p>
                    <a href="#" class="btn btn-light btn-sm w-100">ดูรายรับ</a>
                </div>
            </div>
        </div>

    </div>
</div>

</body>
</html>