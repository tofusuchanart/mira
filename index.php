<?php include_once "config.php"; ?>
</head>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mira</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="icon" href="photo/golo.png">
    <style>
        body { font-family: 'Sarabun', sans-serif; }
        .footer-section {
            background-color: #FFFFFF; /* สีพื้นหลังเทาอ่อนตามภาพ */
            padding: 40px 0;
            color: #000;
        }
        .footer-title {
            font-weight: bold;
            font-size: 1.2rem;
            margin-bottom: 15px;
        }
        .footer-contact-text {
            font-size: 1.1rem;
            text-decoration: none;
            color: #000;
        }
        .social-icons a {
            font-size: 2rem;
            color: #000;
            margin-right: 15px;
            text-decoration: none;
        }
        .text-gray { color: #6c757d; } /* สีเทาสำหรับหัวข้อ */
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light" style="background-color:#FFFFFF">
  <div class="container-fluid">

    <!-- Logo -->
    <a class="navbar-brand" href="#">
      <img src="photo/golo.png" width="120" height="80" alt="Mira">
    </a>

    <!-- Toggle -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
      data-bs-target="#navbarNavDropdown">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Menu -->
    <div class="collapse navbar-collapse" id="navbarNavDropdown">
      <ul class="navbar-nav">

        <li class="nav-item">
          <a class="nav-link active" href="#">หน้าแรก</a>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button"
             data-bs-toggle="dropdown">
            Products
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="index.php?link=women">น้ำหอมสำหรับผู้หญิง</a></li>
            <li><a class="dropdown-item" href="perfume_formen">น้ำหอมสำหรับผู้ชาย</a></li>
            <li><a class="dropdown-item" href="#"></a></li>
          </ul>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">ตะกร้าสินค้า</a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="login/login.php">เข้าสู่ระบบ</a>
        </li>
      </ul>
<form class="d-flex ms-auto" role="search">
        <input class="form-control me-2" type="search" placeholder="Search">
        <button class="btn btn-outline-success" type="submit">Search</button>
      </form>

    </div>
  </div>
      </nav>
      <!-- Search -->
      
          <!-- สิ้นสุดแบนเนอร์ -->
          <!-- เริ่มต้นfooter -->
           

<?php include_once "body.php"; ?>





    



<?php 
// ดึงข้อมูลรีวิว พร้อมชื่อผู้รีวิว (Join ตาราง reviews และ users)
try {
    $stmt_rev = $conn->prepare("SELECT r.*, u.fullname FROM reviews r 
                                JOIN users u ON r.user_id = u.user_id 
                                ORDER BY r.review_date DESC LIMIT 3");
    $stmt_rev->execute();
    $reviews = $stmt_rev->fetchAll();
} catch(PDOException $e) {
    $reviews = []; // ป้องกัน error หากยังไม่มีข้อมูล
}
?>

<style>
    .review-section {
        background-color: #1a1a1a; /* พื้นหลังสีดำเข้ม */
        color: white;
        padding: 80px 0;
    }
    .review-title {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 50px;
    }
    .review-card {
        background: white;
        color: #333;
        border-radius: 10px;
        padding: 30px;
        position: relative;
        height: 100%;
        border: none;
    }
    .review-text {
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 40px;
        color: #555;
    }
    .reviewer-info {
        display: flex;
        align-items: center;
        position: absolute;
        bottom: -25px;
        left: 30px;
    }
    .reviewer-img {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        border: 3px solid #1a1a1a;
        object-fit: cover;
        background: #eee;
    }
    .reviewer-name {
        margin-left: 10px;
        color: white;
        font-size: 0.85rem;
        margin-top: 25px;
    }
    .review-quote {
        color: #e84c88;
        font-weight: bold;
        margin-bottom: 10px;
        display: block;
    }
</style>

<section class="review-section">
    <div class="container text-center">
        <h2 class="review-title">Social Proof & Review</h2>
        <div class="mb-5">— ⚪ —</div>

        <div class="row g-5">
            <?php if (empty($reviews)): ?>
                
            <?php else: ?>
                <?php foreach($reviews as $rev): ?>
                <div class="col-md-4">
                    <div class="review-card text-start shadow">
                        <span class="review-quote">Rating: <?= str_repeat('⭐', $rev['rating']) ?></span>
                        <p class="review-text"><?= htmlspecialchars($rev['comment']) ?></p>
                        <div class="reviewer-info">
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($rev['fullname']) ?>" class="reviewer-img">
                            <div class="reviewer-name"><?= htmlspecialchars($rev['fullname']) ?></div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>
<footer class="footer-section">
    <div class="container">
        <div class="row align-items-start">
            
            <div class="col-md-4 mb-4 mb-md-0">
                <h5 class="fw-bold">บริษัท มิรา จำกัด</h5>
                <p class="mb-0">ศาลากลางจังหวัดพะเยา ถนนพหลโยธิน</p>
                <p>ต.บ้านต๋อม อ.เมืองพะเยา จ.พะเยา 56000</p>
            </div>

            <div class="col-md-5 mb-4 mb-md-0">
                <h5 class="text-gray fw-bold mb-3">Feedback & Question</h5>
                <div class="d-flex flex-column gap-2">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-telephone-fill me-2 fs-4"></i>
                        <span class="footer-contact-text fw-bold">098-818-9079</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-envelope-fill me-2 fs-4"></i>
                        <a href="mailto:miraperfume@gmail.com" class="footer-contact-text fw-bold">miraperfume@gmail.com</a>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <h5 class="text-gray fw-bold mb-3">Follow Us</h5>
                <div class="social-icons d-flex align-items-center">
                    <a href="#"><i class="bi bi-facebook"></i></a>
                    <a href="#"><i class="bi bi-line"></i></a>
                    <a href="#"><i class="bi bi-instagram"></i></a>
                    <a href="#"><i class="bi bi-youtube"></i></a>
                </div>
            </div>

        </div>
    </div>
</footer>
           <!-- สิ้นสุดfooter -->
        <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
      </body>
      </html>