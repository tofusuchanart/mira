<?php 
include_once "../config.php";
if (!$conn) { die("Database Connection Failed"); }
session_start();
$current_user_id = $_SESSION['user_id'] ?? 0;

if ($current_user_id > 0) {
    // เมื่อผู้ใช้เปิดหน้านี้ ให้ปรับสถานะข้อความ... ให้เป็น "อ่านแล้ว (1)"
    $sql_update_read = "UPDATE contact_messages 
                        SET is_read = 1 
                        WHERE user_id = ? 
                        AND admin_reply IS NOT NULL 
                        AND is_read = 0";
    $stmt_update = $conn->prepare($sql_update_read);
    $stmt_update->execute([$current_user_id]);
}
// 1. กำหนดค่า User ID จาก Session
$user_id = $_SESSION['user_id'] ?? 0;
$current_user_id = $user_id; // ใช้ตัวแปรเดียวกันเพื่อความไม่งง

// 2. ดึงโปรโมชั่นที่ยังไม่หมดอายุและ User ยังไม่ได้เก็บ
$sql_promo = "SELECT p.*, UNIX_TIMESTAMP(p.end_date) AS end_ts 
              FROM promotions p
              LEFT JOIN user_vouchers uv ON p.promo_id = uv.promo_id AND uv.user_id = ?
              WHERE p.status = 'active' 
              AND p.end_date >= NOW() 
              AND uv.uv_id IS NULL 
              ORDER BY p.promo_id DESC";
$stmt_promo = $conn->prepare($sql_promo);
$stmt_promo->execute([$user_id]);
$active_promos = $stmt_promo->fetchAll();

// 3. ดึงรีวิวทั้งหมด
try {
    $stmt_all_reviews = $conn->prepare("
        SELECT r.*, u.fullname, p.product_name, p.price, p.image AS product_main_img
        FROM reviews r 
        JOIN users u ON r.user_id = u.user_id 
        JOIN products p ON r.product_id = p.product_id 
        ORDER BY r.review_date DESC
    ");
    $stmt_all_reviews->execute();
    $all_reviews = $stmt_all_reviews->fetchAll();
} catch (PDOException $e) {
    $all_reviews = [];
}

// 4. นับจำนวนสินค้าในตะกร้า
$cart_count = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $cart_count = array_sum($_SESSION['cart']); 
}

// 5. นับข้อความแชทที่ยังไม่ได้อ่าน
// 5. นับข้อความแชทที่ยังไม่ได้อ่าน
// 5. นับข้อความแชทที่ยังไม่ได้อ่าน
$sql_unread_chat = "SELECT COUNT(*) as unread_count FROM contact_messages 
                    WHERE user_id = ? 
                    AND is_read = 0 
                    AND admin_reply IS NOT NULL"; // นับทุกอย่างที่แอดมินตอบและยังไม่ได้อ่าน
