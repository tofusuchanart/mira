<?php
session_start();
require_once "../../config.php";

// ดึงข้อมูลผู้ใช้หาก Login อยู่
$user_id = $_SESSION['user_id'] ?? null;
$fullname = $_SESSION['fullname'] ?? '';
$email = $_SESSION['email'] ?? '';

$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    try {
        $stmt = $conn->prepare("INSERT INTO contact_messages (user_id, subject, message) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $subject, $message]);
        $success = true;
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Owner | MIRA</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="../../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --mira-pink: #f8a5c2;
            --mira-dark-pink: #b3365b;
            --mira-bg: #fff0f5;
        }

        body {
            background-color: var(--mira-bg);
            font-family: 'Sarabun', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .mira-header {
            font-family: 'Playfair Display', serif;
            color: var(--mira-dark-pink);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 30px;
            padding: 50px;
            box-shadow: 0 15px 35px rgba(179, 54, 91, 0.1);
            width: 100%;
            max-width: 700px;
            margin: auto;
        }

        .form-control {
            border-radius: 15px;
            border: 1px solid rgba(179, 54, 91, 0.1);
            padding: 12px 20px;
            background: rgba(255, 255, 255, 0.5);
        }

        .form-control:focus {
            box-shadow: 0 0 0 0.25 margin-bottom rgba(248, 165, 194, 0.25);
            border-color: var(--mira-pink);
        }

        .btn-send {
            background: var(--mira-dark-pink);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 50px;
            font-weight: 600;
            letter-spacing: 1px;
            transition: 0.3s;
            width: 100%;
        }

        .btn-send:hover {
            background: #8e2a48;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(179, 54, 91, 0.2);
            color: white;
        }

        .contact-info-icon {
            width: 50px;
            height: 50px;
            background: white;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--mira-dark-pink);
            font-size: 1.5rem;
            margin-bottom: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>

<div class="container">
    <div class="glass-card shadow-lg">
        <div class="text-center mb-5">
            <h2 class="mira-header fw-bold">Send us a Message</h2>
            <p class="text-muted">มีคำถามหรือข้อสงสัย? ส่งข้อความหาเราได้ทันที</p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success border-0 rounded-4 py-3 text-center mb-4">
                <i class="bi bi-check-circle-fill me-2"></i> ส่งข้อความของคุณเรียบร้อยแล้ว!
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="row g-3">
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-bold text-muted">ชื่อผู้ส่ง</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($fullname) ?>" readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-bold text-muted">อีเมลติดต่อ</label>
                    <input type="email" class="form-control" value="<?= htmlspecialchars($email) ?>" readonly>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label small fw-bold text-muted">หัวข้อข้อความ</label>
                    <input type="text" name="subject" class="form-control" placeholder="ระบุหัวข้อที่ต้องการติดต่อ" required>
                </div>
                <div class="col-12 mb-4">
                    <label class="form-label small fw-bold text-muted">รายละเอียดข้อความ</label>
                    <textarea name="message" class="form-control" rows="5" placeholder="พิมพ์ข้อความของคุณที่นี่..." required></textarea>
                </div>
                <div class="col-12 text-center">
                    <button type="submit" name="send_message" class="btn btn-send">
                        <i class="bi bi-send-fill me-2"></i> ส่งข้อความ
                    </button>
                    <div class="mt-4">
                        <a href="../../users/index_users.php" class="text-decoration-none small text-muted">
                            <i class="bi bi-arrow-left me-1"></i> กลับสู่หน้าหลัก
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="../../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>