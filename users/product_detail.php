<?php
session_start(); // ** สำคัญมาก: ต้องมีบรรทัดนี้ที่บนสุด **
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

        // 2. ดึงรูปภาพย่อย
        $stmt_img = $conn->prepare("SELECT * FROM product_images WHERE product_id = ?");
        $stmt_img->execute([$id]);
        $gallery = $stmt_img->fetchAll();

        // 3. ดึงข้อมูลรีวิว พร้อมชื่อผู้รีวิว
        $stmt_reviews = $conn->prepare("
            SELECT r.*, u.fullname 
            FROM reviews r 
            JOIN users u ON r.user_id = u.user_id 
            WHERE r.product_id = ? 
            ORDER BY r.review_date DESC
        ");
        $stmt_reviews->execute([$id]);
        $reviews = $stmt_reviews->fetchAll();

        // 4. เช็คสิทธิ์การรีวิว (ต้อง Login และเคยซื้อสินค้านี้ที่มีสถานะ completed)
        $can_review = false;
        if (isset($_SESSION['user_id'])) {
            $stmt_check = $conn->prepare("
                SELECT oi.item_id 
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.order_id
                WHERE o.user_id = ? AND oi.product_id = ? AND o.status = 'completed'
                LIMIT 1
            ");
            $stmt_check->execute([$_SESSION['user_id'], $id]);
            if ($stmt_check->fetch()) { 
                $can_review = true; 
            }
        }

    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
} else {
    header("Location: index_users.php");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $product_id = $_POST['product_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];
    $user_id = $_SESSION['user_id'];

    // 1. สร้างรายการคำหยาบ
    $bad_words = ["มึง", "กู", "ควย", "เย็ด", "สัด", "เหี้ย", "ไอ้สัส"]; 
    
    // 2. กรองคำหยาบ (แทนที่ด้วย ***)
    $filtered_comment = str_ireplace($bad_words, "***", $comment);

    try {
        // 3. ใช้ $filtered_comment ในการบันทึก (แทน $comment เดิม)
        $sql = "INSERT INTO reviews (product_id, user_id, rating, comment, review_date) 
                VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        
        if ($stmt->execute([$product_id, $user_id, $rating, $filtered_comment])) {
            echo "<script>alert('รีวิวสำเร็จ!'); window.location='product_detail.php?id=$product_id';</script>";
        }
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
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
        .review-section { background: white; border-radius: 30px; padding: 40px; margin-top: 50px; }
    .star-rating { color: #ffc107; font-size: 1.2rem; }
    .review-media img, .review-media video { 
        width: 120px; height: 120px; object-fit: cover; border-radius: 15px; margin-top: 10px; 
    }
    .rating-input { direction: rtl; display: inline-block; }
    .rating-input input { display: none; }
    .rating-input label { color: #ddd; font-size: 2rem; padding: 0 2px; cursor: pointer; }
    .rating-input label:hover, .rating-input label:hover ~ label,
    .rating-input input:checked ~ label { color: #ffc107; }
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
    
    <div class="container mt-5">
    <div class="card shadow-sm p-4" style="border-radius: 20px; border: none;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold m-0" style="color: #b3365b;">รีวิวจากลูกค้า</h4>
            
            <?php if ($can_review): ?>
                <button class="btn btn-mira-cart" type="button" data-bs-toggle="modal" data-bs-target="#reviewModal">
                    <i class="bi bi-pencil-square me-2"></i> เขียนรีวิวของคุณ
                </button>
            <?php else: ?>
                <span class="badge bg-light text-muted border py-2 px-3 rounded-pill">
                    <i class="bi bi-info-circle me-1"></i> เฉพาะผู้ที่เคยซื้อสินค้านี้เท่านั้นที่รีวิวได้
                </span>
            <?php endif; ?>
        </div>

        <hr class="my-4 opacity-50">

        <div class="review-list">
    <?php if (count($reviews) > 0): ?>
        <?php foreach ($reviews as $row): ?>
            <div class="review-item mb-4 p-3 shadow-sm" style="border-radius: 15px; background: #fff; border-left: 5px solid #b3365b;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <span class="fw-bold text-dark"><?= htmlspecialchars($row['fullname']) ?></span>
                        <div class="star-rating small">
                            <?php 
                            for($i=1; $i<=5; $i++) {
                                echo $i <= $row['rating'] ? '<i class="bi bi-star-fill text-warning"></i>' : '<i class="bi bi-star text-muted"></i>';
                            }
                            ?>
                        </div>
                    </div>
                    <small class="text-muted">
                        <i class="bi bi-calendar3 me-1"></i> <?= date('d/m/Y', strtotime($row['review_date'])) ?>
                    </small>
                </div>
                
                <p class="mb-2 text-secondary" style="font-size: 0.95rem;">
                    <?php 
    $bad_words = ["มึง", "กู", "ควย", "เย็ด", "สัด", "เหี้ย", "ไอ้สัส","หี"];
    $display_comment = str_ireplace($bad_words, "***", $row['comment']);
    echo nl2br(htmlspecialchars($display_comment)); 
?>
                </p>

                <div class="review-media mt-2 d-flex flex-wrap gap-2">
                    <?php if (!empty($row['review_image'])): ?>
                        <img src="../uploads/reviews/<?= $row['review_image'] ?>" 
                             class="img-thumbnail" style="width: 150px; height: 150px; object-fit: cover; border-radius: 10px;">
                    <?php endif; ?>

                    <?php if (!empty($row['review_video'])): ?>
                        <video width="250" controls style="border-radius: 10px;" class="shadow-sm">
                            <source src="../uploads/reviews/<?= $row['review_video'] ?>" type="video/mp4">
                            เบราว์เซอร์ของคุณไม่รองรับการเล่นวิดีโอ
                        </video>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="bi bi-chat-dots text-muted display-4"></i>
            <p class="mt-2 text-muted">ยังไม่มีรีวิวสำหรับสินค้านี้ มารีวิวเป็นคนแรกกันเถอะ!</p>
        </div>
    <?php endif; ?>
</div>
        </div>
    </div>
</div>
<div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 25px; border: none;">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="modal-title fw-bold" id="reviewModalLabel" style="color: #b3365b;">
                    <i class="bi bi-stars me-2"></i> แบ่งปันประสบการณ์ของคุณ
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form action="submit_review.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                    
                    <div class="mb-4 text-center bg-light p-3" style="border-radius: 15px;">
                        <label class="form-label d-block fw-bold mb-3">คะแนนความพึงพอใจ</label>
                        <div class="star-rating-select">
                            <select name="rating" class="form-select w-auto mx-auto border-0 shadow-sm" style="border-radius: 10px;" required>
                                <option value="5">⭐⭐⭐⭐⭐ (5/5)</option>
                                <option value="4">⭐⭐⭐⭐ (4/5)</option>
                                <option value="3">⭐⭐⭐ (3/5)</option>
                                <option value="2">⭐⭐ (2/5)</option>
                                <option value="1">⭐ (1/5)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">ข้อความรีวิวของคุณ</label>
                        <textarea name="comment" class="form-control border-0 bg-light" rows="4" 
                                  style="border-radius: 15px;" placeholder="บอกเราหน่อยว่าคุณชอบสินค้านี้อย่างไร..." required></textarea>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">แนบรูปภาพสินค้า</label>
                            <input type="file" name="review_image" class="form-control" accept="image/*" style="border-radius: 10px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">แนบวิดีโอ (ถ้ามี)</label>
                            <input type="file" name="review_video" class="form-control" accept="video/*" style="border-radius: 10px;">
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-mira-cart py-3">
                            ส่งรีวิวของคุณเลย <i class="bi bi-send-fill ms-2"></i>
                        </button>
                    </div>
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