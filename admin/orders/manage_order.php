<?php 
require_once "../../config.php"; // เชื่อมต่อฐานข้อมูล

// ดึงข้อมูลสินค้าทั้งหมดจากตาราง products โดยเรียงจากใหม่ไปเก่า
try {
    $stmt = $conn->prepare("SELECT * FROM products ORDER BY product_id DESC");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - MIRA Admin</title>
    <link href="../../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;700&display=swap" rel="stylesheet">
    
    <style>
        body { 
            font-family: 'Sarabun', sans-serif; 
            /* ใช้รูปพื้นหลังเดียวกับหน้า Dashboard เพื่อความต่อเนื่อง */
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('../../admin/photo_ad/ro.jpg');
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
            min-height: 100vh;
            color: white;
        }

        .navbar-glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 30px;
            margin-top: 30px;
            margin-bottom: 50px;
        }

        .table {
            color: white;
            vertical-align: middle;
        }

        .table thead th {
            background: rgba(255, 255, 255, 0.1);
            color: #f8a5c2; /* สีชมพูพาสเทล */
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.9rem;
        }

        .table tbody td {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 15px;
        }

        .product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .btn-action {
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 0.85rem;
            transition: 0.3s;
        }

        .btn-add {
            background-color: #f8a5c2;
            color: white;
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
        }
        
        .btn-add:hover {
            background-color: #f78fb3;
            color: white;
            transform: scale(1.05);
        }

        /* ปรับสีตัวเลขราคา */
        .price-text {
            color: #00d2d3;
            font-weight: bold;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-glass sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index_ad.php">
            <i class="bi bi-shield-lock-fill me-2"></i>MIRA ADMIN
        </a>
        <div class="ms-auto">
            <a href="../index_ad.php" class="btn btn-outline-light btn-sm me-2">Dashboard</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="glass-card shadow-lg">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-0">จัดการรายการสินค้า</h2>
                <p class="text-white-50 small mb-0">จัดการข้อมูล แก้ไข และลบสินค้าในระบบ</p>
            </div>
            <a href="add_product.php" class="btn btn-add shadow-sm">
                <i class="bi bi-plus-lg me-2"></i>เพิ่มสินค้าใหม่
            </a>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th width="80">ID</th>
                        <th width="100">รูปภาพ</th>
                        <th>ชื่อสินค้า</th>
                        <th width="150">ราคา</th>
                        <th width="180" class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($products) > 0): ?>
                        <?php foreach($products as $row): ?>
                        <tr>
                            <td><span class="badge bg-dark">#<?php echo $row['product_id']; ?></span></td>
                            <td>
                                <img src="../photo/<?php echo $row['image']; ?>" class="product-img" onerror="this.src='../img/no-image.png'">
                            </td>
                            <td>
                                <div class="fw-bold"><?php echo $row['product_name']; ?></div>
                                <small class="text-white-50 d-block text-truncate" style="max-width: 250px;">
                                    <?php echo $row['description']; ?>
                                </small>
                            </td>
                            <td><span class="price-text"><?php echo number_format($row['price'], 2); ?> ฿</span></td>
                            <td class="text-center">
                                <a href="edit.php?id=<?php echo $row['product_id']; ?>" class="btn btn-warning btn-action me-1 shadow-sm">
                                    <i class="bi bi-pencil-square"></i> แก้ไข
                                </a>
                                <a href="delete.php?id=<?php echo $row['product_id']; ?>" 
                                   class="btn btn-danger btn-action shadow-sm" 
                                   onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบสินค้า: <?php echo $row['product_name']; ?>?')">
                                    <i class="bi bi-trash"></i> ลบ
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">ไม่พบข้อมูลสินค้าในระบบ</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>