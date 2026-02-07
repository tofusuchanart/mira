<?php include_once "../config.php";
session_start();
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
    .btn-logout-mira {
        border: 1px solid #D65A8D;
        color: #D65A8D;
        background-color: transparent;
        border-radius: 50px; /* ทรงมนยาวแบบในรูป */
        padding: 8px 30px;
        font-weight: 500;
        transition: all 0.3s;
    }

    .btn-logout-mira:hover {
        background-color: #D65A8D;
        color: white;
        box-shadow: 0 4px 12px rgba(214, 90, 141, 0.2);
    }
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light shadow-sm" style="background-color:#FFF2f6">
  <div class="container-fluid">

    <a class="navbar-brand">
      <img src="../photo/golo.png" width="70" height="50" alt="Mira">
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
    <a class="nav-link active" href="index_users.php" style="color: #b3365b !important;">
        <i class="bi bi-house-door fs-5"></i> 
    </a>
</li>

        <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-expanded="false" href="javascript:void(0)">
        Products
    </a>
    <ul class="dropdown-menu">
        <li>
            <a class="dropdown-item" href="index_users.php?link=women">
                <i class="bi bi-gender-female me-2"></i> น้ำหอมสำหรับผู้หญิง
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="index_users.php?link=men">
                <i class="bi bi-gender-male me-2"></i> น้ำหอมสำหรับผู้ชาย
            </a>
        </li>
    </ul>
</li>


<style>
    /* ปรับแต่งไอคอน Navbar ให้เป็นโทนชมพู MIRA */
    .mira-nav-icon {
        color: #a34a67 !important; /* สีชมพูเข้มตามรูป Customer Directory */
        font-size: 1.4rem; /* ปรับขนาดไอคอนให้พอดี */
        padding: 8px 12px !important;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* เอฟเฟกต์ตอนเอาเมาส์ไปชี้ */
    .mira-nav-icon:hover {
        color: #f8a5c2 !important; /* ชมพูอ่อนลงเล็กน้อยเมื่อ hover */
        transform: translateY(-2px); /* ลอยขึ้นนิดนึงดูน่ารัก */
    }

    /* ปรับให้แสดงผลเรียงกันสวยๆ ใน Navbar */
    .nav-item {
        display: flex;
        align-items: center;
    }
</style>

    <a class="nav-link mira-nav-icon" href="edit/edit.php">
        <i class="bi bi-bag-heart"></i> </a>
</li>

<li class="nav-item">
    <a class="nav-link mira-nav-icon" href="mes/mes.php">
        <i class="bi bi-chat-dots"></i>
    </a>
</li>

<li class="nav-item">
    <a class="nav-link mira-nav-icon" href="pf.php">
        <i class="bi bi-person-circle"></i>
    </a>
</li>
      </ul>

      <div class="d-lg-flex align-items-center ms-auto mt-3 mt-lg-0 gap-2">
       
        <a href="../login/logout.php">
        <button class="btn btn-logout-mira">ออกจากระบบ</button></a>
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


<div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header text-white border-0" style="background: linear-gradient(45deg, #f8a5c2, #f78fb3);">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>แบ่งปันประสบการณ์ของคุณ</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="save_review.php" method="POST">
                <div class="modal-body p-4 text-start text-dark bg-light">
                    
                    <div class="text-center mb-4">
                        <div class="position-relative d-inline-block">
                            <?php 
                                // ดึงรูปโปรไฟล์จาก Session ถ้าไม่มีให้ใช้รูป Default หรือ UI Avatars
                                $profile_display = !empty($_SESSION['profile_img']) ? "photo/".$_SESSION['profile_img'] : "https://ui-avatars.com/api/?name=".urlencode($_SESSION['fullname'])."&background=f8a5c2&color=fff";
                            ?>
                            <img src="<?php echo $profile_display; ?>" 
                                 class="rounded-circle shadow-sm border border-3 border-white" 
                                 style="width: 80px; height: 80px; object-fit: cover;">
                            <span class="position-absolute bottom-0 end-0 badge rounded-pill bg-success border border-2 border-white">
                                <i class="bi bi-check"></i>
                            </span>
                        </div>
                        <h6 class="mt-2 fw-bold mb-0"><?php echo $_SESSION['fullname']; ?></h6>
                        <small class="text-muted">คุณกำลังเขียนรีวิวในชื่อนี้</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">คะแนนความพึงพอใจ</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-warning"><i class="bi bi-star-fill"></i></span>
                            <select name="rating" class="form-select border-start-0" required>
                                <option value="5">5 ดาว - ประทับใจที่สุด</option>
                                <option value="4">4 ดาว - ดีมาก</option>
                                <option value="3">3 ดาว - ปานกลาง</option>
                                <option value="2">2 ดาว - พอใช้</option>
                                <option value="1">1 ดาว - ควรปรับปรุง</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">สินค้าที่ต้องการรีวิว</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-primary"><i class="bi bi-bag-heart-fill"></i></span>
                            <select name="product_id" class="form-select border-start-0" required>
                                <?php
                                $stmt_p = $conn->query("SELECT product_id, product_name FROM products ORDER BY product_name ASC");
                                while($p = $stmt_p->fetch()) {
                                    echo "<option value='{$p['product_id']}'>{$p['product_name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">ความรู้สึกของคุณ</label>
                        <textarea name="comment" class="form-control" rows="4" 
                                  style="border-radius: 12px; resize: none;" 
                                  placeholder="เขียนบอกเราหน่อยว่าสินค้าชิ้นนี้ดียังไง..." required></textarea>
                    </div>

                    <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                </div>

                <div class="modal-footer border-0 bg-light p-3">
                    <button type="button" class="btn btn-outline-secondary px-4 border-0" data-bs-dismiss="modal">ไว้วันหลัง</button>
                    <button type="submit" class="btn text-white px-4 shadow-sm" 
                            style="background: #f8a5c2; border-radius: 10px; transition: 0.3s;">
                        ส่งรีวิวเลย <i class="bi bi-send-fill ms-1"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
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
        <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
      </body>
      </html>