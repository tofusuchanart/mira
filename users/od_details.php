<?php
session_start();
require_once "../config.php"; 

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: od_htr.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = $_GET['id'];

// 1. ดึงข้อมูลสรุปของคำสั่งซื้อนี้ (เช็ค user_id เพื่อความปลอดภัย)
$stmt_order = $conn->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
$stmt_order->execute([$order_id, $user_id]);
$order = $stmt_order->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header("Location: od_htr.php");
    exit();
}

// 2. ดึงรายการสินค้าในคำสั่งซื้อนี้
$sql_items = "SELECT oi.*, p.product_name, p.image 
              FROM order_items oi 
              JOIN products p ON oi.product_id = p.product_id 
              WHERE oi.order_id = ?";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->execute([$order_id]);
$items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details #<?= $order_id ?> | MIRA</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --mira-pink-dark: #a34a67;
            --mira-pink-soft: #fdf5f7;
            --mira-pink-accent: #f8a5c2;
            --mira-bg: #fff0f5;
        }

        body {
            background-color: var(--mira-bg);
            font-family: 'Sarabun', sans-serif;
            color: #5d5d5d;
        }

        .mira-header {
            font-family: 'Playfair Display', serif;
            color: var(--mira-pink-dark);
        }

        .details-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            border: none;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(163, 74, 103, 0.05);
        }

        .order-status-bar {
            background: var(--mira-pink-dark);
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .product-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 15px;
            background: #fdf5f7;
        }

        .item-row {
            padding: 20px 0;
            border-bottom: 1px solid #eee;
        }

        .item-row:last-child { border-bottom: none; }

        .price-label {
            color: var(--mira-pink-dark);
            font-weight: 600;
        }

        .summary-box {
            background: var(--mira-pink-soft);
            border-radius: 20px;
            padding: 25px;
        }

        .nav-back {
            color: var(--mira-pink-dark);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .status-dot {
            height: 10px;
            width: 10px;
            background-color: #fff;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
            box-shadow: 0 0 8px rgba(255,255,255,0.8);
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            
            <a href="od_htr.php" class="nav-back">
                <i class="bi bi-arrow-left me-2"></i> ย้อนกลับไปประวัติการสั่งซื้อ
            </a>

            <div class="details-card shadow-sm">
                <div class="order-status-bar">
                    <div>
                        <small class="d-block opacity-75">หมายเลขคำสั่งซื้อ</small>
                        <h4 class="mb-0 fw-bold">#<?= str_pad($order['order_id'], 5, '0', STR_PAD_LEFT) ?></h4>
                    </div>
                    <div class="text-end">
                        <span class="status-dot"></span>
                        <span class="text-uppercase fw-bold" style="letter-spacing: 1px;">
                            <?php 
                                $s = $order['status'];
                                if($s == 'pending') echo "รอการตรวจสอบ";
                                elseif($s == 'paid') echo "ชำระเงินแล้ว";
                                elseif($s == 'shipped') echo "กำลังจัดส่ง";
                                elseif($s == 'completed') echo "รายการสำเร็จ";
                                else echo "ยกเลิกแล้ว";
                            ?>
                        </span>
                    </div>
                </div>

                <div class="p-4 p-md-5">
                    <h5 class="mira-header fw-bold mb-4">Items Ordered</h5>
                    
                    <?php foreach ($items as $item): ?>
                    <div class="row item-row align-items-center">
                        <div class="col-3 col-md-2">
                            <img src="../photo/<?= $item['image'] ?>" class="product-img shadow-sm" alt="product">
                        </div>
                        <div class="col-6 col-md-7">
                            <h6 class="mb-1 fw-bold"><?= htmlspecialchars($item['product_name']) ?></h6>
                            <small class="text-muted">จำนวน: <?= $item['quantity'] ?> ชิ้น</small>
                        </div>
                        <div class="col-3 col-md-3 text-end">
                            <span class="price-label">฿<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <div class="row mt-5">
                        <div class="col-md-6 mb-4">
                            <h6 class="fw-bold mb-3">ข้อมูลการจัดส่ง</h6>
                            <p class="text-muted small">
                                <i class="bi bi-calendar3 me-2"></i> สั่งซื้อเมื่อ: <?= date('d F Y (H:i)', strtotime($order['order_date'])) ?><br>
                                <i class="bi bi-credit-card me-2"></i> ชำระเงินผ่าน: โอนผ่านธนาคาร / QR Code
                            </p>
                        </div>
                        <div class="col-md-6">
                            <div class="summary-box">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>ยอดรวมสินค้า</span>
                                    <span>฿<?= number_format($order['total_price'], 2) ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>ค่าจัดส่ง</span>
                                    <span class="text-success">Free</span>
                                </div>
                                <hr style="opacity: 0.1;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold">ยอดรวมสุทธิ</span>
                                    <span class="h4 mb-0 fw-bold" style="color: var(--mira-pink-dark);">฿<?= number_format($order['total_price'], 2) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-5">
                        <p class="small text-muted">หากมีข้อสงสัยเกี่ยวกับคำสั่งซื้อ กรุณา <a href="contact.php" style="color: var(--mira-pink-accent);">ติดต่อเรา</a></p>
                        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm rounded-pill px-4">
                            <i class="bi bi-printer me-2"></i> พิมพ์ใบเสร็จ
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>