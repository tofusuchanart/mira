<?php
session_start();
require_once "../../config.php";
/** @var PDO $conn */
$stmt = $conn->query("SELECT COUNT(*) FROM users");
$user_count = ($stmt) ? $stmt->fetchColumn() : 0;
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
    /* นำเข้าฟอนต์ Sarabun เพื่อความสม่ำเสมอ */
    @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap');

    body {
        background-color: #fdf2f4; /* สีพื้นหลังชมพูอ่อนมากแบบในรูปตัวอย่าง */
        font-family: 'Sarabun', sans-serif;
        color: #4a4a4a;
    }

    /* สไตล์หัวข้อตามรูปตัวอย่าง */
    .mira-title { 
        color: #b3365b; 
        font-weight: 700;
        letter-spacing: -0.5px;
    }

    /* ปุ่มกลับ Dashboard สไตล์ Minimal */
    .back-link {
        text-decoration: none;
        color: #94a3b8;
        font-size: 0.9rem;
        transition: 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .back-link:hover { color: #b3365b; }

    /* Stat Card ปรับให้เรียบและขอบมนขึ้น */
    .stat-card {
        background: white;
        border-radius: 2.5rem; /* ขอบมนมากแบบปุ่มในรูป */
        padding: 25px;
        border: none;
        transition: transform 0.3s ease;
        box-shadow: 0 10px 25px -5px rgba(240, 98, 146, 0.1);
    }
    .stat-card:hover { transform: translateY(-5px); }
    
    .stat-icon { 
        width: 48px; height: 48px; 
        background: #fff0f3; 
        color: #f06292; 
        border-radius: 1rem; 
        display: flex; align-items: center; justify-content: center; 
        font-size: 1.4rem; margin-bottom: 12px; 
    }

    /* แผงตารางสไตล์ MIRA-CARD */
    .glass-panel {
        background: white;
        border-radius: 2.5rem;
        padding: 40px;
        box-shadow: 0 10px 25px -5px rgba(240, 98, 146, 0.1);
        border: none;
    }

    /* ตาราง */
    .table thead th {
        background: transparent;
        border-bottom: 1px solid #f8f9fa;
        color: #94a3b8;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        padding-bottom: 20px;
    }
    .table tbody td {
        padding: 20px 10px;
        border-bottom: 1px solid #fdf2f4;
        font-size: 0.95rem;
    }
    
    .user-avatar { 
        width: 45px; height: 45px; 
        border-radius: 50%; 
        object-fit: cover; 
        border: 2px solid #fff; 
        box-shadow: 0 4px 10px rgba(240, 98, 146, 0.2); 
    }

    /* Badge สไตล์ Minimal */
    .badge-premium { 
        background: #f06292; 
        color: white; 
        border-radius: 50px; 
        padding: 5px 15px; 
        font-size: 0.65rem; 
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    .badge-general {
        background: #f8f9fa;
        color: #94a3b8;
        border-radius: 50px;
        padding: 5px 15px;
        font-size: 0.65rem;
        font-weight: 700;
    }
    
    .text-pink-mira { color: #f06292; }
</style>
</head>
<body>

<div class="container py-5">
    <div class="mb-4">
        <a href="../index_ad.php" class="back-link">
            <i class="bi bi-arrow-left"></i> กลับสู่หน้า Dashboard
        </a>
    </div>

    <div class="d-flex justify-content-between align-items-start mb-5">
        <div>
            <h2 class="mira-title mb-1">ข้อมูลสมาชิก</h2>
            <p class="text-muted small">บริหารจัดการข้อมูลสมาชิกและสถิติการซื้อของ Mira</p>
        </div>
       
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-person-plus"></i></div>
                <h6 class="text-muted small fw-600">ลูกค้าใหม่วันนี้</h6>
                <h3 class="fw-bold mb-0 text-dark"><?= number_format($newToday) ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background: #eef2ff; color: #6366f1;"><i class="bi bi-calendar-check"></i></div>
                <h6 class="text-muted small fw-600">ลูกค้าใหม่เดือนนี้</h6>
                <h3 class="fw-bold mb-0 text-dark"><?= number_format($newMonth) ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background: #fff7ed; color: #f97316;"><i class="bi bi-arrow-repeat"></i></div>
                <h6 class="text-muted small fw-600">ลูกค้าประจำ</h6>
                <h3 class="fw-bold mb-0 text-dark"><?= number_format($loyalCustomers) ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background: #fefce8; color: #ca8a04;"><i class="bi bi-gem"></i></div>
                <h6 class="text-muted small fw-600">ระดับ VIP</h6>
                <h3 class="fw-bold mb-0 text-dark">Premium</h3>
            </div>
        </div>
    </div>

    <div class="glass-panel">
        <div class="mb-4">
            <h5 class="fw-bold text-dark">รายชื่อสมาชิกทั้งหมด (<?= count($customers) ?>)</h5>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>สมาชิกระดับ</th>
                        <th>ข้อมูลลูกค้า</th>
                        <th>วันที่สมัคร</th>
                        <th class="text-center">ออเดอร์</th>
                        <th class="text-end">ยอดซื้อรวม</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $cus): 
                        // --- Logic PHP เดิมของคุณห้ามแก้ไข ---
                        $profile_file = $cus['profile_img'];
                        $file_path = "../../uploads/profiles/" . $profile_file;
                        if (!empty($profile_file) && file_exists($file_path)) {
                            $pic = $file_path;
                        } else {
                            $pic = "https://ui-avatars.com/api/?name=".urlencode($cus['fullname'])."&background=f06292&color=fff";
                        }
                    ?>
                    <tr>
                        <td>
                            <?php if($cus['total_spent'] > 5000): ?>
                                <span class="badge-premium">TOP SPENDER</span>
                            <?php else: ?>
                                <span class="badge-general">GENERAL</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="<?= $pic ?>" class="user-avatar me-3">
                                <div>
                                    <h6 class="mb-0 fw-600 text-dark"><?= htmlspecialchars($cus['fullname']) ?></h6>
                                    <small class="text-muted"><?= $cus['email'] ?></small>
                                </div>
                            </div>
                        </td>
                        <td class="small text-muted"><?= date('d/m/Y', strtotime($cus['created_at'])) ?></td>
                        <td class="text-center fw-bold text-dark"><?= $cus['order_count'] ?></td>
                        <td class="text-end fw-bold text-pink-mira">฿<?= number_format($cus['total_spent'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


</body>
</html>