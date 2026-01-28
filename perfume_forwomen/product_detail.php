<?php 
require_once "../config.php";

// 1. รับ ID สินค้าจาก URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();

        if (!$product) {
            die("ไม่พบข้อมูลสินค้า");
        }
    } catch(PDOException $e) {
        die("Error: " . $e->getMessage());
    }
} else {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $product['product_name'] ?> - MIRA</title>
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; background-color: #fff; color: #333; }
        
        /* แบนเนอร์ด้านบนเหมือนในรูป */
        .page-banner {
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('img/banner-bg.jpg');
            background-size: cover;
            background-position: center;
            height: 250px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .breadcrumb-section { font-size: 0.85rem; color: #888; margin-bottom: 20px; }
        .breadcrumb-section a { color: #888; text-decoration: none; }

        .product-img-box {
            background: #fff;
            border: 1px solid #eee;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
        }
        .product-img-box img { max-width: 100%; height: auto; max-height: 450px; }

        .detail-content h1 { font-size: 1.8rem; font-weight: bold; margin-bottom: 15px; }
        .detail-content .category-tag { color: #e84c88; font-size: 1.1rem; margin-bottom: 20px; display: block; }
        .detail-content .description { color: #666; line-height: 1.8; margin-bottom: 30px; }
        .detail-content .price { font-size: 1.8rem; font-weight: bold; color: #e84c88; margin-bottom: 40px; }
        
        .btn-buy {
            background-color: #000;
            color: #fff;
            padding: 12px 40px;
            border-radius: 0;
            font-weight: bold;
            text-transform: uppercase;
        }
    </style>
</head>
<body>

<div class="page-banner">
    <div class="text-center">
        <h1 class="display-5 fw-bold">Our Products</h1>
        <p>— ⚪ —</p>
    </div>
</div>

<div class="container mt-5 mb-5">
    <div class="row">
        <div class="col-12 mb-4">
            <div class="breadcrumb-section">
                <a href="index.php">HOME</a> / <span><?= strtoupper($product['product_name']) ?></span>
            </div>
        </div>
    </div>

    <div class="row g-5">
        <div class="col-md-6">
            <div class="product-img-box shadow-sm">
                <img src="../photo/<?= $product['image'] ?>" alt="<?= $product['product_name'] ?>">
            </div>
        </div>

        <div class="col-md-6">
            <div class="detail-content py-3">
                <h1 class="mb-3"><?= $product['product_name'] ?></h1>
                <div class="description">
                    <?= nl2br($product['description']) ?>
                </div>

                <div class="price">
                    <?= number_format($product['price']) ?>฿
                </div>     
                <div class="mt-5">
                    <form action="cart_action.php?action=add" method="POST">
    <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
    <input type="number" name="quantity" value="1" min="1">
    <button type="submit">หยิบใส่ตะกร้า</button>
</form>
                    <a href="../users/index_users.php?link=women" class="btn btn-outline-secondary ms-2">กลับไปหน้าสินค้า</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>