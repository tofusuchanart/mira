<?php
require_once "../config.php";

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        // 1. ดึงข้อมูลสินค้าหลัก
        $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();

        if (!$product) {
            die("ไม่พบข้อมูลสินค้า");
        }

        // 2. ดึงรูปภาพย่อยจากตาราง product_images
        $stmt_img = $conn->prepare("SELECT * FROM product_images WHERE product_id = ?");
        $stmt_img->execute([$id]);
        $gallery = $stmt_img->fetchAll();
    } catch (PDOException $e) {
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background-color: #fffafb;
            color: #333;
        }

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
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .breadcrumb-section {
            font-size: 0.9rem;
            letter-spacing: 1px;
        }

        .breadcrumb-section a {
            color: #b3365b;
            text-decoration: none;
            font-weight: 600;
        }

        /* กล่องรูปภาพสินค้าหลัก */
        .product-img-box {
            background: #fff;
            border-radius: 30px;
            padding: 30px;
            text-align: center;
            position: relative;
        }

        .product-img-box #mainProductImg {
            max-width: 100%;
            height: auto;
            max-height: 450px;
            filter: drop-shadow(0 15px 25px rgba(0, 0, 0, 0.1));
            transition: 0.5s ease-in-out;
        }

        /* Gallery Thumbnails */
        .gallery-container {
            display: flex;
            gap: 12px;
            margin-top: 25px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .thumb-img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 12px;
            cursor: pointer;
            border: 2px solid #eee;
            transition: 0.3s;
            background: #fff;
            padding: 4px;
        }

        .thumb-img:hover,
        .thumb-img.active {
            border-color: #b3365b;
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(179, 54, 91, 0.2);
        }

        /* Content Details */
        .detail-content h1 {
            font-size: 2.5rem;
            font-weight: 800;
            color: #333;
        }

        .category-badge {
            background: #f8a5c2;
            color: white;
            padding: 5px 18px;
            border-radius: 50px;
            font-size: 0.85rem;
            display: inline-block;
            margin-bottom: 15px;
        }

        .price {
            font-size: 2.8rem;
            font-weight: bold;
            color: #b3365b;
            margin: 20px 0;
        }

        .description {
            color: #555;
            line-height: 1.8;
            font-size: 1.05rem;
            background: #fff;
            padding: 25px;
            border-radius: 25px;
            border: 1px solid #fceef2;
            margin-bottom: 30px;
        }

        /* Buttons & Qty */
        .qty-input {
            border: 2px solid #f0f0f0;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            padding: 8px 15px;
            background: white;
        }

        .qty-input input {
            width: 50px;
            border: none;
            text-align: center;
            font-weight: bold;
        }

        .btn-mira-cart {
            background: #b3365b;
            color: white;
            border: none;
            padding: 15px 45px;
            border-radius: 50px;
            font-weight: bold;
            transition: 0.3s;
            box-shadow: 0 10px 20px rgba(179, 54, 91, 0.2);
        }

        .btn-mira-cart:hover {
            background: #8e2a48;
            transform: scale(1.05);
            color: white;
        }

        .btn-back {
            border-radius: 50px;
            padding: 15px 25px;
            border: 2px solid #eee;
            color: #888;
            transition: 0.3s;
        }
    </style>
</head>

<body>

    <div class="page-banner">
        <div class="text-center">
            <h1 class="display-5 fw-bold">นิยามแห่งความสง่างาม</h1>
            <p class="opacity-75">MIRA Premium Fragrance Selection</p>
        </div>
    </div>

    <div class="container mt-5 mb-5">
        <div class="row g-5">
            <div class="col-lg-6">
                <div class="breadcrumb-section mb-4">
                    <a href="index_users.php">HOME</a> / <span class="text-uppercase"><?= $product['product_name'] ?></span>
                </div>

                <div class="product-img-box shadow-sm border border-light">
                    <img src="../photo/<?= $product['image'] ?>" id="mainProductImg" alt="<?= $product['product_name'] ?>">

                    <?php if (count($gallery) > 0): ?>
                        <div class="gallery-container">
                            <img src="../photo/<?= $product['image'] ?>"
                                class="thumb-img active"
                                onclick="changeImage(this.src, this)">

                            <?php foreach ($gallery as $img): ?>
                                <img src="../photo/<?= $img['image_path'] ?>"
                                    class="thumb-img"
                                    onclick="changeImage(this.src, this)">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="detail-content ps-lg-4">
                    <span class="category-badge"><i class="bi bi-stars me-1"></i> Best Seller</span>
                    <h1><?= $product['product_name'] ?></h1>

                    <div class="price">฿<?= number_format($product['price']) ?></div>

                    <div class="description shadow-sm">
                        <?= nl2br($product['description']) ?>
                    </div>

                    <form action="cart_action.php?action=add" method="POST" class="d-flex flex-wrap align-items-center gap-3">
                        <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">

                        <div class="qty-input">
                            <span class="me-2 text-muted small">จำนวน</span>
                            <input type="number" name="quantity" value="1" min="1">
                        </div>

                        <button type="submit" class="btn btn-mira-cart">
                            <i class="bi bi-bag-heart-fill me-2"></i> ใส่ตะกร้า
                        </button>

                        <a href="index_users.php" class="btn btn-back">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        function changeImage(src, element) {
            // ดึง Element รูปหลัก
            const mainImg = document.getElementById('mainProductImg');

            // ใส่ Effect ค่อยๆ เปลี่ยน (Fade)
            mainImg.style.opacity = '0';

            setTimeout(() => {
                mainImg.src = src;
                mainImg.style.opacity = '1';
            }, 200);

            // จัดการ Class Active ของ Thumbnail
            const thumbs = document.querySelectorAll('.thumb-img');
            thumbs.forEach(thumb => thumb.classList.remove('active'));
            element.classList.add('active');
        }
    </script>
</body>

</html>