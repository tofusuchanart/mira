<?php
session_start();
require_once "../config.php"; // ตรวจสอบ Path การเชื่อมต่อ DB ให้ถูกต้อง

// ตรวจสอบ Login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = "";

// --- ส่วนของการประมวลผลการอัปเดต (POST) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    
    try {
        // จัดการเรื่องรูปภาพ
        $profile_img = $_POST['old_img']; // ใช้รูปเดิมเป็นหลัก
        if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] == 0) {
            $ext = pathinfo($_FILES['profile_img']['name'], PATHINFO_EXTENSION);
            $new_name = "user_" . uniqid() . "." . $ext;
            $target = "../photo/" . $new_name;
            
            if (move_uploaded_file($_FILES['profile_img']['tmp_name'], $target)) {
                $profile_img = $new_name;
            }
        }

        // SQL Update
        $sql_update = "UPDATE users SET fullname = ?, email = ?, phone = ?, address = ?, profile_img = ? WHERE user_id = ?";
        $stmt_up = $conn->prepare($sql_update);
        $stmt_up->execute([$fullname, $email, $phone, $address, $profile_img, $user_id]);
        
        $msg = "บันทึกการเปลี่ยนแปลงเรียบร้อยแล้วค่ะ ✨";
    } catch (PDOException $e) {
        $msg = "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
}

// --- ดึงข้อมูลปัจจุบันมาแสดงในฟอร์ม ---
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile | MIRA</title>
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

        .glass-panel {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 30px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(179, 54, 91, 0.05);
        }

        /* Profile Upload Decor */
        .profile-upload-wrapper {
            position: relative;
            width: 130px;
            height: 130px;
            margin: 0 auto 30px;
        }

        .profile-preview {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .file-input-label {
            position: absolute;
            bottom: 0;
            right: 0;
            background: var(--mira-pink-dark);
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 2px solid white;
            transition: 0.3s;
        }

        .file-input-label:hover { background: var(--mira-pink-accent); }

        /* Form Styling */
        .form-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--mira-pink-dark);
            margin-bottom: 8px;
        }

        .form-control {
            border-radius: 15px;
            border: 1px solid #f3e4e8;
            padding: 12px 18px;
            background: rgba(255, 255, 255, 0.5);
            transition: 0.3s;
        }

        .form-control:focus {
            box-shadow: 0 0 0 4px rgba(248, 165, 194, 0.15);
            border-color: var(--mira-pink-accent);
            background: white;
        }

        .btn-save {
            background: var(--mira-pink-dark);
            color: white;
            border-radius: 50px;
            padding: 12px 40px;
            border: none;
            font-weight: 600;
            transition: 0.3s;
            width: 100%;
        }

        .btn-save:hover {
            background: #8e3e58;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(163, 74, 103, 0.2);
        }

        .btn-cancel {
            color: #999;
            text-decoration: none;
            font-size: 0.9rem;
            transition: 0.3s;
        }
        .btn-cancel:hover { color: var(--mira-pink-dark); }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            
            <div class="glass-panel">
                <div class="text-center mb-4">
                    <h2 class="mira-header fw-bold">Edit Profile</h2>
                    <p class="text-muted small">ปรับปรุงข้อมูลส่วนตัวของคุณให้เป็นปัจจุบัน</p>
                </div>

                <?php if ($msg != ""): ?>
                    <div class="alert alert-light border-0 text-center mb-4" style="color: var(--mira-pink-dark); background: #fff0f5; border-radius: 15px;">
                        <i class="bi bi-check-circle-fill me-2"></i> <?= $msg ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="profile-upload-wrapper">
                        <img src="../photo/<?= !empty($user['profile_img']) ? $user['profile_img'] : 'default.jpg' ?>" id="preview" class="profile-preview">
                        <label for="profile_img" class="file-input-label">
                            <i class="bi bi-camera-fill"></i>
                        </label>
                        <input type="file" name="profile_img" id="profile_img" hidden onchange="previewImg(this)">
                        <input type="hidden" name="old_img" value="<?= $user['profile_img'] ?>">
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">ชื่อ-นามสกุล</label>
                            <input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($user['fullname']) ?>" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">อีเมล</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">เบอร์โทรศัพท์</label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label">ที่อยู่สำหรับการจัดส่ง</label>
                            <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($user['address']) ?></textarea>
                        </div>

                        <div class="col-12 text-center mt-4">
                            <button type="submit" class="btn btn-save mb-3">บันทึกข้อมูล</button>
                            <br>
                            <a href="pf.php" class="btn-cancel">ยกเลิกและกลับหน้าโปรไฟล์</a>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
    // ฟังก์ชันโชว์รูปตัวอย่างทันทีที่เลือกไฟล์
    function previewImg(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

</body>
</html>