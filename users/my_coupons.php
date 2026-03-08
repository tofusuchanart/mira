<?php
session_start();
require_once "../config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $current_date = date('Y-m-d H:i:s');
    
    // ดึงคูปองที่ User กดเก็บไว้ (user_vouchers) 
    // โดยเชื่อมกับข้อมูลโปรโมชั่น (promotions)
    // เงื่อนไข: สถานะต้องเป็น 'unused' และโปรโมชั่นยังไม่หมดอายุ
    $stmt = $conn->prepare("
        SELECT p.*, uv.used_status, uv.collected_at 
        FROM user_vouchers uv
        JOIN promotions p ON uv.promo_id = p.promo_id
        WHERE uv.user_id = ? 
        AND uv.used_status = 'unused' 
        AND p.status = 'active'
        AND p.end_date >= ?
        ORDER BY p.end_date ASC
    ");
    $stmt->execute([$user_id, $current_date]);
    $coupons = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log($e->getMessage());
    $coupons = [];
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIRA | My Coupons</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="photo/golo.png">
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

        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 30px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(179, 54, 91, 0.05);
        }

        .mira-header {
            font-family: 'Playfair Display', serif;
            color: var(--mira-pink-dark);
        }

        /* Coupon Card Style */
        .coupon-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            display: flex;
            position: relative;
            border: none;
            transition: 0.3s;
            height: 100%;
        }

        .coupon-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(163, 74, 103, 0.15);
        }

        .coupon-left {
            background: var(--mira-pink-dark);
            color: white;
            width: 100px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 10px;
            border-right: 2px dashed var(--mira-bg);
        }

        .coupon-left::before, .coupon-left::after {
            content: '';
            position: absolute;
            left: 90px;
            width: 20px;
            height: 20px;
            background: var(--mira-bg);
            border-radius: 50%;
        }

        .coupon-left::before { top: -10px; }
        .coupon-left::after { bottom: -10px; }

        .coupon-right {
            padding: 20px;
            flex-grow: 1;
        }

        .discount-val {
            font-size: 1.8rem;
            font-weight: bold;
            line-height: 1;
        }

        .discount-unit { font-size: 0.9rem; }

        .btn-use {
            background: var(--mira-pink-soft);
            color: var(--mira-pink-dark);
            border: 1px solid var(--mira-pink-accent);
            border-radius: 50px;
            font-size: 0.8rem;
            padding: 5px 15px;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="glass-panel">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <a href="pf.php" class="text-decoration-none text-muted mb-2 d-inline-block">
                            <i class="bi bi-arrow-left"></i> กลับไปยังโปรไฟล์
                        </a>
                        <h2 class="mira-header fw-bold">โค้ดส่วนลดของฉัน</h2>
                    </div>
                    <i class="bi bi-ticket-perforated-fill fs-1" style="color: var(--mira-pink-accent);"></i>
                </div>

                <div class="row g-4">
                    <?php if (count($coupons) > 0): ?>
                        <?php foreach ($coupons as $cp): ?>
                            <div class="col-md-6">
                                <div class="coupon-card shadow-sm">
                                    <div class="coupon-left">
                                        <div class="discount-val">
                                            <?= number_format($cp['discount_value'], 0) ?>
                                        </div>
                                        <div class="discount-unit">
                                            <?= ($cp['discount_type'] == 'percentage') ? '%' : 'THB' ?>
                                        </div>
                                    </div>
                                    <div class="coupon-right">
                                        <h6 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($cp['promo_name']) ?></h6>
                                        <p class="text-muted small mb-2">
                                            ขั้นต่ำ ฿<?= number_format($cp['min_spent'], 2) ?>
                                        </p>
                                        <div class="d-flex justify-content-between align-items-end">
                                            <div class="text-danger small" style="font-size: 0.75rem;">
                                                <i class="bi bi-clock"></i> หมดเขต <?= date('d/m/Y', strtotime($cp['end_date'])) ?>
                                            </div>
                                            <a href="index_users.php" class="btn btn-use">ใช้เลย</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-5">
                            <i class="bi bi-emoji-frown display-1 text-muted"></i>
                            <p class="mt-3 text-muted">ตอนนี้ยังไม่มีโค้ดส่วนลดว่างให้ใช้งาน</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>