$stmt_chat = $conn->prepare($sql_unread_chat);
$stmt_chat->execute([$current_user_id]);
$row_chat = $stmt_chat->fetch(PDO::FETCH_ASSOC);
$unread_chats = $row_chat['unread_count'] ?? 0;
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
        .cart-badge {
        position: absolute;
        top: -2px;      /* ปรับขึ้นลงตามความเหมาะสม */
        right: -2px;    /* ปรับซ้ายขวาตามความเหมาะสม */
        background-color: #ff4757; /* สีแดงสด */
        color: white;
        font-size: 10px;
        font-weight: bold;
        border-radius: 50%;
        min-width: 18px;
        height: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #FFF2f6; /* ขอบสีเดียวกับ Navbar เพื่อให้ดูมีมิติ */
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    /* ปรับ nav-link ให้รองรับ position absolute ของ badge */
    .mira-nav-icon {
        position: relative; 
    }
        /* ปรับแต่งกรอบ Banner ให้มนและมีมิติ */
        #carouselExampleFade {
            border-radius: 30px;
            /* ความโค้งตามสไตล์ Dashboard */
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(179, 54, 91, 0.1);
            /* เงาชมพูจางๆ */
            margin: 20px auto;
            /* เพิ่มระยะห่างจาก Header */
            max-width: 95%;
            /* ให้เห็นขอบพื้นหลังสีชมพูอ่อนเล็กน้อย */
        }

        /* ปรับแต่งภาพ Banner */
        .carousel-item img {
            object-fit: cover;
            height: 450px;
            /* กำหนดความสูงให้พอดี ไม่ยาวเกินไป */
        }

        /* ปรับแต่งปุ่ม Previous / Next ให้เป็นวงกลมฟุ้งๆ */
        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            background-color: rgba(255, 255, 255, 0.3);
            /* ขาวโปร่งแสง */
            border-radius: 50%;
            padding: 20px;
            background-size: 50%;
            transition: all 0.3s ease;
        }

        .carousel-control-prev:hover .carousel-control-prev-icon,
        .carousel-control-next:hover .carousel-control-next-icon {
            background-color: #b3365b;
            /* เปลี่ยนเป็นชมพู Mira เมื่อชี้ */
            box-shadow: 0 0 15px rgba(179, 54, 91, 0.5);
            /* แสงฟุ้งรอบปุ่ม */
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
            margin: 0 8px;
            /* เพิ่มระยะห่างระหว่างปุ่ม */
            transition: all 0.3s ease;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            /* เตรียมทรงวงกลม */
            width: 45px;
            /* กำหนดความกว้างวงกลม */
            height: 45px;
            /* กำหนดความสูงวงกลม */
            color: #b3365b !important;
            /* สีไอคอนบนพื้นดำ */
        }

        /* Effect วงกลมสีชมพูอ่อนเมื่อ Hover */
        .navbar-nav .nav-link:hover {
            background-color: #b5365c33;
            /* สีชมพู Mira แบบจาง 20% */
            color: #ff85a1 !important;
            /* สีไอคอนเมื่อ Hover ให้สว่างขึ้น */
            transform: translateY(-2px);
            /* ยกตัวขึ้นเล็กน้อย */
        }

        /* ปรับแต่ง Dropdown Text (Products) ให้ยังเป็นข้อความแต่ดูดี */
        .navbar-nav .nav-item.dropdown .nav-link {
            width: auto;
            /* ให้ความกว้างตามข้อความ */
            border-radius: 20px;
            /* ทรงมนยาว */
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
            border-radius: 50px;
            /* ทรงมนยาวแบบในรูป */
            padding: 8px 30px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-logout-mira:hover {
            background-color: #D65A8D;
            color: white;
            box-shadow: 0 4px 12px rgba(214, 90, 141, 0.2);
        }

        .mira-voucher {
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #f8a5c2;
            transition: 0.3s;
        }

        .voucher-left {
            background: linear-gradient(135deg, #b3365b, #f78fb3);
            min-width: 80px;
            position: relative;
        }

        .voucher-left::after {
            content: "";
            position: absolute;
            right: -5px;
            top: 10%;
            height: 80%;
            border-right: 2px dashed rgba(255, 255, 255, 0.5);
        }

        .btn-collect-mira {
            background-color: #b3365b;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: bold;
            transition: 0.3s;
        }

        .btn-collect-mira:hover {
            background-color: #8e2a48;
            transform: scale(1.02);
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-light shadow-sm" style="background-color:#FFF2f6">
        <div class="container-fluid">

            <a class="navbar-brand">
                <img src="../photo/golo.png" width="70" height="50" alt="Mira">
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNavDropdown">

                <ul class="navbar-nav mx-auto align-items-center">

                    <li class="nav-item">
                        <a class="nav-link active" href="index_users.php" style="color: #b3365b !important;">
                            <i class="bi bi-house-door fs-5"></i>
                        </a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-expanded="false" href="javascript:void(0)">
                            Products
                        </a>
                        <ul class="dropdown-menu border-0 shadow-sm">
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



                    <li class="nav-item">
        <a class="nav-link mira-nav-icon" href="mes/chat.php">
            <i class="bi bi-chat-dots"></i>
            <?php if ($unread_chats > 0): ?>
                <span class="cart-badge" style="background-color: #ff4757;">
                    <?= $unread_chats ?>
                </span>
            <?php endif; ?>
        </a>
    </li>
                    <li class="nav-item">
    <a class="nav-link mira-nav-icon" href="cart/mycart.php">
        <i class="bi bi-bag-heart"></i>
        <?php if ($cart_count > 0): ?>
            <span class="cart-badge">
                <?php echo $cart_count; ?>
            </span>
        <?php endif; ?>
    </a>
</li>


       

                    <li class="nav-item">
                        <a class="nav-link mira-nav-icon" href="pf.php">
                            <i class="bi bi-person-circle"></i>
                        </a>
                    </li>
                </ul>

                <div class="d-lg-flex align-items-center mt-3 mt-lg-0">
                    <a href="../login/logout.php" class="text-decoration-none">
                        <button class="btn btn-logout-mira">ออกจากระบบ</button>
                    </a>
                </div>

            </div>
        </div>
    </nav>

    <style>
        /* CSS เดิมของคุณ */
        .mira-nav-icon {
            color: #a34a67 !important;
            font-size: 1.4rem;
            padding: 8px 12px !important;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .mira-nav-icon:hover {
            color: #f8a5c2 !important;
            transform: translateY(-2px);
        }

        /* ตกแต่งปุ่ม Logout เพิ่มเติมเพื่อให้เข้ากับธีม */
        .btn-logout-mira {
            background-color: #b3365b;
            color: white;
            border-radius: 50px;
            padding: 8px 20px;
            border: none;
            transition: 0.3s;
        }

        .btn-logout-mira:hover {
            background-color: #a34a67;
            color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
    </style>

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
                width: 100%;
                /* พอมือถือให้ปุ่มเต็มความกว้าง */
                margin-top: 5px;
            }
        }

        .mira-voucher {
            border-radius: 15px;
            overflow: hidden;
            border: 1px solid rgba(179, 54, 91, 0.1);
            transition: transform 0.3s ease;
        }

        .mira-voucher:hover {
            transform: translateY(-5px);
        }

        .voucher-left {
            background-color: #b3365b;
            position: relative;
            min-width: 80px;
        }

        /* รอยปรุตรงกลางบัตร */
        .voucher-left::after {
            content: "";
            position: absolute;
            right: -5px;
            top: 10%;
            height: 80%;
            border-right: 2px dashed #f8a5c2;
        }

        .btn-collect-voucher {
            background-color: #fff2f6;
            color: #b3365b;
            border: 1px solid #f8a5c2;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-collect-voucher:hover {
            background-color: #b3365b;
            color: white;
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
    } catch (PDOException $e) {
        $reviews = []; // ป้องกัน error หากยังไม่มีข้อมูล
    }
    ?>

    <style>
        .review-section {
            background-color: #1a1a1a;
            /* พื้นหลังสีดำเข้ม */
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
<style>
    /* ตกแต่งแถบเลื่อนให้เข้ากับธีมมืด */
    .review-scroll-container::-webkit-scrollbar { width: 5px; }
    .review-scroll-container::-webkit-scrollbar-track { background: #1a1a1a; }
    .review-scroll-container::-webkit-scrollbar-thumb { background: #b3365b; border-radius: 10px; }
    
    .review-card-mini {
        background: #1e1e1e;
        border-radius: 12px;
        border: 1px solid #333;
        transition: 0.3s;
    }
    .review-card-mini:hover { border-color: #f8a5c2; }
</style>

<section class="review-section py-5" style="background: #121212; color: #ffffff;">
    <div class="container">
        <div class="text-start mb-4 d-flex justify-content-between align-items-end">
            <div>
                <h2 class="fw-bold m-0" style="color: #f8a5c2; font-size: 1.5rem;">CUSTOMER REVIEWS</h2>
                <p class="text-muted small m-0">เลื่อนเพื่ออ่านรีวิวทั้งหมด</p>
            </div>
            <div class="text-warning" style="font-size: 0.8rem;">
                <i class="bi bi-star-fill"></i> รีวิวเฉลี่ย 5.0
            </div>
        </div>

        <div class="review-scroll-container" style="max-height: 450px; overflow-y: auto; overflow-x: hidden; padding-right: 8px;">
            <div class="row g-2">
                <?php foreach ($all_reviews as $rev): ?>
                    <div class="col-12">
                        <div class="review-card-mini p-2 px-3">
                            <div class="d-flex align-items-center gap-3">
                                <img src="../photo/<?= $rev['product_main_img'] ?>" 
                                     style="width: 40px; height: 40px; object-fit: cover; border-radius: 6px;" 
                                     alt="product">
                                
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <span class="fw-bold" style="font-size: 0.85rem; color: #f8a5c2;">
                                                <?= htmlspecialchars($rev['product_name']) ?>
                                            </span>
                                            <span class="text-white-50 ms-2" style="font-size: 0.75rem;">
                                                ฿<?= number_format($rev['price'], 0) ?>
                                            </span>
                                        </div>
                                        <div class="text-warning" style="font-size: 0.65rem;">
                                            <?= str_repeat('<i class="bi bi-star-fill"></i>', $rev['rating']) ?>
                                        </div>
                                    </div>

                                    <p class="text-light m-0" style="font-size: 0.85rem; opacity: 0.85;">
                                        "<?php 
                                            $bad_words = ["มึง", "กู", "ควย", "เย็ด", "สัส", "เหี้ย", "ส้นตีน", "หี", "จิ๋ม", "หำ", "เงี่ยน", "เสียว", "แตกใน", "เย็ดกัน", "โดนเย็ด", "โดนแตก", "โดนเสียว", "โดนเงี่ยน", "โดนหี", "โดนหำ", "โดนจิ๋ม", "โดนควย", "โดนกู", "โดนมึง", "โดนสัส", "โดนเหี้ย", "โดนส้นตีน", "โดนไอ้", "อี", "แม่ง", "เวรเอ๊ย", "ห่วยแตกชิบ", "ควาย", "บัดซบ", "ชั่ว", "เลว", "โง่", "ปัญญาอ่อน", "เหี้ยไร้สาระ", "ส้นตีนไร้สมอง", "ไอ้ควาย", "ไอ้บัดซบ", "ไอ้ชั่ว", "ไอ้เลว", "ไอ้โง่", "ไอ้ปัญญาอ่อน", "โกง", "หลอกลวง", "ขี้โกง", "ขี้หลอก", "ขี้ขโมย", "ขี้เมา", "ขี้เหล้า", "ขี้เถ้า", "ขี้ตั๊ว", "ขี้ตั๊วแตก", "ขี้ตั๊วแตกชิบหาย", "ขี้ตั๊วแตกชิบหายโคตรๆ", "ขี้ตั๊วแตกชิบหายโคตรๆเลย", "ขี้ตั๊วแตกชิบหายโคตรๆมาก", "ขี้ตั๊วแตกชิบหายโคตรๆสุดๆ", "ขี้ตั๊วแตกชิบหายโคตรๆที่สุด", "ขี้ตั๊วแตกชิบหายโคตรๆจริงๆ", "ขี้ตั๊วแตกชิบหายโคตรๆจริงๆเลย", "ขี้ตั๊วแตกชิบหายโคตรๆจริงๆมาก", "ขี้ตั๊วแตกชิบหายโคตรๆจริงๆสุดๆ", "ขี้ตั๊วแตกชิบหายโคตรๆจริงๆที่สุด"];
                                            echo htmlspecialchars(str_ireplace($bad_words, "***", $rev['comment']));
                                        ?>"
                                    </p>

                                    <div class="d-flex justify-content-between mt-1">
                                        <small style="font-size: 0.7rem; color: #666;">โดย: <?= htmlspecialchars($rev['fullname']) ?></small>
                                        <small style="font-size: 0.65rem; color: #666;"><?= date('d/m/Y', strtotime($rev['review_date'])) ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
    <style>
        /* ปรับแต่ง Footer ให้เข้ากับโทนในรูปภาพ */
        .footer-section {
            background-color: #fff2f6;
            /* สีชมพูอ่อนพาสเทลตามรูป Customer Directory */
            padding: 60px 0 30px;
            color: #444;
            border-top: 1px solid rgba(179, 54, 91, 0.1);
        }

        .footer-title {
            font-family: 'Playfair Display', serif;
            color: #b3365b;
            /* สีชมพูเข้มตัวเดียวกับโลโก้ในรูป */
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
            border-radius: 12px;
            /* โค้งมนแบบ Card ในรูป */
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





        /* สไตล์พื้นฐานของ Social Icons */
        .social-icons a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 45px;
            height: 45px;
            margin-left: 15px;
            /* เว้นระยะห่างระหว่างไอคอน */
            background-color: #ffffff;
            /* พื้นหลังสีขาว */
            color: #b3365b;
            /* สีชมพู MIRA */
            border-radius: 50%;
            /* ทรงกลม */
            text-decoration: none;
            font-size: 1.2rem;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            /* อนิเมชั่นแบบเด้งนุ่มนวล */
            box-shadow: 0 4px 15px rgba(179, 54, 91, 0.1);
            /* เงาบางๆ */
        }

        /* Effect เมื่อเอาเมาส์ไปชี้ (Hover) */
        .social-icons a:hover {
            color: #ffffff;
            /* เปลี่ยนไอคอนเป็นสีขาว */
            transform: translateY(-5px) scale(1.1);
            /* ลอยขึ้นและขยายใหญ่ขึ้นเล็กน้อย */
            box-shadow: 0 8px 20px rgba(179, 54, 91, 0.2);
            /* เงาเข้มขึ้น */
        }

        /* แยกสีตามแบรนด์เมื่อ Hover (ตัวเลือกเสริมเพื่อความพรีเมียม) */
        .social-icons a:hover .bi-facebook {
            color: #ffffff;
        }

        .social-icons a:hover:has(.bi-facebook) {
            background-color: #1877F2;
            /* สีฟ้า Facebook */
        }

        .social-icons a:hover:has(.bi-line) {
            background-color: #06C755;
            /* สีเขียว Line */
        }

        .social-icons a:hover:has(.bi-instagram) {
            background: radial-gradient(circle at 30% 107%, #fdf497 0%, #fdf497 5%, #fd5949 45%, #d6249f 60%, #285AEB 90%);
            /* สีรุ้ง Instagram */
        }

        /* สำหรับมือถือ ให้ระยะห่างพอดี */
        @media (max-width: 768px) {
            .social-icons {
                justify-content: center !important;
                margin-top: 20px;
            }

            .social-icons a {
                margin: 0 10px;
            }
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

                    <div class="d-flex flex-column align-items-center gap-2">
                        <div class="d-flex align-items-center justify-content-center w-100">
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
                    <h6 class="footer-title justify-content-md-end d-md-flex">Follow Our Beauty & Contact us</h6>
                    <div class="social-icons d-flex align-items-center justify-content-md-end mt-4">
                        <a href="https://www.facebook.com/profile.php?id=61587653476633" target="_blank"><i class="bi bi-facebook"></i></a>
                        <a href="https://line.me/" target="_blank"><i class="bi bi-line"></i></a>
                        <a href="https://www.instagram.com/accounts/login" target="_blank"><i class="bi bi-instagram"></i></a>
                    </div>

                </div>

            </div>


        </div>
        </div>
    </footer>
    <!-- สิ้นสุดfooter -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// --- ระบบนับถอยหลัง ---
function updateCountdowns() {
    const timers = document.querySelectorAll('.countdown');
    timers.forEach(timer => {
        const endTime = parseInt(timer.getAttribute('data-time')) * 1000;
        const now = new Date().getTime();
        const diff = endTime - now;

        if (diff <= 0) {
            timer.innerHTML = "หมดเวลา";
            return;
        }

        const hours = Math.floor((diff / (1000 * 60 * 60)));
        const mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const secs = Math.floor((diff % (1000 * 60)) / 1000);

        timer.innerHTML = `${hours.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    });
}
setInterval(updateCountdowns, 1000); // อัปเดตทุกวินาที

// --- ระบบเก็บส่วนลดแล้วหายไป ---
function collectVoucher(promoId) {
    const card = document.getElementById('promo-card-' + promoId);
    
    // ส่งข้อมูลไปที่ PHP
    fetch('collect_voucher.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'promo_id=' + promoId
    })
    .then(response => response.json())
    .then(data => {
        if(data.status === 'success') {
            // สั่งทำ Animation ค่อยๆ หายไป
            card.style.transition = "all 0.5s ease";
            card.style.opacity = "0";
            card.style.transform = "scale(0.8)";
            
            setTimeout(() => {
                card.remove(); // ลบออกจาหน้าเว็บ
            }, 500);

            Swal.fire({
                icon: 'success',
                title: 'เก็บแล้ว!',
                text: 'ส่วนลดนี้อยู่ในบัญชีของคุณแล้ว',
                showConfirmButton: false,
                timer: 1500
            });
        } else {
            Swal.fire({ icon: 'error', title: 'โอ๊ะ!', text: data.message });
        }
    });
}
</script>
</body>

</html>