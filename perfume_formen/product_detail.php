<?php 
require_once "../config.php";

try {
    // ดึงข้อมูลสินค้าทั้งหมดจากตาราง products
    $stmt = $conn->prepare("SELECT * FROM products ORDER BY product_id DESC");
    $stmt->execute();
    $products = $stmt->fetchAll();
    
    // นับจำนวนสินค้าที่มีทั้งหมด
    $total_products = count($products);
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Products - ESXENSE</title>
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; background-color: #f9f9f9; }
        
        /* สไตล์ส่วน Header Banner เหมือนในรูปตัวอย่าง */
        .product-banner {
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('img/banner-bg.jpg'); /* ใส่รูปพื้นหลังโทนดำ */
            background-size: cover;
            background-position: center;
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
        }

        /* สไตล์การ์ดสินค้า */
        .product-card {
            border: none;
            background: white;
            transition: 0.3s;
            height: 100%;
            border-radius: 8px;
            overflow: hidden;
            text-align: center;
            padding: 20px;
        }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
        .product-img {
            max-width: 100%;
            height: 200px;
            object-fit: contain;
            margin-bottom: 15px;
        }
        .product-name { font-size: 0.9rem; color: #555; height: 45px; overflow: hidden; margin-bottom: 10px; }
        .product-price { font-weight: bold; color: #e84c88; font-size: 1.2rem; }
    </style>
</head>
<body>

<div class="product-banner">
    <div>
        <h1 class="display-4 fw-bold">Our Products</h1>
        <div class="mx-auto" style="width: 50px; height: 2px; background: white; margin-top: 10px;"></div>
    </div>
</div>
        <div class="text-muted small">พบ <?= $total_products ?> รายการ</div>
    </div>

    <div class="row g-4">
        <?php foreach($products as $row): ?>
<div class="col-6 col-md-4 col-lg-3">
    <div class="product-card shadow-sm" style="cursor: pointer;" 
         onclick="checkLoginBeforeView('<?= $row['product_id'] ?>')">
        
        <img src="photo/<?= htmlspecialchars($row['image']) ?>" class="product-img" alt="<?= $row['product_name'] ?>">
        
        <div class="product-name">
            <?= htmlspecialchars($row['product_name']) ?>
        </div>
        
        <div class="product-price">
            <?= number_format($row['price']) ?>฿
        </div>
    </div>
</div>
<?php endforeach; ?>
    </div>
</div>

<footer class="mt-5 py-5 bg-white border-top">
    <div class="container text-center text-muted">
        <p>© 2024 ESXENSE Perfume. All rights reserved.</p>
    </div>
</footer>

<script src="../bootstrap/js/bootstrap.bundle.min.js"></script>

</body>
</html>