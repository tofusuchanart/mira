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
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="photo/golo.png">
    <style>
        /* ปรับแต่งกรอบ Banner ให้มนและมีมิติ */
#carouselExampleFade {
    border-radius: 30px; /* ความโค้งตามสไตล์ Dashboard */
    overflow: hidden;
    box-shadow: 0 15px 35px rgba(179, 54, 91, 0.1); /* เงาชมพูจางๆ */
    margin: 20px auto; /* เพิ่มระยะห่างจาก Header */
    max-width: 95%; /* ให้เห็นขอบพื้นหลังสีชมพูอ่อนเล็กน้อย */
}

/* ปรับแต่งภาพ Banner */
.carousel-item img {
    object-fit: cover;
    height: 450px; /* กำหนดความสูงให้พอดี ไม่ยาวเกินไป */
}

/* ปรับแต่งปุ่ม Previous / Next ให้เป็นวงกลมฟุ้งๆ */
.carousel-control-prev-icon,
.carousel-control-next-icon {
    background-color: rgba(255, 255, 255, 0.3); /* ขาวโปร่งแสง */
    border-radius: 50%;
    padding: 20px;
    background-size: 50%;
    transition: all 0.3s ease;
}

.carousel-control-prev:hover .carousel-control-prev-icon,
.carousel-control-next:hover .carousel-control-next-icon {
    background-color: #b3365b; /* เปลี่ยนเป็นชมพู Mira เมื่อชี้ */
    box-shadow: 0 0 15px rgba(179, 54, 91, 0.5); /* แสงฟุ้งรอบปุ่ม */
}

/* เพิ่มจุดกลมๆ (Indicators) ด้านล่าง */
.carousel-indicators [data-bs-target] {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background-color: #b3365b;
    margin: 0 5px;
}
    /* ปรับแต่งเมนูให้ดูพรีเมียมตามแบบ Dashboard */
    .navbar-nav .nav-link {
        font-size: 1.15rem;
        padding: 0.5rem 1rem !important;
        margin: 0 8px; /* เพิ่มระยะห่างระหว่างปุ่ม */
        transition: all 0.3s ease;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%; /* เตรียมทรงวงกลม */
        width: 45px;  /* กำหนดความกว้างวงกลม */
        height: 45px; /* กำหนดความสูงวงกลม */
        color: #b3365b !important; /* สีไอคอนบนพื้นดำ */
    }

    /* Effect วงกลมสีชมพูอ่อนเมื่อ Hover */
    .navbar-nav .nav-link:hover {
        background-color: #b5365c33; /* สีชมพู Mira แบบจาง 20% */
        color: #ff85a1 !important; /* สีไอคอนเมื่อ Hover ให้สว่างขึ้น */
        transform: translateY(-2px); /* ยกตัวขึ้นเล็กน้อย */
    }

    /* ปรับแต่ง Dropdown Text (Products) ให้ยังเป็นข้อความแต่ดูดี */
    .navbar-nav .nav-item.dropdown .nav-link {
        width: auto; /* ให้ความกว้างตามข้อความ */
        border-radius: 20px; /* ทรงมนยาว */
        padding: 0.5rem 1.5rem !important;
    }

    .navbar-nav .bi {
        font-size: 1.4rem;
        line-height: 1;
    }
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light" style="background-color:#FFF2f6;">
  <div class="container-fluid">

    <!-- Logo -->
    <a class="navbar-brand">
      <img src="photo/golo.png" width="70" height="50" alt="Mira">
    </a>

    <!-- Toggle -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
      data-bs-target="#navbarNavDropdown">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Menu -->
    <div class="collapse navbar-collapse" id="navbarNavDropdown">
      <ul class="navbar-nav mx-auto">

        <li class="nav-item">
    <a class="nav-link active" href="index.php?link=home" style="color: #b3365b !important;">
        <i class="bi bi-house-door fs-5"></i> 
    </a>
</li>

        <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-expanded="false" href="javascript:void(0)">
        Products
    </a>
    <ul class="dropdown-menu">
        <li>
            <a class="dropdown-item" href="index.php?link=women">
                <i class="bi bi-gender-female me-2"></i> น้ำหอมสำหรับผู้หญิง
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="index.php?link=men">
                <i class="bi bi-gender-male me-2"></i> น้ำหอมสำหรับผู้ชาย
            </a>
        </li>
    </ul>
</li>
        <li class="nav-item">
    <?php if (isset($_SESSION['user_id'])): ?>
        <a class="nav-link" href="contact_owner.php">
            <i class="bi bi-chat-dots"></i>
        </a>
    <?php else: ?>
        <a class="nav-link" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#loginAlertModal">
            <i class="bi bi-chat-dots"></i>
        </a>
    <?php endif; ?>
