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
    <link rel="icon" href="../photo_ad/golo.png">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@200;400;600&display=swap');

        body { 
            background-color: #fff5f7; /* พื้นหลังชมพูอ่อนมากแบบ Minimal */
            font-family: 'Sarabun', sans-serif; 
            color: #4a4a4a;
        }

        /* ปุ่มย้อนกลับแบบในรูป */
        .back-link {
            text-decoration: none;
            color: #94a3b8;
            font-size: 0.95rem;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 15px;
        }
        .back-link:hover { color: #b3365b; }

        h2.fw-bold {
            color: #b3365b;
            letter-spacing: -1px;
        }

        /* คอนเทนเนอร์หลักขาวสะอาด */
        .glass-container {
            background: #ffffff;
            border-radius: 25px;
            border: none;
            padding: 35px;
            box-shadow: 0 10px 30px rgba(179, 54, 91, 0.05);
            margin-top: 20px;
        }

        /* รายการรีวิวแบบ Minimal */
        .review-card-item {
            background: #fff;
            border-bottom: 1px solid #f1f1f1;
            padding: 20px 0;
            transition: 0.2s;
        }
        .review-card-item:last-child { border-bottom: none; }

        .user-img {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #f8a5c2;
        }

        .star-rating { color: #f8a5c2; font-size: 0.9rem; }
        
        .product-badge {
            background: #fff0f3;
            color: #d63384;
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 0.75rem;
            border: 1px solid #f8d7da;
            display: inline-block;
        }

        /* ปุ่มลบสไตล์ Minimal */
        .btn-delete {
            background: transparent;
            border: none;
            color: #ff4d7d;
            font-size: 1.2rem;
            padding: 8px;
            transition: 0.3s;
            border-radius: 12px;
        }
        .btn-delete:hover {
            background: #fff0f3;
            color: #ff1f5a;
        }

        .text-muted-custom { color: #8e8e8e; font-size: 0.85rem; }
    </style>
</head>
<body>

<div class="container py-5">
    <a href="../index_ad.php" class="back-link">
        <i class="bi bi-arrow-left"></i> กลับสู่หน้า Dashboard
    </a>

    <div class="text-start mb-4">
        <h2 class="fw-bold mb-1">จัดการรีวิวสินค้า</h2>
        <p class="text-muted">ตรวจสอบและจัดการความคิดเห็นจากลูกค้า Mira ของคุณ</p>
    </div>
    

    <div class="glass-container">
        <h5 class="fw-bold mb-4" style="color: #b3365b;">รายการรีวิวทั้งหมด (<?= count($reviews) ?>)</h5>

        <?php if (empty($reviews)): ?>
            <div class="text-center py-5">
                <i class="bi bi-chat-dots fs-1" style="color: #f8a5c2;"></i>
                <p class="mt-3 text-muted">ยังไม่มีข้อมูลการรีวิวในขณะนี้</p>
            </div>
        <?php else: ?>
            <?php foreach($reviews as $rev): ?>
            <div class="review-card-item">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <?php 
                            $pic = !empty($rev['profile_img']) ? "../../register/photo/".$rev['profile_img'] : "https://ui-avatars.com/api/?name=".urlencode($rev['fullname']);
                        ?>
                        <img src="<?= $pic ?>" class="user-img">
                    </div>

                    <div class="col-md-3">
                        <h6 class="mb-1 fw-bold" style="color: #4a4a4a;"><?= htmlspecialchars($rev['fullname']) ?></h6>
                        <span class="product-badge">
                            <i class="bi bi-bag-heart me-1"></i><?= htmlspecialchars($rev['product_name']) ?>
                        </span>
                    </div>

                    <div class="col-md-5">
                        <div class="star-rating mb-1">
                            <?= str_repeat('<i class="bi bi-star-fill"></i>', $rev['rating']) ?>
                            <?= str_repeat('<i class="bi bi-star"></i>', 5 - $rev['rating']) ?>
                        </div>
                        <p class="mb-0" style="font-size: 0.95rem; color: #666;">"<?= htmlspecialchars($rev['comment']) ?>"</p>
                        <small class="text-muted-custom"><?= date('d/m/Y H:i', strtotime($rev['review_date'])) ?></small>
                    </div>

                    <div class="col text-end">
                        <a href="javascript:void(0);" 
   class="btn-delete"
   onclick="confirmDelete('<?= $rev['review_id'] ?>')">
    <i class="bi bi-trash3"></i>
</a>
                    </div>
                </div>

            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function confirmDelete(reviewId) {
    Swal.fire({
        title: 'ยืนยันการลบรีวิว?',
        text: "เมื่อลบแล้วจะไม่สามารถกู้คืนข้อมูลได้!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#b3365b', // สีชมพูเข้มตามธีมระบบ
        cancelButtonColor: '#94a3b8', // สีเทาตามธีมปุ่มย้อนกลับ
        confirmButtonText: 'ยืนยันการลบ',
        cancelButtonText: 'ยกเลิก',
        borderRadius: '20px', // ให้ขอบมนเข้ากับ glass-container
        customClass: {
            popup: 'rounded-4'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // ส่งไปที่ไฟล์เดิมพร้อม parameter delete_id ตาม Logic PHP เดิมของคุณ
            window.location.href = '?delete_id=' + reviewId;
        }
    })
}
</script>



<script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>