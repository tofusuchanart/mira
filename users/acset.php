<?php
session_start();
require_once "../config.php"; 

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$status_msg = "";

// --- 1. ส่วนการประมวลผลการลบบัญชี (Delete Logic) ---
if (isset($_POST['confirm_delete_account'])) {
    try {
        // เริ่ม Transaction เพื่อความปลอดภัย
        $conn->beginTransaction();

        // ลบข้อมูลในตารางที่เกี่ยวข้องก่อน (ถ้าไม่ได้ตั้ง CASCADE ไว้)
        // เช่น ลบรีวิว หรือ ข้อความติดต่อ ของ user นี้
        $conn->prepare("DELETE FROM reviews WHERE user_id = ?")->execute([$user_id]);
        $conn->prepare("DELETE FROM contact_messages WHERE user_id = ?")->execute([$user_id]);
        
        // ลบตัวตนผู้ใช้
        $delete_stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $delete_stmt->execute([$user_id]);

        $conn->commit();

        // ลบ Session และส่งไปหน้าสมัครสมาชิกหรือหน้าแรก
        session_destroy();
        echo "<script>
                alert('บัญชีของคุณถูกลบเรียบร้อยแล้ว หวังว่าจะได้พบกันใหม่นะค่ะ ✨');
                window.location.href = '../register.php'; 
              </script>";
        exit();
    } catch (Exception $e) {
        $conn->rollBack();
        $status_msg = "ไม่สามารถลบบัญชีได้: " . $e->getMessage();
    }
}

// --- 2. ดึงข้อมูลผู้ใช้มาโชว์ ---
$stmt = $conn->prepare("SELECT email, created_at, role FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// --- 3. ส่วนเปลี่ยนรหัสผ่าน (เหมือนเดิม) ---
if (isset($_POST['change_password'])) {
    $current_pass = $_POST['current_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    $check_stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
    $check_stmt->execute([$user_id]);
    $stored_pass = $check_stmt->fetchColumn();

    if ($current_pass !== $stored_pass) {
        $status_msg = "รหัสผ่านปัจจุบันไม่ถูกต้องค่ะ";
    } elseif ($new_pass !== $confirm_pass) {
        $status_msg = "รหัสผ่านใหม่ไม่ตรงกันค่ะ";
    } else {
        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $update_stmt->execute([$new_pass, $user_id]);
        $status_msg = "เปลี่ยนรหัสผ่านสำเร็จแล้ว ✨";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings | MIRA</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        /* Style เดิมของคุณทั้งหมดคงไว้ */
        :root { --mira-pink-dark: #a34a67; --mira-pink-soft: #fdf5f7; --mira-pink-accent: #f8a5c2; --mira-bg: #fff0f5; }
        body { background-color: var(--mira-bg); font-family: 'Sarabun', sans-serif; color: #5d5d5d; }
        .mira-header { font-family: 'Playfair Display', serif; color: var(--mira-pink-dark); letter-spacing: 1px; }
        .settings-card { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(15px); border: 1px solid rgba(255, 255, 255, 0.6); border-radius: 30px; padding: 40px; box-shadow: 0 15px 35px rgba(163, 74, 103, 0.05); }
        .section-title { color: var(--mira-pink-dark); font-weight: 600; font-size: 1.1rem; margin-bottom: 25px; display: flex; align-items: center; }
        .form-label { font-size: 0.85rem; font-weight: 600; color: var(--mira-pink-accent); text-transform: uppercase; }
        .form-control { border-radius: 15px; border: 1px solid #f3e4e8; padding: 12px 15px; background: rgba(255, 255, 255, 0.5); }
        .btn-mira { background: var(--mira-pink-dark); color: white; border-radius: 50px; padding: 10px 30px; border: none; transition: 0.3s; font-weight: 600; }
        .status-badge { background: white; padding: 15px 25px; border-radius: 20px; border-left: 5px solid var(--mira-pink-accent); margin-bottom: 30px; }
        .nav-back { color: var(--mira-pink-dark); text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <a href="pf.php" class="nav-back"><i class="bi bi-chevron-left me-2"></i> กลับหน้าโปรไฟล์</a>

            <div class="settings-card">
                <h2 class="mira-header fw-bold mb-4">Account Settings</h2>

                <div class="status-badge shadow-sm d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted d-block">อีเมลที่ใช้งานปัจจุบัน</small>
                        <strong><?= htmlspecialchars($user['email']) ?></strong>
                    </div>
                    <div class="text-end">
                        <span class="badge rounded-pill" style="background: var(--mira-pink-soft); color: var(--mira-pink-dark);">
                            <?= strtoupper($user['role']) ?>
                        </span>
                    </div>
                </div>

                <?php if ($status_msg): ?>
                    <div class="alert alert-light text-center border-0 mb-4" style="border-radius: 15px; background: #fff; color: var(--mira-pink-dark);">
                        <?= $status_msg ?>
                    </div>
                <?php endif; ?>

                <div class="mb-5">
                    <div class="section-title"><i class="bi bi-shield-lock"></i> Password & Security</div>
                    <form action="" method="POST">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">รหัสผ่านปัจจุบัน</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">รหัสผ่านใหม่</label>
                                <input type="password" name="new_password" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ยืนยันรหัสผ่านใหม่</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" name="change_password" class="btn btn-mira">บันทึกรหัสผ่านใหม่</button>
                            </div>
                        </div>
                    </form>
                </div>

                <hr style="opacity: 0.1;">

                <div class="mt-4">
                    <div class="section-title"><i class="bi bi-bell"></i> Notifications & Others Settings</div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <p class="mb-0 fw-bold text-danger">ลบบัญชีผู้ใช้</p>
                            <small class="text-muted">ข้อมูลทั้งหมดจะถูกลบออกถาวรและไม่สามารถกู้คืนได้</small>
                        </div>
                        <form id="delete-form" method="POST">
                            <input type="hidden" name="confirm_delete_account" value="1">
                           <button type="button" onclick="confirmDelete()" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                                ลบบัญชี
                            </button>
                        
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// ฟังก์ชันแจ้งเตือนก่อนลบจริง
function confirmDelete() {
    Swal.fire({
        title: 'คุณแน่ใจหรือไม่?',
        text: "ข้อมูลสมาชิกและประวัติของคุณจะถูกลบออกถาวรนะค่ะ",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#a34a67', // สีชมพูเข้ม MIRA
        cancelButtonColor: '#ccc',
        confirmButtonText: 'ใช่, ฉันต้องการลบ',
        cancelButtonText: 'ยกเลิก',
        borderRadius: '20px'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form').submit();
        }
    })
}
</script>

</body>
</html>