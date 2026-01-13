<?php 
// ถอยหลัง 1 ชั้นเพื่อไปหาไฟล์ config.php ที่อยู่ด้านนอก.png
require_once "../config.php"; 

try {
    // 1. นับจำนวนสินค้าทั้งหมด
    $stmtProd = $conn->query("SELECT COUNT(*) FROM products");
    $countProducts = $stmtProd->fetchColumn();

    // 2. นับจำนวนสมาชิก (ตาราง users)
    $stmtUser = $conn->query("SELECT COUNT(*) FROM users");
    $countUsers = $stmtUser->fetchColumn();

    // 3. นับจำนวนหมวดหมู่สินค้า
    $stmtCat = $conn->query("SELECT COUNT(*) FROM categories");
    $countCategories = $stmtCat->fetchColumn();

} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Mira</title>
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Sarabun', sans-serif; background-color: #f4f7f6; }
        .stat-card { border: none; border-radius: 15px; transition: transform 0.3s; }
        .stat-card:hover { transform: translateY(-5px); }
        .icon-box { font-size: 3rem; opacity: 0.3; position: absolute; right: 15px; top: 15px; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#">MIRA ADMIN</a>
        <div class="ms-auto">
            <a href="../index.php" class="btn btn-outline-light btn-sm">ดูหน้าเว็บไซต์</a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h2 class="mb-4 fw-bold text-dark">ภาพรวมระบบ (Dashboard)</h2>
    
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card stat-card bg-primary text-white shadow-sm h-100">
                <div class="card-body">
                    <i class="bi bi-box-seam icon-box"></i>
                    <h6 class="text-uppercase mb-2">สินค้าในระบบ</h6>
                    <h2 class="display-4 fw-bold"><?php echo $countProducts; ?></h2>
                    <p class="mb-0 mt-3">
                        <a href="admin.php" class="text-white text-decoration-none small">
                            จัดการสินค้า <i class="bi bi-arrow-right-circle ms-1"></i>
                        </a>
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card stat-card bg-success text-white shadow-sm h-100">
                <div class="card-body">
                    <i class="bi bi-grid-3x3-gap icon-box"></i>
                    <h6 class="text-uppercase mb-2">หมวดหมู่ทั้งหมด</h6>
                    <h2 class="display-4 fw-bold"><?php echo $countCategories; ?></h2>
                    <p class="mb-0 mt-3 small">จัดการหมวดหมู่สินค้า</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card stat-card bg-info text-white shadow-sm h-100">
                <div class="card-body">
                    <i class="bi bi-people icon-box"></i>
                    <h6 class="text-uppercase mb-2">สมาชิก (Users)</h6>
                    <h2 class="display-4 fw-bold"><?php echo $countUsers; ?></h2>
                    <p class="mb-0 mt-3 small">ข้อมูลสมาชิกในฐานข้อมูล</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-12">
            <div class="p-5 bg-white rounded shadow-sm">
                <h4 class="mb-4">เมนูจัดการด่วน</h4>
                <div class="d-flex gap-3">
                    <a href="add_product.php" class="btn btn-success p-3 px-4">
                        <i class="bi bi-plus-circle me-2"></i> เพิ่มสินค้าใหม่
                    </a>
                    <a href="admin.php" class="btn btn-primary p-3 px-4">
                        <i class="bi bi-list-ul me-2"></i> รายการสินค้าทั้งหมด
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>