</li>
<li class="nav-item"><?php if (isset($_SESSION['user_id'])): ?>
    <a class="nav-link active" href="index.php?link=home" style="color: #b3365b !important;">
        <i class="bi bi-bag-heart"></i> 
    </a>
    <?php else: ?>
        <a class="nav-link" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#loginAlertModal">
            <i class="bi bi-bag-heart"></i> 
        </a>
    <?php endif; ?>
</li>

        <li class="nav-item">
    <a class="nav-link active" href="login/login.php" style="color: #b3365b !important;">
        <i class="bi bi-box-arrow-in-right"></i>
    </a>
</li>
      </ul>

    </div>
  </div>
      </nav>
      
          <!-- สิ้นสุดแบนเนอร์ -->


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


<style>
    /* ปรับแต่ง Footer ให้เข้ากับโทนในรูปภาพ */
    .footer-section {
        background-color: #fff2f6; /* สีชมพูอ่อนพาสเทลตามรูป Customer Directory */
        padding: 60px 0 30px;
        color: #444;
        border-top: 1px solid rgba(179, 54, 91, 0.1);
    }
    .footer-title {
        font-family: 'Playfair Display', serif;
        color: #b3365b; /* สีชมพูเข้มตัวเดียวกับโลโก้ในรูป */
        font-weight: bold;
        font-size: 1.25rem;
        margin-bottom: 25px;
        position: relative;
    }
    /* ขีดเส้นใต้หัวข้อเล็กๆ เพื่อความสวยงาม */
    
    .footer-contact-text {
        font-size: 1rem;
        text-decoration: none;
        color: #555;
        transition: 0.3s;
    }
    .footer-contact-text:hover {
        color: #b3365b;
    }
    /* ปรับแต่ง Icon Social ให้ดูสะอาดตา (ขาว-ชมพู) */
    .social-icons a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        background: #ffffff;
        border-radius: 12px; /* โค้งมนแบบ Card ในรูป */
        color: #b3365b;
        margin-right: 12px;
        text-decoration: none;
        box-shadow: 0 4px 10px rgba(179, 54, 91, 0.08);
        transition: all 0.3s ease;
    }
    .social-icons a:hover {
        background: #b3365b;
        color: #ffffff;
        transform: translateY(-3px);
    }
    .footer-info-box {
        background: rgba(255, 255, 255, 0.5);
        padding: 20px;
        border-radius: 15px;
        border: 1px solid #ffffff;
    }
    .copyright-text {
        font-size: 0.85rem;
        color: #888;
        border-top: 1px solid rgba(179, 54, 91, 0.1);
        padding-top: 25px;
        margin-top: 40px;
    }
</style>

<footer class="footer-section">
    <div class="container">
        <div class="row g-4">
            
            <div class="col-md-4">
                <div class="footer-info-box h-100">
                    <h6 class="footer-title">บริษัท มิรา จำกัด</h6>
                    <p class="mb-0 text-muted small">ศาลากลางจังหวัดพะเยา ถนนพหลโยธิน<br>
                    ต.บ้านต๋อม อ.เมืองพะเยา จ.พะเยา 56000</p>
                </div>
            </div>

            <div class="col-md-4 text-center"> 
    <h6 class="footer-title justify-content-center d-flex" style="font-size: 1.1rem;">Customer Support</h6> 
    
    <div class="d-flex flex-column align-items-center gap-2"> <div class="d-flex align-items-center justify-content-center w-100">
            <div class="me-2 text-white d-flex align-items-center justify-content-center" 
                 style="width:28px; height:28px; background:#b3365b; border-radius:50%; flex-shrink: 0;">
                <i class="bi bi-telephone-fill" style="font-size: 0.75rem;"></i>
            </div>
            <span class="footer-contact-text fw-bold" style="font-size: 0.9rem;">098-818-9079</span>
        </div>

        <div class="d-flex align-items-center justify-content-center w-100">
            <div class="me-2 text-white d-flex align-items-center justify-content-center" 
                 style="width:28px; height:28px; background:#b3365b; border-radius:50%; flex-shrink: 0;">
                <i class="bi bi-envelope-heart-fill" style="font-size: 0.75rem;"></i>
            </div>
            <a href="mailto:miraperfume@gmail.com" class="footer-contact-text fw-bold" 
               style="font-size: 0.9rem; text-decoration: none;">miraperfume@gmail.com</a>
        </div>

    </div>
</div>

            <div class="col-md-4 text-md-end">
                <h6 class="footer-title justify-content-md-end d-md-flex">Follow Our Beauty</h6>
                <div class="social-icons d-flex align-items-center justify-content-md-end mt-4">
                    <a href="#"><i class="bi bi-facebook"></i></a>
                    <a href="#"><i class="bi bi-line"></i></a>
                    <a href="#"><i class="bi bi-instagram"></i></a>                   
                </div>
               
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