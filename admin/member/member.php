<?php
session_start();
require_once "../../config.php";

$newToday = $conn->query("SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE() AND role = 'customer'")->fetchColumn();

// 2. ลูกค้าใหม่เดือนนี้
$newMonth = $conn->query("SELECT COUNT(*) FROM users WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) AND role = 'customer'")->fetchColumn();

// 3. ลูกค้าประจำ (ซื้อซ้ำมากกว่า 1 ครั้ง)
$loyalCustomers = $conn->query("SELECT COUNT(*) FROM (SELECT user_id FROM orders GROUP BY user_id HAVING COUNT(order_id) > 1) as loyal")->fetchColumn();

// 4. รายชื่อลูกค้าและยอดซื้อรวม (ดึงข้อมูลจาก users และ orders)
$customers = $conn->query("SELECT u.*, 
                           COALESCE(SUM(o.total_price), 0) as total_spent, 
                           COUNT(o.order_id) as order_count 
                           FROM users u 
                           LEFT JOIN orders o ON u.user_id = o.user_id 
                           WHERE u.role = 'customer'
                           GROUP BY u.user_id 
                           ORDER BY total_spent DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>MIRA | Membership Insights</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body {
            background: linear-gradient(rgba(255, 235, 240, 0.8), rgba(255, 235, 240, 0.8)), 
                        url('https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=1350&q=80');
            background-size: cover; background-attachment: fixed;
            font-family: 'Sarabun', sans-serif; color: #4a4a4a;
        }

        .mira-title { font-family: 'Playfair Display', serif; color: #b3365b; letter-spacing: 1px; }

        /* Stat Card แบบหรูหรา */
        .stat-card {
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 20px;
            padding: 25px;
            transition: 0.3s;
            box-shadow: 0 10px 25px rgba(248, 165, 194, 0.1);
        }
        .stat-card:hover { transform: translateY(-5px); background: white; }
        .stat-icon { width: 50px; height: 50px; background: #f8a5c2; color: white; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 15px; }

        /* Table Style */
        .glass-panel {
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 25px;
            padding: 30px;
        }
        .user-avatar { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; border: 2px solid white; box-shadow: 0 3px 10px rgba(0,0,0,0.1); }
        
        .badge-premium { background: linear-gradient(45deg, #d4af37, #f1c40f); color: white; border-radius: 50px; padding: 5px 15px; font-size: 0.7rem; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="mira-title fw-bold">Customer Directory</h2>
            <p class="text-muted">บริหารจัดการข้อมูลสมาชิกและสถิติการซื้อ</p>
        </div>
        <a href="../index_ad.php" class="btn btn-outline-dark rounded-pill px-4">
            <i class="bi bi-grid-1x2 me-2"></i>Dashboard
        </a>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-person-plus"></i></div>
                <h6 class="text-muted small">ลูกค้าใหม่วันนี้</h6>
                <h3 class="fw-bold mb-0"><?= number_format($newToday) ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background: #a29bfe;"><i class="bi bi-calendar-check"></i></div>
                <h6 class="text-muted small">ลูกค้าใหม่เดือนนี้</h6>
                <h3 class="fw-bold mb-0"><?= number_format($newMonth) ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background: #fab1a0;"><i class="bi bi-arrow-repeat"></i></div>
                <h6 class="text-muted small">ลูกค้าประจำ (ซื้อซ้ำ)</h6>
                <h3 class="fw-bold mb-0"><?= number_format($loyalCustomers) ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background: #d4af37;"><i class="bi bi-gem"></i></div>
                <h6 class="text-muted small">สมาชิกระดับ Gold</h6>
                <h3 class="fw-bold mb-0">Premium</h3>
            </div>
        </div>
    </div>

    <div class="glass-panel">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-bold m-0 text-pink-mira">รายชื่อสมาชิกทั้งหมด</h5>
            <div class="input-group style="width: 250px;">
                <input type="text" class="form-control rounded-pill border-0 shadow-sm px-3" placeholder="ค้นหาชื่อลูกค้า...">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="text-muted small">
                    <tr>
                        <th>สมาชิกระดับ</th>
                        <th>ข้อมูลลูกค้า</th>
                        <th>วันที่สมัคร</th>
                        <th class="text-center">จำนวนออเดอร์</th>
                        <th class="text-end">ยอดซื้อรวมทั้งหมด</th>
                        
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $cus): 
                        $pic = !empty($cus['profile_img']) ? "../../register/photo/".$cus['profile_img'] : "https://ui-avatars.com/api/?name=".urlencode($cus['fullname'])."&background=f8a5c2&color=fff";
                    ?>
                    <tr>
                        <td>
                            <?php if($cus['total_spent'] > 5000): ?>
                                <span class="badge-premium shadow-sm">TOP SPENDER</span>
                            <?php else: ?>
                                <span class="badge bg-light text-dark rounded-pill shadow-sm" style="font-size: 0.7rem;">GENERAL</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="<?= $pic ?>" class="user-avatar me-3">
                                <div>
                                    <h6 class="mb-0 fw-bold"><?= htmlspecialchars($cus['fullname']) ?></h6>
                                    <small class="text-muted"><?= $cus['email'] ?></small>
                                </div>
                            </div>
                        </td>
                        <td class="small text-muted"><?= $cus['created_at'] ?></td>
                        <td class="text-center fw-bold"><?= $cus['order_count'] ?></td>
                        <td class="text-end fw-bold text-dark">฿<?= number_format($cus['total_spent'], 2) ?></td>
                        
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>