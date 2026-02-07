<?php
session_start();
require_once "../config.php"; // ตรวจสอบ path ไฟล์เชื่อมต่อฐานข้อมูลให้ถูกต้อง

// ตรวจสอบว่า Login หรือยัง (อ้างอิงตามโครงสร้างฐานข้อมูล users)
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user = [];

// ดึงข้อมูลผู้ใช้จากฐานข้อมูล
try {
    $sql = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("ไม่พบข้อมูลผู้ใช้");
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIRA | My Profile</title>
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

        /* Glass Panel */
        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 30px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(179, 54, 91, 0.05);
        }

        /* Profile Image */
        .profile-img-wrapper {
            position: relative;
            width: 140px;
            height: 140px;
            margin: 0 auto 20px;
        }

        .profile-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid white;
            box-shadow: 0 8px 15px rgba(163, 74, 103, 0.1);
        }

        /* Label & Info */
        .info-group {
            margin-bottom: 25px;
            border-bottom: 1px solid rgba(163, 74, 103, 0.1);
            padding-bottom: 10px;
        }

        .info-label {
            font-size: 0.75rem;
            color: var(--mira-pink-accent);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
            display: block;
        }

        .info-text {
            font-size: 1.1rem;
            color: #444;
            font-weight: 400;
        }

        /* Menu Items */
        .menu-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            border-radius: 15px;
            color: #666;
            text-decoration: none;
            transition: 0.3s;
            background: white;
            margin-bottom: 12px;
        }

        .menu-link:hover {
            background: var(--mira-pink-dark);
            color: white;
            transform: translateX(5px);
        }

        .menu-icon {
            margin-right: 15px;
            font-size: 1.2rem;
        }

        .btn-edit {
            background: var(--mira-pink-dark);
            color: white;
            border-radius: 50px;
            padding: 10px 30px;
            border: none;
            transition: 0.3s;
        }

        .btn-edit:hover {
            background: #8e3e58;
            box-shadow: 0 5px 15px rgba(163, 74, 103, 0.3);
            color: white;
        }

        .role-badge {
            background: var(--mira-pink-accent);
            color: white;
            font-size: 0.7rem;
            padding: 4px 12px;
            border-radius: 50px;
            text-transform: uppercase;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="glass-panel text-center">
                        <div class="profile-img-wrapper">
                            <img src="../photo/<?= !empty($user['profile_img']) ? htmlspecialchars($user['profile_img']) : 'default_user.png' ?>" class="profile-img">
                        </div>
                        
                        <h4 class="mira-header fw-bold mb-1"><?= htmlspecialchars($user['fullname']) ?></h4>
                        <div class="mb-4">
                            <span class="role-badge"><?= htmlspecialchars($user['role']) ?></span>
                        </div>

                        <div class="text-start mt-4">
                            <a href="order_history.php" class="menu-link shadow-sm">
                                <i class="bi bi-clock-history menu-icon"></i> ประวัติการสั่งซื้อ
                            </a>
                            <a href="edit_pf.php" class="menu-link shadow-sm">
                                <i class="bi bi-pencil-square menu-icon"></i> แก้ไขข้อมูลส่วนตัว
                            </a>
                            <a href="../../logout.php" class="menu-link shadow-sm text-danger">
                                <i class="bi bi-box-arrow-right menu-icon"></i> ออกจากระบบ
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="glass-panel h-100">
                        <div class="d-flex justify-content-between align-items-center mb-5">
                            <h5 class="mira-header fw-bold mb-0">ข้อมูลบัญชีผู้ใช้งาน</h5>
                            <i class="bi bi-stars" style="color: var(--mira-pink-accent);"></i>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-group">
                                    <span class="info-label">ชื่อ-นามสกุล</span>
                                    <span class="info-text"><?= htmlspecialchars($user['fullname']) ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-group">
                                    <span class="info-label">อีเมล</span>
                                    <span class="info-text"><?= htmlspecialchars($user['email']) ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-group">
                                    <span class="info-label">เบอร์โทรศัพท์</span>
                                    <span class="info-text"><?= !empty($user['phone']) ? htmlspecialchars($user['phone']) : '-' ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-group">
                                    <span class="info-label">วันที่เป็นสมาชิก</span>
                                    <span class="info-text"><?= date('d/m/Y', strtotime($user['created_at'])) ?></span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="info-group border-0">
                                    <span class="info-label">ที่อยู่สำหรับการจัดส่ง</span>
                                    <span class="info-text d-block mt-2">
                                        <?= !empty($user['address']) ? nl2br(htmlspecialchars($user['address'])) : '<span class="text-muted small">ยังไม่ได้ระบุที่อยู่</span>' ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <a href="acset.php" class="btn btn-edit">
                                <i class="bi bi-gear-fill me-2"></i> ตั้งค่าบัญชี
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>