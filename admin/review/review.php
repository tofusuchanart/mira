<?php
require_once "../../config.php";

// ดึงข้อมูลรีวิวทั้งหมด
try {
    $sql = "SELECT r.*, u.fullname, u.profile_img, p.product_name 
            FROM reviews r
            JOIN users u ON r.user_id = u.user_id
            JOIN products p ON r.product_id = p.product_id
            ORDER BY r.review_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// โค้ดสำหรับลบรีวิว
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $del_stmt = $conn->prepare("DELETE FROM reviews WHERE review_id = ?");
    
    if ($del_stmt->execute([$delete_id])) {
        // แก้ไขจาก 'manage_reviews.php' เป็น 'review.php' (หรือไฟล์หน้าหลักของคุณ)
        echo "<script>alert('ลบรีวิวเรียบร้อยแล้ว'); window.location='review.php';</script>";
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reviews - MIRA Admin</title>
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;700&display=swap" rel="stylesheet">
    
    <style>
        body { 
            font-family: 'Sarabun', sans-serif; 
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('../../admin/photo_ad/ro.jpg');
            background-size: cover;
            background-attachment: fixed;
            min-height: 100vh;
            color: white;
        }

        .glass-container {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(15px);
            border-radius: 25px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            padding: 30px;
            margin-top: 40px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.5);
        }

        .review-card-item {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: 0.3s;
        }

        .review-card-item:hover {
            background: rgba(255, 255, 255, 0.12);
            transform: scale(1.01);
        }

        .user-img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #f8a5c2;
        }

        .star-rating { color: #f8a5c2; }
        
        .product-badge {
            background: rgba(248, 165, 194, 0.2);
            color: #f8a5c2;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            border: 1px solid rgba(248, 165, 194, 0.3);
        }

        .btn-delete {
            background: rgba(255, 71, 87, 0.2);
            border: 1px solid #ff4757;
            color: #ff4757;
            border-radius: 10px;
            transition: 0.3s;
        }

        .btn-delete:hover {
            background: #ff4757;
            color: white;
        }

        .nav-link-custom {
            color: white;
            text-decoration: none;
            opacity: 0.7;
            transition: 0.3s;
        }

        .nav-link-custom:hover { opacity: 1; color: #f8a5c2; }
    </style>
</head>
<body>

<div class="container pb-5">
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

    <div class="glass-container">
        <?php if (empty($reviews)): ?>
            <div class="text-center py-5">
                <i class="bi bi-chat-dots fs-1 text-white-50"></i>
                <p class="mt-3 text-white-50">ยังไม่มีข้อมูลการรีวิวในขณะนี้</p>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach($reviews as $rev): ?>
                <div class="col-12">
                    <div class="review-card-item">
                        <div class="row align-items-center">
                            <div class="col-md-1 text-center">
                                <?php 
                                    $pic = !empty($rev['profile_img']) ? "../photo/".$rev['profile_img'] : "https://ui-avatars.com/api/?name=".urlencode($rev['fullname']);
                                ?>
                                <img src="<?= $pic ?>" class="user-img shadow-sm">
                            </div>
                            <div class="col-md-3">
                                <h6 class="mb-1 fw-bold"><?= htmlspecialchars($rev['fullname']) ?></h6>
                                <span class="product-badge">รีวิวสินค้า: <?= htmlspecialchars($rev['product_name']) ?></span>
                            </div>
                            <div class="col-md-5">
                                <div class="star-rating mb-1">
                                    <?= str_repeat('<i class="bi bi-star-fill"></i> ', $rev['rating']) ?>
                                    <?= str_repeat('<i class="bi bi-star"></i> ', 5 - $rev['rating']) ?>
                                </div>
                                <p class="small text-white-50 mb-0">"<?= htmlspecialchars($rev['comment']) ?>"</p>
                                <small class="text-muted" style="font-size: 0.7rem;"><?= $rev['review_date'] ?></small>
                            </div>
                            <div class="col-md-3 text-end">
                                <a href="?delete_id=<?= $rev['review_id'] ?>" 
                                   class="btn btn-delete px-3 py-2"
                                   onclick="return confirm('ยืนยันการลบรีวิวนี้หรือไม่?')">
                                    <i class="bi bi-trash3 me-1"></i> ลบรีวิว
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>