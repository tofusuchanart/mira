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
    body { font-family: 'Sarabun', sans-serif; background-color: #fffafb; color: #333; }
    
    /* แบนเนอร์ปรับให้โค้งมนและใช้โทนสีแบรนด์ */
    .page-banner {
        background: linear-gradient(rgba(179, 54, 91, 0.7), rgba(0, 0, 0, 0.8)), url('../perfume_formen/photo/bner.png');
        background-size: cover;
        background-position: center;
        height: 250px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        border-radius: 0 0 50px 50px;
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }

    .breadcrumb-section { font-size: 0.9rem; letter-spacing: 1px; }
    .breadcrumb-section a { color: #b3365b; text-decoration: none; font-weight: 600; }
    .breadcrumb-section span { color: #888; }

    /* กล่องรูปภาพสินค้า */
    .product-img-box {
        background: #fff;
        border: none;
        border-radius: 30px;
        padding: 40px;
        text-align: center;
        transition: 0.3s;
    }
    .product-img-box img { 
        max-width: 100%; 
        height: auto; 
        max-height: 400px; 
        filter: drop-shadow(0 10px 15px rgba(0,0,0,0.1));
    }

    /* รายละเอียดสินค้า */
    .detail-content h1 { font-size: 2.2rem; font-weight: 800; color: #333; margin-bottom: 10px; }
    .category-badge {
        background: #f8a5c2;
        color: white;
        padding: 4px 15px;
        border-radius: 50px;
        font-size: 0.8rem;
        display: inline-block;
        margin-bottom: 15px;
    }
    .detail-content .description { 
        color: #666; 
        line-height: 1.8; 
        font-size: 1.05rem;
        background: #fdf2f5;
        padding: 20px;
        border-radius: 20px;
        margin-bottom: 30px;
    }
    .detail-content .price { 
        font-size: 2.5rem; 
        font-weight: bold; 
        color: #b3365b; 
        margin-bottom: 30px; 
    }

    /* ส่วนจัดการจำนวนและปุ่ม */
    .qty-input {
        border: 2px solid #eee;
        border-radius: 50px;
        display: inline-flex;
        align-items: center;
        padding: 5px;
        background: white;
    }
    .qty-input input {
        width: 60px;
        border: none;
        text-align: center;
        font-weight: bold;
        background: transparent;
    }
    .qty-input input:focus { outline: none; }

    .btn-mira-cart {
        background: #b3365b;
        color: white;
        border: none;
        padding: 15px 40px;
        border-radius: 50px;
        font-weight: bold;
        transition: 0.3s;
        box-shadow: 0 5px 15px rgba(179, 54, 91, 0.3);
    }
    .btn-mira-cart:hover {
        background: #a34a67;
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(179, 54, 91, 0.4);
        color: white;
    }
    .btn-back {
        border-radius: 50px;
        padding: 15px 25px;
        border: 2px solid #eee;
        color: #888;
        transition: 0.3s;
    }
    .btn-back:hover { background: #eee; color: #333; }
</style>

<div class="page-banner">
    <div class="text-center">
        <h1 class="display-5 fw-bold">นิยามแห่งความสง่างามในทุกหยดกลิ่น</h1>
        <p class="opacity-75">MIRA สำหรับผู้ที่เลือกสิ่งที่ดีที่สุดให้กับตัวเอง</p>
    </div>
</div>

<div class="container mt-5 mb-5">
    <div class="row g-5 align-items-center">
        <div class="col-md-6">
            <div class="breadcrumb-section mb-3">
                <a href="index_users.php">HOME</a> / <span><?= strtoupper($product['product_name']) ?></span>
            </div>
            <div class="product-img-box shadow-sm">
                <img src="../photo/<?= $product['image'] ?>" alt="<?= $product['product_name'] ?>">
            </div>
        </div>

        <div class="col-md-6">
            <div class="detail-content py-3">
                <span class="category-badge">สั่งเลย!</span>
                <h1><?= $product['product_name'] ?></h1>
                
                <div class="price">
                    ฿<?= number_format($product['price']) ?>
                </div>

                <div class="description">
                    <?= nl2br($product['description']) ?>
                </div>

                <form action="cart_action.php?action=add" method="POST" class="d-flex flex-wrap align-items-center gap-3">
                    <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                    
                    <div class="qty-input">
                        <span class="ms-3 text-muted small">จำนวน</span>
                        <input type="number" name="quantity" value="1" min="1">
                    </div>

                    <button type="submit" class="btn btn-mira-cart">
                        <i class="bi bi-bag-plus me-2"></i> หยิบใส่ตะกร้า
                    </button>
                    
                    <a href="../users/index_users.php" class="btn btn-back">
                        <i class="bi bi-arrow-left"></i> กลับ
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>