<?php include_once "../config.php";

?>
</head>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mira</title>
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
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

<nav class="navbar navbar-expand-lg navbar-light shadow-sm" style="background-color:#FFFFFF">
  <div class="container-fluid">

    <a class="navbar-brand" href="index_users.php?link=home">
      <img src="../photo/golo.png" width="120" height="80" alt="Mira" style="object-fit: contain;">
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNavDropdown">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link active" href="index_users.php?link=home">หน้าแรก</a>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Products
          </a>
          <ul class="dropdown-menu border-0 shadow-sm">
            <li><a class="dropdown-item" href="index_users.php?link=women">น้ำหอมสำหรับผู้หญิง</a></li>
            <li><a class="dropdown-item" href="index_users.php?link=men">น้ำหอมสำหรับผู้ชาย</a></li>
          </ul>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="#">ตะกร้าสินค้า</a>
        </li>
      </ul>

      <div class="d-lg-flex align-items-center ms-auto mt-3 mt-lg-0 gap-2">
        <form class="d-flex m-0" role="search">
          <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
          <button class="btn btn-outline-success nav-btn" type="submit">Search</button>
        </form>
        <a href="../login/logout.php">
        <button class="btn btn-danger nav-btn">ออกจากระบบ</button></a>
      </div>
      
    </div>
  </div>
</nav>

<style>
  /* กำหนดให้ปุ่มมีความกว้างเท่ากัน */
  .nav-btn {
    min-width: 110px; 
    display: flex;
    justify-content: center;
    align-items: center;
  }

  /* ปรับแต่ง Dropdown ให้ดูทันสมัยขึ้น */
  .nav-link {
    font-weight: 500;
    transition: color 0.2s;
  }

  .dropdown-item:active {
    background-color: #198754;
  }

  /* ระยะห่างสำหรับมือถือ */
  @media (max-width: 991.98px) {
    .nav-btn {
      width: 100%; /* พอมือถือให้ปุ่มเต็มความกว้าง */
      margin-top: 5px;
    }
  }
</style>
      <!-- Search -->
      
          <!-- สิ้นสุดแบนเนอร์ -->
          <!-- เริ่มต้นfooter -->
           

<?php include_once "body.php"; ?>
<?php 
// ดึงข้อมูลรีวิว พร้อมชื่อผู้รีวิว (Join ตาราง reviews และ users)
try {
// ตัวอย่างคำสั่ง SQL ในหน้า index.php
$stmt_rev = $conn->prepare("SELECT r.*, u.fullname, u.profile_img FROM reviews r 
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
        <div class="mb-3">— ⚪ —</div>
        <button type="button" class="btn btn-outline-light mb-5" data-bs-toggle="modal" data-bs-target="#reviewModal">
            <i class="bi bi-pencil-square"></i> เขียนรีวิวของคุณ
        </button>
        <div class="row g-5">
            <?php if (empty($reviews)): ?>
                <p class="text-white-50">ยังไม่มีรีวิวในขณะนี้ เป็นคนแรกที่รีวิวสิ!</p>
            <?php else: ?>
                <?php foreach($reviews as $rev): ?>
                <div class="col-md-4">
                    <div class="review-card text-start shadow">
                        <div class="mb-2">
                            <span class="text-warning"><?= str_repeat('⭐', $rev['rating']) ?></span>
                            <span class="text-muted small">(<?= $rev['rating'] ?>/5)</span>
                        </div>
                        
                        <p class="review-text"><?= htmlspecialchars($rev['comment']) ?></p>
                        
                        <div class="reviewer-info">
                            <?php 
                                $user_pic = (!empty($rev['profile_img'])) 
                                            ? "photo/" . $rev['profile_img'] 
                                            : "https://ui-avatars.com/api/?name=" . urlencode($rev['fullname']) . "&background=random";
                            ?>
                            <img src="<?= $user_pic ?>" class="reviewer-img" alt="Profile">
                            
                            <div class="reviewer-name">
                                <strong><?= htmlspecialchars($rev['fullname']) ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-dark">เขียนรีวิวสินค้า</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="save_review.php" method="POST">
                <div class="modal-body text-start text-dark">
                    <div class="mb-3">
                        <label class="form-label fw-bold">คะแนนความพึงพอใจ</label>
                        <select name="rating" class="form-select" required>
                            <option value="5">5 ดาว - ดีมาก</option>
                            <option value="4">4 ดาว - ดี</option>
                            <option value="3">3 ดาว - ปานกลาง</option>
                            <option value="2">2 ดาว - พอใช้</option>
                            <option value="1">1 ดาว - ควรปรับปรุง</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">เลือกสินค้าที่ต้องการรีวิว</label>
                        <select name="product_id" class="form-select" required>
                            <?php
                            // ดึงรายชื่อสินค้ามาให้เลือก
                            $stmt_p = $conn->query("SELECT product_id, product_name FROM products");
                            while($p = $stmt_p->fetch()) {
                                echo "<option value='{$p['product_id']}'>{$p['product_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">ความคิดเห็น</label>
                        <textarea name="comment" class="form-control" rows="4" placeholder="แบ่งปันความประทับใจของคุณ..." required></textarea>
                    </div>
                    <input type="hidden" name="user_id" value="3"> 
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary">ส่งรีวิว</button>
                </div>
            </form>
        </div>
    </div>
</div>
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
</footer><footer class="mt-5 py-5 bg-white border-top">
    <div class="container text-center text-muted">
        <p>© 2024 ESXENSE Perfume. All rights reserved.</p>
    </div>
</footer>
           <!-- สิ้นสุดfooter -->
        <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
      </body>
      </html>