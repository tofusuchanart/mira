<?php
session_start();
require_once "../config.php"; // ปรับ Path ตามไฟล์เชื่อมต่อ DB ของคุณ

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ดึงข้อมูลคำสั่งซื้อของ User นี้
// JOIN กับ order_items เพื่อดูจำนวนรายการ และเลือกแสดงข้อมูลล่าสุดขึ้นก่อน
try {
    $sql = "SELECT o.*, COUNT(oi.item_id) as total_items 
            FROM orders o
            LEFT JOIN order_items oi ON o.order_id = oi.order_id
            WHERE o.user_id = ?
            GROUP BY o.order_id
            ORDER BY o.order_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History | MIRA</title>
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
            letter-spacing: 1px;
        }

        .order-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 20px;
            transition: 0.3s;
            box-shadow: 0 10px 25px rgba(163, 74, 103, 0.03);
        }

        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(163, 74, 103, 0.08);
            border-color: var(--mira-pink-accent);
        }

        .status-badge {
            padding: 6px 15px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        /* Status Colors */
        .status-pending { background: #fff3cd; color: #856404; }
        .status-paid { background: #d1e7dd; color: #0f5132; }
        .status-shipped { background: #cff4fc; color: #055160; }
        .status-completed { background: #fdf5f7; color: var(--mira-pink-dark); border: 1px solid var(--mira-pink-accent); }
        .status-cancelled { background: #f8d7da; color: #842029; }

        .btn-view {
            color: var(--mira-pink-dark);
            border: 1px solid var(--mira-pink-dark);
            border-radius: 50px;
            padding: 8px 25px;
            text-decoration: none;
            font-size: 0.85rem;
            transition: 0.3s;
        }

        .btn-view:hover {
            background: var(--mira-pink-dark);
            color: white;
        }

        .nav-back {
            color: var(--mira-pink-dark);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            margin-bottom: 30px;
            font-weight: 600;
        }

        .price-text {
            color: var(--mira-pink-dark);
            font-weight: 600;
            font-size: 1.1rem;
        }

        .empty-state {
            padding: 100px 20px;
            text-align: center;
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--mira-pink-accent);
            opacity: 0.5;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <a href="index_users.php" class="nav-back">
                <i class="bi bi-chevron-left me-2"></i> กลับหน้าหลัก
            </a>

            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h2 class="mira-header fw-bold mb-0">ประวัติการสั่งซื้อ</h2>
                    <p class="text-muted small mb-0">ตรวจสอบและติดตามสถานะคำสั่งซื้อของคุณ</p>
                </div>
                <div class="text-end text-muted small">
                    คำสั่งซื้อทั้งหมด: <?= count($orders) ?> รายการ
                </div>
            </div>

            <hr style="opacity: 0.1; margin-bottom: 40px;">

            <?php if (empty($orders)): ?>
                <div class="empty-state">
                    <i class="bi bi-bag-heart mb-3 d-block"></i>
                    <h5 class="fw-bold">ยังไม่มีประวัติการสั่งซื้อ</h5>
                    <p class="text-muted">เริ่มช้อปปิ้งเพื่อสร้างความทรงจำที่ดีกับเรานะคะ</p>
                    <a href="index_users.php" class="btn btn-view mt-3">ไป shopping กันเถอะ!</a>
                </div>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order-card shadow-sm">
                        <div class="row align-items-center">
                            <div class="col-md-2 mb-3 mb-md-0">
                                <small class="text-muted d-block">Order ID</small>
                                <span class="fw-bold">#<?= str_pad($order['order_id'], 5, '0', STR_PAD_LEFT) ?></span>
                            </div>
                            
                            <div class="col-md-3 mb-3 mb-md-0">
                                <small class="text-muted d-block">วันที่สั่งซื้อ</small>
                                <span><?= date('d/m/Y H:i', strtotime($order['order_date'])) ?></span>
                            </div>

                            <div class="col-md-2 mb-3 mb-md-0">
                                <small class="text-muted d-block">สถานะ</small>
                                <span class="status-badge status-<?= $order['status'] ?>">
                                    <?php 
                                        switch($order['status']) {
                                            case 'pending': echo 'รอชำระเงิน'; break;
                                            case 'paid': echo 'ชำระเงินแล้ว'; break;
                                            case 'shipped': echo 'กำลังจัดส่ง'; break;
                                            case 'completed': echo 'สำเร็จ'; break;
                                            case 'cancelled': echo 'ยกเลิก'; break;
                                        }
                                    ?>
                                </span>
                            </div>

                            <div class="col-md-3 text-md-center mb-3 mb-md-0">
                                <small class="text-muted d-block">ยอดรวมสุทธิ</small>
                                <span class="price-text">฿<?= number_format($order['total_price'], 2) ?></span>
                                <small class="text-muted d-block" style="font-size: 0.7rem;">(<?= $order['total_items'] ?> รายการ)</small>
                            </div>

                            <div class="col-md-2 text-md-end">
                                <a href="od_details.php?id=<?= $order['order_id'] ?>" class="btn-view">
                                    ดูรายละเอียด
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>
    </div>
</div>

</body>
</html>