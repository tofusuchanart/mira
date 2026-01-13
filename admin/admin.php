<?php 
require_once "config.php"; // เชื่อมต่อฐานข้อมูล

// ดึงข้อมูลสินค้าทั้งหมดจากตาราง products
try {
    $stmt = $conn->prepare("SELECT * FROM products");
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
    <title>Mira Admin Dashboard</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark sticky-top">
    <div class="container">
        <a class="navbar-brand" href="#">Mira Admin Panel</a>
        <a href="index.php" class="btn btn-outline-light btn-sm">ไปหน้าเว็บหลัก</a>
    </div>
</nav>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>จัดการรายการสินค้า</h2>
        <a href="add_product.php" class="btn btn-success"><i class="bi bi-plus-circle"></i> เพิ่มสินค้าใหม่</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>รูปภาพ</th>
                        <th>ชื่อสินค้า</th>
                        <th>ราคา</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($products as $row): ?>
                    <tr>
                        <td><?php echo $row['product_id']; ?></td>
                        <td>
                            <img src="photo/<?php echo $row['image']; ?>" width="50" class="rounded">
                        </td>
                        <td><?php echo $row['product_name']; ?></td>
                        <td><?php echo number_format($row['price'], 2); ?> ฿</td>
                        <td>
                            <a href="edit.php?id=<?php echo $row['product_id']; ?>" class="btn btn-warning btn-sm">แก้ไข</a>
                            <a href="delete.php?id=<?php echo $row['product_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('ยืนยันการลบ?')">ลบ</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>