<?php include_once "config.php";
?>
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
            background-color: #070606; /* สีพื้นหลังเทาอ่อนตามภาพ */
            padding: 40px 0;
            color: #FFFF;
        }
        .footer-title {
            font-weight: bold;
            font-size: 1.2rem;
            margin-bottom: 15px;
        }
        .footer-contact-text {
            font-size: 1.1rem;
            text-decoration: none;
            color: #fffFFF;
        }
        .social-icons a {
            font-size: 2rem;
            color: #FFFF;
            margin-right: 15px;
            text-decoration: none;
        }
        .text-gray { color: #ffff; } /* สีเทาสำหรับหัวข้อ */
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light" style="background-color:#FFFFFF;">
  <div class="container-fluid">

    <!-- Logo -->
    <a class="navbar-brand">
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
          <a class="nav-link active" href="index.php?link=home">🏠</a>
        </li>

        <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-expanded="false" href="javascript:void(0)">
        Products
    </a>
    <ul class="dropdown-menu">
        <li><a class="dropdown-item" href="index.php?link=women">น้ำหอมสำหรับผู้หญิง</a></li>
        <li><a class="dropdown-item" href="index.php?link=men">น้ำหอมสำหรับผู้ชาย</a></li>
    </ul>
</li>
        <li class="nav-item">
  <?php if (isset($_SESSION['user_id'])): ?>
    <a class="nav-link" href="contact_owner.php">💬</a>
  <?php else: ?>
    <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#loginAlertModal">💬</a>
  <?php endif; ?>
</li>
<li class="nav-item">
  <?php if (isset($_SESSION['user_id'])): ?>
    <a class="nav-link" href="contact_owner.php">🛒</a>
  <?php else: ?>
    <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#loginAlertModal">🛒</a>
  <?php endif; ?>
</li>

        <li class="nav-item">
          <a class="nav-link" href="login/login.php">👤</a>
        </li>
      </ul>

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
        <div class="modal fade" id="loginAlertModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 20px; border: none; overflow: hidden;">
      <div class="modal-body text-center p-5">
        <div class="mb-4">
          <i class="bi bi-person-circle" style="font-size: 4rem; color: #b3365b;"></i>
        </div>
        
        <h4 class="fw-bold mb-3" style="color: #b3365b;">กรุณาเข้าสู่ระบบ</h4>
        <p class="text-muted mb-4">คุณต้องเข้าสู่ระบบก่อนจึงจะสามารถส่งข้อความถึงเจ้าของร้านได้</p>
        
        <div class="d-grid gap-2">
          <a href="login/login.php" class="btn py-2 fw-bold shadow-sm" 
             style="background-color: #b3365b; color: white; border-radius: 50px;">
             ไปที่หน้าเข้าสู่ระบบ
          </a>
          <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">
            ไว้ทีหลัง
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
      </body>
      </html>